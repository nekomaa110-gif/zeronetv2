<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public static function normalizePhone(string $raw): string
    {
        $n = preg_replace('/[^0-9]/', '', $raw);
        if (str_starts_with($n, '0'))   $n = '62' . substr($n, 1);
        if (str_starts_with($n, '620')) $n = '62' . substr($n, 3);
        return $n;
    }

    public function send(string $number, string $message): array
    {
        $cfg = config('services.whatsapp');
        try {
            $res = Http::withHeaders(['x-api-key' => $cfg['key']])
                ->timeout(15)
                ->post(rtrim($cfg['url'], '/').'/send', [
                    'number'  => $number,
                    'message' => $message,
                ]);
            $body = $res->json();
            $ok   = $res->successful() && (($body['ok'] ?? false) === true);
            Log::info('wa.send', [
                'to' => $number, 'ok' => $ok, 'http' => $res->status(), 'body' => $body,
            ]);
            return ['ok' => $ok, 'http' => $res->status(), 'body' => $body];
        } catch (\Throwable $e) {
            Log::error('wa.send.exception', ['err' => $e->getMessage(), 'to' => $number]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function status(): array
    {
        $cfg = config('services.whatsapp');
        try {
            $res = Http::withHeaders(['x-api-key' => $cfg['key']])
                ->timeout(5)
                ->get(rtrim($cfg['url'], '/').'/status');
            return $res->json() ?: ['ok' => false, 'status' => 'unreachable'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => 'unreachable', 'error' => $e->getMessage()];
        }
    }
}
