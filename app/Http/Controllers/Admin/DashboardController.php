<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RadAcct;
use App\Models\RadCheck;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $blockedUsernames = RadCheck::where('attribute', 'Auth-Type')
            ->where('value', 'Reject')
            ->pluck('username');

        $stats = [
            'total_users'        => RadCheck::where('attribute', 'Cleartext-Password')->distinct('username')->count(),
            'active_users'       => RadCheck::where('attribute', 'Cleartext-Password')
                ->whereNotIn('username', $blockedUsernames)
                ->distinct('username')->count(),
            'online_sessions'    => RadAcct::whereNull('acctstoptime')->count(),
            'available_vouchers' => Voucher::where('status', 'ready')->count(),
        ];

        $recentLogs = ActivityLog::with('user')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLogs'));
    }

    public function stats(): JsonResponse
    {
        $blockedUsernames = RadCheck::where('attribute', 'Auth-Type')
            ->where('value', 'Reject')
            ->pluck('username');

        return response()->json([
            'total_users'        => RadCheck::where('attribute', 'Cleartext-Password')->distinct('username')->count(),
            'active_users'       => RadCheck::where('attribute', 'Cleartext-Password')
                ->whereNotIn('username', $blockedUsernames)
                ->distinct('username')->count(),
            'online_sessions'    => RadAcct::whereNull('acctstoptime')->count(),
            'available_vouchers' => Voucher::where('status', 'ready')->count(),
        ]);
    }
}
