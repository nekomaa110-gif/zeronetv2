<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RadAcct;
use App\Models\RadCheck;
use App\Models\Voucher;
use App\Services\MikrotikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private MikrotikService $mikrotik) {}

    public function index(): View
    {
        $stats   = $this->computeStats();
        $routers = collect($this->mikrotik->routers())
            ->map(fn ($cfg, $id) => ['id' => $id, ...$cfg])
            ->values();

        return view('admin.dashboard', compact('stats', 'routers'));
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->computeStats());
    }

    private function computeStats(): array
    {
        return Cache::remember('dashboard.stats', 60, function () {
            $blocked = fn ($q) => $q->select('username')->from('radcheck')
                ->where('attribute', 'Auth-Type')
                ->where('value', 'Reject');

            return [
                'total_users'        => RadCheck::where('attribute', 'Cleartext-Password')
                                            ->distinct('username')->count(),
                'active_users'       => RadCheck::where('attribute', 'Cleartext-Password')
                                            ->whereNotIn('username', $blocked)
                                            ->distinct('username')->count(),
                'online_sessions'    => RadAcct::whereNull('acctstoptime')->count(),
                'available_vouchers' => Voucher::where('status', 'ready')->count(),
            ];
        });
    }
}
