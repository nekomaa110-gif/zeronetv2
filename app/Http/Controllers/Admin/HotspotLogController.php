<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RadPostAuth;
use Illuminate\Http\Request;
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

        return view('admin.hotspot-logs.index', compact('logs', 'search', 'status', 'dateFrom', 'dateTo'));
    }
}
