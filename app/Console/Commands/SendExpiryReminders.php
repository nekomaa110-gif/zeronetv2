<?php

namespace App\Console\Commands;

use App\Jobs\SendWhatsAppMessage;
use App\Models\CustomerContact;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendExpiryReminders extends Command
{
    protected $signature = 'wa:reminders {--dry : Tampilkan target tanpa kirim}';
    protected $description = 'Kirim reminder H-1 ke pelanggan yang Expiration di radcheck akan habis';

    public function handle(): int
    {
        $hours = (int) config('services.whatsapp.reminder_hours', 24);
        $now   = now();
        $from  = $now->copy()->addHours($hours - 1);
        $to    = $now->copy()->addHours($hours + 1);

        // radcheck.value disimpan sbg string "27 Apr 2026 23:59:59"
        $rows = DB::table('radcheck')
            ->join('customer_contacts', 'customer_contacts.username', '=', 'radcheck.username')
            ->where('radcheck.attribute', 'Expiration')
            ->whereNull('customer_contacts.reminder_sent_at')
            ->whereRaw("STR_TO_DATE(radcheck.value, '%d %b %Y %H:%i:%s') BETWEEN ? AND ?", [
                $from->format('Y-m-d H:i:s'),
                $to->format('Y-m-d H:i:s'),
            ])
            ->select(
                'customer_contacts.id as contact_id',
                'customer_contacts.username',
                'customer_contacts.name',
                'customer_contacts.phone',
                'radcheck.value as expiry_raw',
            )
            ->get();

        if ($rows->isEmpty()) {
            $this->info('Tidak ada target reminder.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($rows as $r) {
            $expiry = $this->parseExpiry($r->expiry_raw);
            $name   = $r->name ?: $r->username;
            $msg    = "Halo {$name},\n"
                   . "Paket internet Anda (user: {$r->username}) akan habis pada "
                   . ($expiry ? $expiry->format('d M Y H:i') : $r->expiry_raw) . ".\n"
                   . "Silakan perpanjang sebelum jatuh tempo agar koneksi tidak terputus.\n\n"
                   . "Terima kasih.";

            if ($this->option('dry')) {
                $this->line("[DRY] {$r->phone} ({$r->username}) — habis {$r->expiry_raw}");
            } else {
                SendWhatsAppMessage::dispatch($r->phone, $msg, $r->contact_id)
                    ->delay(now()->addSeconds(rand(2, 30)));
            }
            $count++;
        }

        $this->info(($this->option('dry') ? '[DRY] ' : '') . "Total {$count} reminder.");
        return self::SUCCESS;
    }

    private function parseExpiry(string $raw): ?Carbon
    {
        try {
            return Carbon::createFromFormat('d M Y H:i:s', trim($raw));
        } catch (\Throwable) {
            return null;
        }
    }
}
