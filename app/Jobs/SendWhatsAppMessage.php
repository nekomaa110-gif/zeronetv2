<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;
    public int $timeout = 60;

    public function __construct(
        public string $number,
        public string $message,
        public ?int $contactId = null,
    ) {}

    public function handle(WhatsAppService $wa): void
    {
        $r = $wa->send($this->number, $this->message);
        if (!($r['ok'] ?? false)) {
            // Nomor bukan WA → jangan retry
            $err = $r['body']['error'] ?? null;
            if ($err === 'number_not_on_whatsapp') {
                return;
            }
            throw new \RuntimeException('WA send failed: '.json_encode($r));
        }
        if ($this->contactId) {
            \App\Models\CustomerContact::where('id', $this->contactId)
                ->update(['reminder_sent_at' => now()]);
        }
    }
}
