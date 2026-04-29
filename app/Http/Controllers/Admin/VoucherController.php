<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VoucherRequest;
use App\Models\Package;
use App\Models\Voucher;
use App\Services\ActivityLogService;
use App\Services\VoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function __construct(private readonly VoucherService $service) {}

    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $type   = $request->input('type', '');

        $vouchers = Voucher::with(['package', 'creator'])
            ->search($search)
            ->byStatus($status)
            ->byType($type)
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('vouchers.index', [
            'vouchers' => $vouchers,
            'search'   => $search,
            'status'   => $status,
            'type'     => $type,
            'types'    => VoucherService::TYPES,
        ]);
    }

    public function create(): View
    {
        return view('vouchers.create', [
            'packages' => Package::where('is_active', true)->orderBy('groupname')->get(),
            'types'    => VoucherService::TYPES,
        ]);
    }

    public function store(VoucherRequest $request): RedirectResponse
    {
        $type     = $request->input('type');
        $count    = $request->integer('count', 1);
        $vouchers = $this->service->generate(
            $count, $type,
            $request->integer('package_id'),
            $request->input('note'),
            $request->input('prefix')
        );

        ActivityLogService::log(
            'generate_voucher',
            "Generate {$vouchers->count()} voucher tipe " . VoucherService::TYPES[$type]['label'],
            'Voucher',
            null
        );

        return redirect()->route('vouchers.index')
            ->with('success', "Berhasil membuat {$vouchers->count()} voucher.");
    }

    public function disable(Voucher $voucher): RedirectResponse
    {
        if (in_array($voucher->status, ['expired', 'disabled'])) {
            return back()->with('error', 'Voucher tidak dapat dinonaktifkan.');
        }

        $this->service->disable($voucher);
        ActivityLogService::log('disable_voucher', "Voucher {$voucher->code} dinonaktifkan", 'Voucher', $voucher->id);

        return back()->with('success', "Voucher {$voucher->code} dinonaktifkan.");
    }

    public function enable(Voucher $voucher): RedirectResponse
    {
        if ($voucher->status === 'expired') {
            return back()->with('error', 'Voucher expired tidak dapat diaktifkan kembali.');
        }

        $this->service->enable($voucher);
        ActivityLogService::log('enable_voucher', "Voucher {$voucher->code} diaktifkan kembali", 'Voucher', $voucher->id);

        return back()->with('success', "Voucher {$voucher->code} diaktifkan kembali.");
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $code = $voucher->code;
        $this->service->delete($voucher);
        ActivityLogService::log('delete_voucher', "Voucher {$code} dihapus", 'Voucher', null);

        return back()->with('success', "Voucher {$code} berhasil dihapus.");
    }

    public function print(Request $request): View
    {
        $vouchers = Voucher::with('package')
            ->when($request->boolean('print_all'), function ($q) use ($request) {
                // Print semua hasil filter (lintas halaman)
                if ($request->filled('status')) $q->byStatus($request->input('status'));
                if ($request->filled('type'))   $q->byType($request->input('type'));
                if ($request->filled('search')) $q->search($request->input('search'));
            }, function ($q) use ($request) {
                // Print by ID terpilih
                $ids = array_filter(explode(',', $request->input('ids', '')));
                if (!empty($ids)) $q->whereIn('id', $ids);
            })
            ->orderBy('created_at')
            ->limit(500)
            ->get();

        return view('vouchers.print', compact('vouchers'));
    }
}
