<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RadiusUserRequest;
use App\Models\Voucher;
use App\Services\ActivityLogService;
use App\Services\RadiusUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RadiusUserController extends Controller
{
    public function __construct(private RadiusUserService $service) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $group  = $request->string('group')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $users  = $this->service->paginate($search, 15, $group, $status);

        if ($request->ajax()) {
            return view('user-hotspot._results', compact('users', 'search', 'group', 'status'));
        }

        $groups = $this->service->availableGroups();
        $stats  = $this->service->stats();

        return view('user-hotspot.index', compact('users', 'search', 'group', 'status', 'groups', 'stats'));
    }

    public function create(): View
    {
        $groups = $this->service->availableGroups();
        return view('user-hotspot.create', compact('groups'));
    }

    public function store(RadiusUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->service->create($data);

        ActivityLogService::log(
            'create',
            "membuat user hotspot: {$data['username']}",
            'radius_user',
            $data['username'],
            ['group' => $data['group'] ?? null]
        );

        return redirect()
            ->route('user-hotspot.index')
            ->with('success', "User {$data['username']} berhasil dibuat.");
    }

    public function edit(string $username): View
    {
        $user   = $this->service->find($username);
        $groups = $this->service->availableGroups();
        return view('user-hotspot.edit', compact('user', 'groups'));
    }

    public function update(RadiusUserRequest $request, string $username): RedirectResponse
    {
        $data = $request->validated();
        $this->service->update($username, $data);

        // Sync expired_at ke tabel vouchers jika username ini adalah voucher
        $voucher = Voucher::where('code', $username)->first();
        if ($voucher && ! empty($data['expiry'])) {
            $newExpiry = \Carbon\Carbon::parse($data['expiry'])->setTime(23, 59, 59);
            $voucher->update(['expired_at' => $newExpiry]);
        }

        ActivityLogService::log(
            'update',
            "mengupdate user hotspot: {$username}",
            'radius_user',
            $username
        );

        return redirect()
            ->route('user-hotspot.index')
            ->with('success', "User {$username} berhasil diperbarui.");
    }

    public function destroy(string $username): RedirectResponse
    {
        $this->service->delete($username);

        Voucher::where('code', $username)->delete();

        ActivityLogService::log(
            'delete',
            "menghapus user hotspot: {$username}",
            'radius_user',
            $username
        );

        return back()->with('success', "User {$username} berhasil dihapus.");
    }

    public function toggle(string $username): RedirectResponse
    {
        $nowActive = $this->service->toggle($username);
        $status    = $nowActive ? 'diaktifkan' : 'dinonaktifkan';

        // Sync status ke tabel vouchers jika username ini adalah voucher
        $voucher = Voucher::where('code', $username)->first();
        if ($voucher && ! in_array($voucher->status, ['expired'])) {
            if ($nowActive) {
                $voucher->update([
                    'status' => $voucher->first_login_at ? 'active' : 'ready',
                ]);
            } else {
                $voucher->update(['status' => 'disabled']);
            }
        }

        ActivityLogService::log(
            'update',
            "{$status} user hotspot: {$username}",
            'radius_user',
            $username,
            ['active' => $nowActive]
        );

        return back()->with('success', "User {$username} berhasil {$status}.");
    }
}
