<?php

namespace App\Services;

use App\Models\RadCheck;
use App\Models\RadGroupCheck;
use App\Models\RadReply;
use App\Models\RadUserGroup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RadiusUserService
{
    /**
     * Daftar user hotspot dengan search + pagination manual.
     * Sumber data: radcheck (username unik dengan attribute Cleartext-Password).
     */
    public function paginate(string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        $query = RadCheck::query()
            ->select('username')
            ->where('attribute', 'Cleartext-Password');

        if ($search) {
            $query->where('username', 'like', "%{$search}%");
        }

        // Ambil username unik
        $query->distinct();

        $total    = (clone $query)->count();
        $page     = LengthAwarePaginator::resolveCurrentPage();
        $usernames = $query->forPage($page, $perPage)->pluck('username');

        // Enrich dengan data group dan expiry
        $items = $usernames->map(fn ($username) => $this->buildUserData($username));

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

        $expiry = RadCheck::where('username', $username)
            ->where('attribute', 'Expiration')
            ->value('value');

        $maxData = RadReply::where('username', $username)
            ->where('attribute', 'WISPr-Bandwidth-Max-Down')
            ->value('value');

        return [
            'username'  => $username,
            'password'  => $password,
            'group'     => $group,
            'expiry'    => $expiry,
            'max_down'  => $maxData,
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

            // Tanggal expire opsional
            if (! empty($data['expiry'])) {
                RadCheck::create([
                    'username'  => $data['username'],
                    'attribute' => 'Expiration',
                    'op'        => ':=',
                    'value'     => $data['expiry'],
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

            // Update expiry
            RadCheck::where('username', $username)->where('attribute', 'Expiration')->delete();
            if (! empty($data['expiry'])) {
                RadCheck::create([
                    'username'  => $username,
                    'attribute' => 'Expiration',
                    'op'        => ':=',
                    'value'     => $data['expiry'],
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
     * Ambil daftar group yang tersedia.
     */
    public function availableGroups(): Collection
    {
        return RadGroupCheck::distinct()->pluck('groupname')->sort()->values();
    }

    private function buildUserData(string $username): array
    {
        $group  = RadUserGroup::where('username', $username)->value('groupname');
        $expiry = RadCheck::where('username', $username)
            ->where('attribute', 'Expiration')
            ->value('value');

        return [
            'username' => $username,
            'group'    => $group ?? '-',
            'expiry'   => $expiry ?? '-',
            'active'   => $this->isActive($username),
        ];
    }
}
