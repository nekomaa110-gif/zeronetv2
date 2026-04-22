<?php

namespace App\Services;

use Exception;

/**
 * Minimal RouterOS API client (RFC-compatible, no external package needed).
 * Supports both new-style login (RouterOS ≥ 6.43) and legacy MD5 challenge.
 */
class MikrotikClient
{
    /** @var resource */
    private $socket;

    public function __construct(
        string $host,
        string $user,
        string $pass,
        int    $port    = 8728,
        int    $timeout = 5
    ) {
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if (! is_resource($this->socket)) {
            throw new Exception("Tidak dapat terhubung ke {$host}:{$port} — {$errstr}");
        }

        stream_set_timeout($this->socket, $timeout);
        $this->login($user, $pass);
    }

    // ─── Protocol layer ───────────────────────────────────────────────────

    private function encodeLen(int $n): string
    {
        if ($n < 0x80)       return chr($n);
        if ($n < 0x4000)     return pack('n', $n | 0x8000);
        if ($n < 0x200000)   return substr(pack('N', $n | 0xC00000), 1);
        if ($n < 0x10000000) return pack('N', $n | 0xE0000000);
        return chr(0xF0) . pack('N', $n);
    }

    private function writeSentence(array $words): void
    {
        $buf = '';
        foreach ($words as $w) {
            $buf .= $this->encodeLen(strlen($w)) . $w;
        }
        $buf .= chr(0);
        fwrite($this->socket, $buf);
    }

    private function readLen(): int
    {
        $b = ord(fread($this->socket, 1));
        if (($b & 0x80) === 0) return $b;
        if (($b & 0xC0) === 0x80) return (($b & 0x3F) << 8) | ord(fread($this->socket, 1));
        if (($b & 0xE0) === 0xC0) {
            $r = unpack('n', fread($this->socket, 2))[1];
            return (($b & 0x1F) << 16) | $r;
        }
        if (($b & 0xF0) === 0xE0) {
            $r = unpack('N', chr(0) . fread($this->socket, 3))[1];
            return (($b & 0x0F) << 24) | $r;
        }
        return unpack('N', fread($this->socket, 4))[1];
    }

    private function readWord(): string
    {
        $len = $this->readLen();
        if ($len === 0) return '';
        $buf = '';
        while (strlen($buf) < $len) {
            $chunk = fread($this->socket, $len - strlen($buf));
            if ($chunk === false || $chunk === '') break;
            $buf .= $chunk;
        }
        return $buf;
    }

    private function readSentence(): array
    {
        $words = [];
        while (true) {
            $w = $this->readWord();
            if ($w === '') break;
            $words[] = $w;
        }
        return $words;
    }

    // ─── Login ────────────────────────────────────────────────────────────

    private function login(string $user, string $pass): void
    {
        $this->writeSentence(['/login', '=name=' . $user, '=password=' . $pass]);
        $sentence = $this->readSentence();

        if (empty($sentence)) {
            throw new Exception('Tidak ada respons dari router saat login.');
        }

        if ($sentence[0] === '!done') {
            // Check for legacy MD5 challenge (RouterOS < 6.43)
            $ret = null;
            foreach ($sentence as $word) {
                if (str_starts_with($word, '=ret=')) {
                    $ret = substr($word, 5);
                }
            }

            if ($ret !== null) {
                $response = '00' . md5(chr(0) . $pass . pack('H*', $ret));
                $this->writeSentence(['/login', '=name=' . $user, '=response=' . $response]);
                $s2 = $this->readSentence();
                if (($s2[0] ?? '') !== '!done') {
                    throw new Exception('Login gagal (challenge-response).');
                }
            }
            return;
        }

        if ($sentence[0] === '!trap') {
            $msg = '';
            foreach ($sentence as $word) {
                if (str_starts_with($word, '=message=')) $msg = substr($word, 9);
            }
            throw new Exception('Login gagal: ' . ($msg ?: 'salah username/password'));
        }

        throw new Exception('Respons login tidak terduga: ' . ($sentence[0] ?? 'kosong'));
    }

    // ─── Public query API ─────────────────────────────────────────────────

    /**
     * Run a RouterOS API command and return array of result rows (each row = assoc array).
     *
     * @param  array<string, string|null>  $params  '=key' => 'value', or '?key' => 'value' for queries
     */
    public function query(string $cmd, array $params = []): array
    {
        $words = [$cmd];
        foreach ($params as $key => $value) {
            $words[] = $value !== null ? "{$key}={$value}" : $key;
        }
        $this->writeSentence($words);

        $result  = [];
        $current = null;

        while (true) {
            $sentence = $this->readSentence();
            if (empty($sentence)) continue;

            $type = $sentence[0];

            if ($type === '!done') {
                if ($current !== null) $result[] = $current;
                break;
            }

            if ($type === '!trap' || $type === '!fatal') {
                $msg = '';
                foreach ($sentence as $word) {
                    if (str_starts_with($word, '=message=')) $msg = substr($word, 9);
                }
                throw new Exception("RouterOS error: {$msg}");
            }

            if ($type === '!re') {
                if ($current !== null) $result[] = $current;
                $current = [];
                foreach (array_slice($sentence, 1) as $word) {
                    if (str_starts_with($word, '=')) {
                        [$k, $v] = array_pad(explode('=', substr($word, 1), 2), 2, '');
                        $current[$k] = $v;
                    }
                }
            }
        }

        return $result;
    }

    public function __destruct()
    {
        if (is_resource($this->socket)) {
            @fclose($this->socket);
        }
    }
}
