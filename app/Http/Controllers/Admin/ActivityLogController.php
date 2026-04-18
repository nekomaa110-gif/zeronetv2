<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $search   = $request->input('search', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo   = $request->input('date_to', '');

        $logs = ActivityLog::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('action', 'like', "%{$search}%")
                       ->orWhere('description', 'like', "%{$search}%")
                       ->orWhereHas('user', fn ($u) =>
                           $u->where('name', 'like', "%{$search}%")
                             ->orWhere('username', 'like', "%{$search}%")
                       );
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-logs.index', compact('logs', 'search', 'dateFrom', 'dateTo'));
    }
}
