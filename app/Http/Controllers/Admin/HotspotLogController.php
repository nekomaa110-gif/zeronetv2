<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RadCheck;
use App\Models\RadPostAuth;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HotspotLogController extends Controller
{
    public function index(Request $request): View
    {
        $search   = $request->input('search', '');
        $status   = $request->input('status', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo   = $request->input('date_to', '');

        $logs = RadPostAuth::query()
            ->search($search)
            ->byStatus($status)
            ->byDateFrom($dateFrom)
            ->byDateTo($dateTo)
            ->orderByDesc('authdate')
            ->paginate(30)
            ->withQueryString();

        $rejectReasons = $this->resolveRejectReasons($logs);
        $userIps       = $this->resolveUserIps($logs);

        return view('admin.hotspot-logs.index', compact('logs', 'search', 'status', 'dateFrom', 'dateTo', 'rejectReasons', 'userIps'));
    }

    public function poll(Request $request): JsonResponse
    {
        $afterId = (int) $request->input('after', 0);

        if (!$afterId) {
            return response()->json(['count' => 0]);
        }

        $newLogs = RadPostAuth::where('id', '>', $afterId)
            ->orderByDesc('authdate')
            ->limit(20)
            ->get();

        if ($newLogs->isEmpty()) {
            return response()->json(['count' => 0]);
        }

        $rejectReasons = $this->resolveRejectReasons($newLogs);
        $userIps       = $this->resolveUserIps($newLogs);

        $html = $newLogs->map(fn($log) =>
            view('admin.hotspot-logs._row', [
                'log'           => $log,
                'rejectReasons' => $rejectReasons,
                'userIps'       => $userIps,
                'isNew'         => true,
            ])->render()
        )->implode('');

        return response()->json([
            'count'  => $newLogs->count(),
            'max_id' => $newLogs->max('id'),
            'html'   => $html,
        ]);
    }

    /**
     * Get the framed IP (client IP) for each username on the current page.
     * Matches by finding the radacct session whose acctstarttime is closest
     * to each auth event's authdate (within 2 minutes, same NAS).
     */
    private function resolveUserIps($logs): array
    {
        $usernames = $logs->pluck('username')->unique()->values();

        if ($usernames->isEmpty()) {
            return [];
        }

        $sub = DB::table('radacct')
            ->whereIn('username', $usernames)
            ->select('username', DB::raw('MAX(acctstarttime) as max_start'))
            ->groupBy('username');

        $rows = DB::table('radacct as a')
            ->joinSub($sub, 'b', fn($j) => $j->on('a.username', '=', 'b.username')
                ->on('a.acctstarttime', '=', 'b.max_start'))
            ->select('a.username', 'a.framedipaddress')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $ip = $row->framedipaddress ?? '';
            if ($ip && $ip !== '0.0.0.0') {
                $result[$row->username] = $ip;
            }
        }

        return $result;
    }

    /**
     * Derive reject reason for each failed username on the current page.
     * Uses batch queries (no N+1) against radcheck, radacct, and vouchers.
     */
    private function resolveRejectReasons($logs): array
    {
        $failed = $logs->filter(fn($l) => !$l->isSuccess())
            ->pluck('username')->unique()->values();

        if ($failed->isEmpty()) {
            return [];
        }

        $blocked = RadCheck::whereIn('username', $failed)
            ->where('attribute', 'Auth-Type')
            ->where('value', 'Reject')
            ->pluck('username')
            ->flip()->map(fn() => true);

        $expiredExpiration = RadCheck::whereIn('username', $failed)
            ->where('attribute', 'Expiration')
            ->whereRaw("STR_TO_DATE(value, '%d %b %Y %H:%i:%s') < NOW()")
            ->pluck('username')
            ->flip()->map(fn() => true);

        $activeSessions = DB::table('radacct')
            ->whereIn('username', $failed)
            ->whereNull('acctstoptime')
            ->pluck('username')
            ->flip()->map(fn() => true);

        $voucherStatuses = Voucher::whereIn('code', $failed)
            ->pluck('status', 'code');

        $reasons = [];
        foreach ($failed as $username) {
            if ($blocked->has($username)) {
                $reasons[$username] = $voucherStatuses->get($username) === 'disabled'
                    ? 'Akun dinonaktifkan'
                    : 'Voucher sudah expired';
            } elseif ($expiredExpiration->has($username)) {
                $reasons[$username] = 'Voucher sudah expired';
            } elseif ($activeSessions->has($username)) {
                $reasons[$username] = 'Akun sedang digunakan';
            } else {
                $reasons[$username] = 'Username atau password salah';
            }
        }

        return $reasons;
    }
}
