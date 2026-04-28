<?php

namespace App\Jobs;

use App\Services\ActivityLogService;
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
        public ?int $userId = null,
    ) {}

    public function handle(WhatsAppService $wa): void
    {
        $r = $wa->send($this->number, $this->message);

        if (!($r['ok'] ?? false)) {
            $err = $r['body']['error'] ?? null;
            if ($err === 'number_not_on_whatsapp') {
                ActivityLogService::log(
                    action: 'wa_send',
                    description: "WA tidak terkirim ke {$this->number}: nomor bukan WhatsApp",
                    subjectType: 'wa_message',
                    subjectId: $this->contactId ? (string) $this->contactId : null,
                    properties: ['status' => 'not_on_whatsapp', 'number' => $this->number],
                    userId: $this->userId,
                );
                return;
            }
            ActivityLogService::log(
                action: 'wa_send',
                description: "WA gagal kirim ke {$this->number}",
                subjectType: 'wa_message',
                subjectId: $this->contactId ? (string) $this->contactId : null,
                properties: ['status' => 'failed', 'number' => $this->number, 'error' => $err],
                userId: $this->userId,
            );
            throw new \RuntimeException('WA send failed: '.json_encode($r));
        }

        if ($this->contactId) {
            \App\Models\CustomerContact::where('id', $this->contactId)
                ->update(['reminder_sent_at' => now()]);
        }

        ActivityLogService::log(
            action: 'wa_send',
            description: "WA terkirim ke {$this->number}",
            subjectType: 'wa_message',
            subjectId: $this->contactId ? (string) $this->contactId : null,
            properties: [
                'status'     => 'sent',
                'number'     => $this->number,
                'message_id' => $r['body']['id'] ?? null,
            ],
            userId: $this->userId,
        );
    }
}
