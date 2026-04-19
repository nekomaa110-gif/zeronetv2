<?php

namespace App\Services;

use App\Models\Package;
use App\Models\RadCheck;
use App\Models\RadReply;
use App\Models\RadUserGroup;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoucherService
{
    /**
     * Definisi tipe voucher.
     *
     * session_seconds — batas TOTAL waktu sesi (diisi ke radcheck sebagai Max-All-Session).
     *                   Memerlukan rlm_sqlcounter dikonfigurasi di FreeRADIUS.
     *                   null = tidak ada batas waktu sesi (untuk 7d).
     *
     * calendar_hours  — masa berlaku sejak login pertama (diisi ke radcheck sebagai Expiration).
     *                   Bekerja out-of-the-box dengan rlm_expiration FreeRADIUS.
     */
    public const TYPES = [
        '4h' => [
            'label'           => '4 Jam',
            'session_seconds' => 14400,
            'calendar_hours'  => 24,
            'description'     => '4 jam pemakaian, berlaku 24 jam sejak login pertama',
        ],
        '5h' => [
            'label'           => '5 Jam',
            'session_seconds' => 18000,
            'calendar_hours'  => 24,
            'description'     => '5 jam pemakaian, berlaku 24 jam sejak login pertama',
        ],
        '7d' => [
            'label'           => '7 Hari',
            'session_seconds' => null,
            'calendar_hours'  => 168,
            'description'     => '7 hari kalender penuh sejak login pertama',
        ],
    ];

    /**
     * Generate sejumlah voucher dan tulis ke FreeRADIUS.
     *
     * Username = prefix + suffix (atau XXXX-XXXX jika tanpa prefix).
     * Password = 4 digit numerik acak — disimpan di DB dan radcheck.
     */
    public function generate(int $count, string $type, int $packageId, ?string $note, ?string $prefix): Collection
    {
        $cfg    = self::TYPES[$type];
        $package = Package::findOrFail($packageId);
        $vouchers = collect();
        $prefix = $prefix ? strtoupper(trim($prefix)) : null;

        DB::transaction(function () use ($count, $type, $cfg, $packageId, $package, $note, $prefix, &$vouchers) {
            for ($i = 0; $i < $count; $i++) {
                $voucher = Voucher::create([
                    'code'            => $this->generateUsername($prefix),
                    'prefix'          => $prefix,
                    'password'        => $this->generatePassword(),
                    'type'            => $type,
                    'package_id'      => $packageId,
                    'session_seconds' => $cfg['session_seconds'],
                    'calendar_hours'  => $cfg['calendar_hours'],
                    'status'          => 'ready',
                    'note'            => $note,
                    'created_by'      => auth()->id(),
                ]);

                $this->writeToRadius($voucher, $package->groupname);
                $vouchers->push($voucher);
            }
        });

        return $vouchers;
    }

    /**
     * Tulis entri FreeRADIUS saat voucher dibuat.
     * Expiration belum di-set — ditulis saat login pertama terdeteksi.
     */
    private function writeToRadius(Voucher $voucher, string $groupname): void
    {
        // Username = code, Password = 4-digit numerik
        RadCheck::updateOrCreate(
            ['username' => $voucher->code, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $voucher->password]
        );

        // Batas total waktu sesi (4h/5h) — butuh rlm_sqlcounter di FreeRADIUS
        if ($voucher->session_seconds) {
            RadCheck::updateOrCreate(
                ['username' => $voucher->code, 'attribute' => 'Max-All-Session'],
                ['op' => ':=', 'value' => (string) $voucher->session_seconds]
            );
        }

        // Assign ke profile/paket
        RadUserGroup::where('username', $voucher->code)->delete();
        RadUserGroup::create([
            'username'  => $voucher->code,
            'groupname' => $groupname,
            'priority'  => 1,
        ]);
    }

    /**
     * Aktifkan voucher: set first_login_at, hitung expired_at, tulis Expiration ke radcheck.
     * Dipanggil oleh sync command (tanpa auth) maupun controller.
     */
    public function activate(Voucher $voucher, Carbon $firstLoginAt): void
    {
        $expiredAt = $firstLoginAt->copy()->addHours($voucher->calendar_hours);

        $voucher->update([
            'status'         => 'active',
            'first_login_at' => $firstLoginAt,
            'expired_at'     => $expiredAt,
        ]);

        // Expiration — format diakui FreeRADIUS rlm_expiration: "DD Mon YYYY HH:MM:SS"
        RadCheck::updateOrCreate(
            ['username' => $voucher->code, 'attribute' => 'Expiration'],
            ['op' => ':=', 'value' => $expiredAt->format('d M Y H:i:s')]
        );

        // Hapus Auth-Type Reject jika ada (dari disable sebelumnya)
        RadCheck::where('username', $voucher->code)
            ->where('attribute', 'Auth-Type')
            ->delete();
    }

    /**
     * Tandai expired dan blokir akses di FreeRADIUS segera.
     */
    public function expire(Voucher $voucher): void
    {
        $voucher->update(['status' => 'expired']);

        RadCheck::updateOrCreate(
            ['username' => $voucher->code, 'attribute' => 'Auth-Type'],
            ['op' => ':=', 'value' => 'Reject']
        );

        RadCheck::where('username', $voucher->code)
            ->where('attribute', 'Cleartext-Password')
            ->update(['value' => Str::random(32)]);
    }

    /**
     * Nonaktifkan voucher secara manual (admin/operator).
     */
    public function disable(Voucher $voucher): void
    {
        $voucher->update(['status' => 'disabled']);

        RadCheck::updateOrCreate(
            ['username' => $voucher->code, 'attribute' => 'Auth-Type'],
            ['op' => ':=', 'value' => 'Reject']
        );

        RadCheck::where('username', $voucher->code)
            ->where('attribute', 'Cleartext-Password')
            ->update(['value' => Str::random(32)]);

        // Paksa expiration module reject — bekerja independen dari Auth-Type
        // (radgroupcheck punya Auth-Type := Accept yang override Reject di radcheck)
        RadCheck::updateOrCreate(
            ['username' => $voucher->code, 'attribute' => 'Expiration'],
            ['op' => ':=', 'value' => '01 Jan 2000 00:00:01']
        );
    }

    /**
     * Aktifkan kembali voucher yang disabled (admin only).
     */
    public function enable(Voucher $voucher): void
    {
        if ($voucher->status === 'expired') {
            return;
        }

        $voucher->update([
            'status' => $voucher->first_login_at ? 'active' : 'ready',
        ]);

        RadCheck::where('username', $voucher->code)
            ->where('attribute', 'Auth-Type')
            ->delete();

        RadCheck::updateOrCreate(
            ['username' => $voucher->code, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $voucher->password]
        );

        // Restore Expiration yang benar (atau hapus jika voucher belum pernah login)
        if ($voucher->first_login_at && $voucher->expired_at) {
            RadCheck::updateOrCreate(
                ['username' => $voucher->code, 'attribute' => 'Expiration'],
                ['op' => ':=', 'value' => Carbon::parse($voucher->expired_at)->format('d M Y H:i:s')]
            );
        } else {
            RadCheck::where('username', $voucher->code)
                ->where('attribute', 'Expiration')
                ->delete();
        }
    }

    /**
     * Hapus voucher dan semua entri RADIUS-nya.
     */
    public function delete(Voucher $voucher): void
    {
        DB::transaction(function () use ($voucher) {
            RadCheck::where('username', $voucher->code)->delete();
            RadReply::where('username', $voucher->code)->delete();
            RadUserGroup::where('username', $voucher->code)->delete();
            $voucher->delete();
        });
    }

    /**
     * Sinkronisasi dari FreeRADIUS:
     *  1. Aktifkan voucher ready yang sudah login pertama kali (via radpostauth).
     *  2. Tandai expired pada voucher yang melewati expired_at.
     *
     * @return array{activated: int, expired: int}
     */
    public function syncFromRadius(): array
    {
        $activated = 0;
        $expired   = 0;

        // 1. Aktifkan voucher yang baru pertama kali berhasil login
        $readyCodes = Voucher::where('status', 'ready')->pluck('code');

        if ($readyCodes->isNotEmpty()) {
            $firstLogins = DB::table('radpostauth')
                ->select('username', DB::raw('MIN(authdate) as first_login'))
                ->whereIn('username', $readyCodes)
                ->where('reply', 'like', '%Accept%')
                ->groupBy('username')
                ->get()
                ->keyBy('username');

            Voucher::where('status', 'ready')
                ->whereIn('code', $firstLogins->keys())
                ->each(function (Voucher $voucher) use ($firstLogins, &$activated) {
                    $row = $firstLogins->get($voucher->code);
                    $this->activate($voucher, Carbon::parse($row->first_login));
                    $activated++;
                });
        }

        // 2. Koreksi Expiration di radcheck untuk voucher active yang datanya tidak sinkron
        //    (terjadi saat migrasi dari sistem lama atau data radcheck ditulis manual)
        Voucher::where('status', 'active')
            ->whereNotNull('expired_at')
            ->each(function (Voucher $voucher) {
                $expected = Carbon::parse($voucher->expired_at)->format('d M Y H:i:s');
                RadCheck::where('username', $voucher->code)
                    ->where('attribute', 'Expiration')
                    ->where('value', '!=', $expected)
                    ->update(['op' => ':=', 'value' => $expected]);
            });

        // 3. Tandai expired yang sudah melewati batas waktu
        Voucher::where('status', 'active')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<', now())
            ->each(function (Voucher $voucher) use (&$expired) {
                $this->expire($voucher);
                $expired++;
            });

        return compact('activated', 'expired');
    }

    /**
     * Generate username unik — hanya huruf kapital & angka, tanpa tanda baca.
     * Dengan prefix: "{PREFIX}{4CHARS}" (e.g. "CAFEA3K9").
     * Tanpa prefix:  "{6CHARS}" (e.g. "A3K9B2").
     * Karakter: huruf & angka tanpa ambigu (no 0,O,I,1,l).
     */
    private function generateUsername(?string $prefix): string
    {
        $chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $suffix = $prefix ? 4 : 6;

        do {
            $rand = '';
            for ($i = 0; $i < $suffix; $i++) {
                $rand .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $code = ($prefix ?? '') . $rand;
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }

    /**
     * Generate password 4 digit numerik acak, zero-padded.
     */
    private function generatePassword(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
