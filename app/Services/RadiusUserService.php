<?php

namespace App\Services;

use App\Models\RadCheck;
use App\Models\RadGroupCheck;
use App\Models\RadReply;
use App\Models\RadUserGroup;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RadiusUserService
{
    /**
     * Daftar user hotspot dengan search + pagination manual.
     * Sumber data: radcheck (username unik dengan attribute Cleartext-Password).
     */
    public function paginate(string $search = '', int $perPage = 15, string $group = '', string $status = ''): LengthAwarePaginator
    {
        $query = RadCheck::query()
            ->select('radcheck.username')
            ->where('radcheck.attribute', 'Cleartext-Password');

        if ($group) {
            $query->join('radusergroup', 'radcheck.username', '=', 'radusergroup.username')
                  ->where('radusergroup.groupname', $group);
        }

        if ($search) {
            $query->where('radcheck.username', 'like', "%{$search}%");
        }

        // Expression untuk expiry efektif: prefer vouchers.expired_at, fallback radcheck Expiration
        $effectiveExpiryExpr = "
            COALESCE(
                (SELECT v.expired_at FROM vouchers v WHERE v.code = radcheck.username COLLATE utf8mb4_unicode_ci LIMIT 1),
                (SELECT COALESCE(
                    STR_TO_DATE(rc_exp.value, '%d %b %Y %H:%i:%s'),
                    STR_TO_DATE(rc_exp.value, '%d %b %Y')
                 )
                 FROM radcheck rc_exp
                 WHERE rc_exp.username = radcheck.username AND rc_exp.attribute = 'Expiration'
                 LIMIT 1)
            )
        ";

        $hasReject = function ($q) {
            $q->from('radcheck as rc_rej')
              ->whereColumn('rc_rej.username', 'radcheck.username')
              ->where('rc_rej.attribute', 'Auth-Type')
              ->where('rc_rej.value', 'Reject');
        };

        if ($status === 'nonaktif') {
            $query->whereExists($hasReject);
        } elseif ($status === 'aktif') {
            $query->whereNotExists($hasReject)
                  ->where(function ($q) use ($effectiveExpiryExpr) {
                      $q->whereRaw("$effectiveExpiryExpr >= NOW()")
                        ->orWhereRaw("$effectiveExpiryExpr IS NULL");
                  });
        } elseif ($status === 'expired') {
            $query->whereRaw("$effectiveExpiryExpr < NOW()");
        }

        $query->distinct();

        $total     = (clone $query)->count();
        $page      = LengthAwarePaginator::resolveCurrentPage();
        $usernames = $query->forPage($page, $perPage)->pluck('username');

        // Batch-fetch semua data yang dibutuhkan (menghindari N+1)
        $groups         = RadUserGroup::whereIn('username', $usernames)->pluck('groupname', 'username');
        $radExpiries    = RadCheck::whereIn('username', $usernames)->where('attribute', 'Expiration')->pluck('value', 'username');
        $blocked        = RadCheck::whereIn('username', $usernames)->where('attribute', 'Auth-Type')->where('value', 'Reject')->pluck('username')->flip();
        $voucherExpiries = Voucher::whereIn('code', $usernames)->pluck('expired_at', 'code');

        $items = $usernames->map(function ($username) use ($groups, $radExpiries, $blocked, $voucherExpiries) {
            // Prefer vouchers.expired_at (authoritative) over radcheck.Expiration
            if ($voucherExpiries->has($username) && $voucherExpiries[$username]) {
                $expiry = Carbon::parse($voucherExpiries[$username])->format('d M Y H:i');
            } elseif ($radExpiries->has($username)) {
                $expiry = Carbon::parse($radExpiries[$username])->format('d M Y');
            } else {
                $expiry = '-';
            }

            return [
                'username' => $username,
                'group'    => $groups->get($username, '-'),
                'expiry'   => $expiry,
                'active'   => ! $blocked->has($username),
            ];
        });

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    /**
     * Ambil detail satu user.
     */
    public function find(string $username): array
    {
        $password = RadCheck::where('username', $username)
            ->where('attribute', 'Cleartext-Password')
            ->value('value');

        if (! $password) {
            abort(404, 'User tidak ditemukan.');
        }

        $group = RadUserGroup::where('username', $username)->value('groupname');

        $expiryRaw = RadCheck::where('username', $username)
            ->where('attribute', 'Expiration')
            ->value('value');

        $maxData = RadReply::where('username', $username)
            ->where('attribute', 'WISPr-Bandwidth-Max-Down')
            ->value('value');

        return [
            'username'     => $username,
            'password'     => $password,
            'group'        => $group,
            'expiry'       => $expiryRaw ? Carbon::parse($expiryRaw)->format('d M Y') : '-',
            'expiry_input' => $expiryRaw ? Carbon::parse($expiryRaw)->format('Y-m-d') : '',
            'max_down'     => $maxData,
        ];
    }

    /**
     * Buat user baru di FreeRADIUS.
     */
    public function create(array $data): void
    {
        DB::transaction(function () use ($data) {
            // Password
            RadCheck::create([
                'username'  => $data['username'],
                'attribute' => 'Cleartext-Password',
                'op'        => ':=',
                'value'     => $data['password'],
            ]);

            // Assign ke group/profile
            if (! empty($data['group'])) {
                RadUserGroup::create([
                    'username'  => $data['username'],
                    'groupname' => $data['group'],
                    'priority'  => 1,
                ]);
            }

            // Tanggal expire opsional — simpan dalam format FreeRADIUS: "d M Y 23:59:59"
            if (! empty($data['expiry'])) {
                RadCheck::create([
                    'username'  => $data['username'],
                    'attribute' => 'Expiration',
                    'op'        => ':=',
                    'value'     => self::toRadiusDate($data['expiry']),
                ]);
            }
        });
    }

    /**
     * Update data user (password, group, expiry).
     */
    public function update(string $username, array $data): void
    {
        DB::transaction(function () use ($username, $data) {
            // Update password jika diisi
            if (! empty($data['password'])) {
                RadCheck::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Cleartext-Password'],
                    ['op' => ':=', 'value' => $data['password']]
                );
            }

            // Update group
            RadUserGroup::where('username', $username)->delete();
            if (! empty($data['group'])) {
                RadUserGroup::create([
                    'username'  => $username,
                    'groupname' => $data['group'],
                    'priority'  => 1,
                ]);
            }

            // Update expiry — simpan dalam format FreeRADIUS: "d M Y 23:59:59"
            RadCheck::where('username', $username)->where('attribute', 'Expiration')->delete();
            if (! empty($data['expiry'])) {
                RadCheck::create([
                    'username'  => $username,
                    'attribute' => 'Expiration',
                    'op'        => ':=',
                    'value'     => self::toRadiusDate($data['expiry']),
                ]);
            }
        });
    }

    /**
     * Hapus semua data user dari tabel RADIUS.
     */
    public function delete(string $username): void
    {
        DB::transaction(function () use ($username) {
            RadCheck::where('username', $username)->delete();
            RadReply::where('username', $username)->delete();
            RadUserGroup::where('username', $username)->delete();
        });
    }

    /**
     * Toggle aktif/nonaktif user.
     * Nonaktif = tambah attribute Auth-Type := Reject di radcheck.
     */
    public function toggle(string $username): bool
    {
        $reject = RadCheck::where('username', $username)
            ->where('attribute', 'Auth-Type')
            ->where('value', 'Reject')
            ->first();

        if ($reject) {
            $reject->delete();
            return true; // sekarang aktif
        }

        RadCheck::create([
            'username'  => $username,
            'attribute' => 'Auth-Type',
            'op'        => ':=',
            'value'     => 'Reject',
        ]);

        return false; // sekarang nonaktif
    }

    /**
     * Cek apakah user aktif.
     */
    public function isActive(string $username): bool
    {
        return ! RadCheck::where('username', $username)
            ->where('attribute', 'Auth-Type')
            ->where('value', 'Reject')
            ->exists();
    }

    /**
     * Ambil daftar group dari UNION radgroupcheck + radgroupreply,
     * sama seperti logika panel lama — agar profil lawas tetap muncul.
     */
    public function availableGroups(): Collection
    {
        $rows = DB::select('
            SELECT groupname FROM radgroupcheck
            UNION
            SELECT groupname FROM radgroupreply
            ORDER BY groupname
        ');

        return collect($rows)->pluck('groupname')->values();
    }

    /**
     * Konversi input date dari form (Y-m-d) ke format FreeRADIUS "d M Y 23:59:59".
     * Terima juga "d M Y" dan "d M Y H:i:s" agar tetap aman.
     */
    private static function toRadiusDate(string $date): string
    {
        return Carbon::parse($date)->setTime(23, 59, 59)->format('d M Y H:i:s');
    }
}
