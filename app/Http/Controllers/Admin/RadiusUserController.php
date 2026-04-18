<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RadiusUserRequest;
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
        $users  = $this->service->paginate($search, 15, $group);
        $groups = $this->service->availableGroups();

        return view('admin.radius-users.index', compact('users', 'search', 'group', 'groups'));
    }

    public function create(): View
    {
        $groups = $this->service->availableGroups();
        return view('admin.radius-users.create', compact('groups'));
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
            ->route('admin.radius-users.index')
            ->with('success', "User {$data['username']} berhasil dibuat.");
    }

    public function edit(string $username): View
    {
        $user   = $this->service->find($username);
        $groups = $this->service->availableGroups();
        return view('admin.radius-users.edit', compact('user', 'groups'));
    }

    public function update(RadiusUserRequest $request, string $username): RedirectResponse
    {
        $data = $request->validated();
        $this->service->update($username, $data);

        ActivityLogService::log(
            'update',
            "mengupdate user hotspot: {$username}",
            'radius_user',
            $username
        );

        return redirect()
            ->route('admin.radius-users.index')
            ->with('success', "User {$username} berhasil diperbarui.");
    }

    public function destroy(string $username): RedirectResponse
    {
        $this->service->delete($username);

        ActivityLogService::log(
            'delete',
            "menghapus user hotspot: {$username}",
            'radius_user',
            $username
        );

        return redirect()
            ->route('admin.radius-users.index')
            ->with('success', "User {$username} berhasil dihapus.");
    }

    public function toggle(string $username): RedirectResponse
    {
        $nowActive = $this->service->toggle($username);
        $status    = $nowActive ? 'diaktifkan' : 'dinonaktifkan';

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
