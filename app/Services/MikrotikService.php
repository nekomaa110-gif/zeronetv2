<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;

class MikrotikService
{
    /** @var array<string, MikrotikClient> */
    private array $pool = [];

    // ─── Config ───────────────────────────────────────────────────────────

    public function routers(): array
    {
        return config('mikrotik.routers', []);
    }

    public function routerConfig(string $id): array
    {
        $cfg = config("mikrotik.routers.{$id}");
        abort_if(! $cfg, 404, 'Router tidak ditemukan.');
        return $cfg;
    }

    // ─── Connection pool ──────────────────────────────────────────────────

    private function client(string $id): MikrotikClient
    {
        if (! isset($this->pool[$id])) {
            $c = $this->routerConfig($id);
            $this->pool[$id] = new MikrotikClient(
                $c['host'],
                $c['user'],
                $c['pass'],
                $c['port'],
                $c['timeout']
            );
        }
        return $this->pool[$id];
    }

    // ─── Status & Stats ───────────────────────────────────────────────────

    public function isOnline(string $id): bool
    {
        try {
            $this->client($id)->query('/system/identity/print');
            return true;
        } catch (Exception) {
            return false;
        }
    }

    public function stats(string $id): array
    {
        $c   = $this->client($id);
        $res = $c->query('/system/resource/print')[0] ?? [];
        $idn = $c->query('/system/identity/print')[0]  ?? [];

        $totalMem = (int) ($res['total-memory']   ?? 0);
        $freeMem  = (int) ($res['free-memory']     ?? 0);
        $usedMem  = $totalMem - $freeMem;

        $totalHdd = (int) ($res['total-hdd-space'] ?? 0);
        $freeHdd  = (int) ($res['free-hdd-space']  ?? 0);
        $usedHdd  = $totalHdd - $freeHdd;

        return [
            'identity'  => $idn['name']      ?? '-',
            'uptime'    => $res['uptime']     ?? '-',
            'version'   => $res['version']    ?? '-',
            'board'     => $res['board-name'] ?? '-',
            'cpu_load'  => (int) ($res['cpu-load'] ?? 0),
            'total_mem' => $totalMem,
            'used_mem'  => $usedMem,
            'mem_pct'   => $totalMem > 0 ? round($usedMem / $totalMem * 100) : 0,
            'total_hdd' => $totalHdd,
            'used_hdd'  => $usedHdd,
            'hdd_pct'   => $totalHdd > 0 ? round($usedHdd / $totalHdd * 100) : 0,
        ];
    }

    // ─── Traffic ──────────────────────────────────────────────────────────

    public function trafficInterface(string $id, string $interface = 'ether1'): array
    {
        // Fail-fast: kalau router baru saja gagal dihubungi, jangan coba lagi
        // selama 15 detik. Hemat worker PHP-FPM saat router down.
        if (Cache::has("router.down.{$id}")) {
            throw new Exception('Router tidak dapat dihubungi (cached).');
        }

        try {
            $res = $this->client($id)->query('/interface/print', ['?name' => $interface]);
        } catch (Exception $e) {
            Cache::put("router.down.{$id}", true, 15);
            throw $e;
        }

        $data = $res[0] ?? [];

        $rxBytes = (int) ($data['rx-byte'] ?? 0);
        $txBytes = (int) ($data['tx-byte'] ?? 0);
        $now     = microtime(true);

        $cacheKey = "traffic.{$id}.{$interface}";
        $prev     = Cache::get($cacheKey);

        Cache::put($cacheKey, ['rx' => $rxBytes, 'tx' => $txBytes, 'ts' => $now], 120);

        if (!$prev || ($now - $prev['ts']) <= 0) {
            return ['download' => 0, 'upload' => 0];
        }

        // Counter reset (interface/router restart) → abaikan sampel ini
        if ($rxBytes < $prev['rx'] || $txBytes < $prev['tx']) {
            return ['download' => 0, 'upload' => 0];
        }

        $elapsed  = $now - $prev['ts'];
        $download = (int) round(($rxBytes - $prev['rx']) / $elapsed * 8);
        $upload   = (int) round(($txBytes - $prev['tx']) / $elapsed * 8);

        return [
            'download' => max(0, $download),
            'upload'   => max(0, $upload),
        ];
    }

    // ─── Hotspot Users ────────────────────────────────────────────────────

    public function hotspotUsers(string $id): array
    {
        return $this->client($id)->query('/ip/hotspot/active/print') ?: [];
    }

    public function disconnectUser(string $id, string $sessionId): void
    {
        $this->client($id)->query('/ip/hotspot/active/remove', ['=.id' => $sessionId]);
    }

    // ─── Router Actions ───────────────────────────────────────────────────

    public function reboot(string $id): void
    {
        try {
            $this->client($id)->query('/system/reboot');
        } catch (Exception) {
            // Koneksi putus seketika saat reboot — ini normal
        }
    }

    /**
     * Simpan backup di router lalu unduh via FTP.
     * Pastikan FTP aktif di MikroTik: /ip/service set ftp disabled=no
     */
    public function downloadBackup(string $id): string
    {
        $cfg  = $this->routerConfig($id);
        $name = 'bak-' . date('YmdHis');

        $this->client($id)->query('/system/backup/save', [
            '=name'          => $name,
            '=dont-encrypt'  => 'yes',
        ]);

        sleep(3);

        $ftp = @ftp_connect($cfg['host'], 21, 10);
        if (! $ftp) {
            throw new Exception('Tidak dapat membuka koneksi FTP. Pastikan FTP aktif: /ip/service set ftp disabled=no');
        }

        if (! @ftp_login($ftp, $cfg['user'], $cfg['pass'])) {
            ftp_close($ftp);
            throw new Exception('Login FTP gagal. Cek kredensial atau izin FTP user.');
        }

        ftp_pasv($ftp, true);

        $tmp = tempnam(sys_get_temp_dir(), 'mt-bak-');
        $ok  = @ftp_get($ftp, $tmp, "{$name}.backup", FTP_BINARY);
        ftp_close($ftp);

        if (! $ok) {
            @unlink($tmp);
            throw new Exception('Gagal mengunduh file backup dari router.');
        }

        $content = file_get_contents($tmp);
        @unlink($tmp);

        // Hapus file backup sementara dari router
        try {
            $files = $this->client($id)->query('/file/print');
            foreach ($files as $file) {
                if (($file['name'] ?? '') === "{$name}.backup") {
                    $this->client($id)->query('/file/remove', ['=.id' => $file['.id']]);
                    break;
                }
            }
        } catch (Exception) {
        }

        return $content;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    public static function bytes(int $bytes): string
    {
        return match (true) {
            $bytes >= 1_073_741_824 => round($bytes / 1_073_741_824, 1) . ' GB',
            $bytes >= 1_048_576     => round($bytes / 1_048_576,     1) . ' MB',
            $bytes >= 1_024         => round($bytes / 1_024,         1) . ' KB',
            default                 => $bytes . ' B',
        };
    }
}
