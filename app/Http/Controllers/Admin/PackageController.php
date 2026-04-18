<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\Package;
use App\Services\ActivityLogService;
use App\Services\PackageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(private PackageService $service) {}

    public function index(): View
    {
        $packages = $this->service->all();
        return view('admin.packages.index', compact('packages'));
    }

    public function create(): View
    {
        return view('admin.packages.form', [
            'package'   => null,
            'isUpdate'  => false,
            'presets'   => PackageService::ATTRIBUTE_PRESETS,
            'operators' => PackageService::OPERATORS,
            'targets'   => PackageService::TARGET_TABLES,
        ]);
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        $package = $this->service->create($request->validated());

        ActivityLogService::log('create', "membuat paket: {$package->groupname}", 'package', $package->groupname);

        return redirect()
            ->route('admin.packages.index')
            ->with('success', "Paket \"{$package->groupname}\" berhasil dibuat.");
    }

    public function edit(Package $package): View
    {
        return view('admin.packages.form', [
            'package'   => $this->service->find($package),
            'isUpdate'  => true,
            'presets'   => PackageService::ATTRIBUTE_PRESETS,
            'operators' => PackageService::OPERATORS,
            'targets'   => PackageService::TARGET_TABLES,
        ]);
    }

    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        $this->service->update($package, $request->validated());

        ActivityLogService::log('update', "mengupdate paket: {$package->groupname}", 'package', $package->groupname);

        return redirect()
            ->route('admin.packages.index')
            ->with('success', "Paket \"{$package->groupname}\" berhasil diperbarui.");
    }

    public function toggle(Package $package): RedirectResponse
    {
        $this->service->toggle($package);
        $status = $package->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLogService::log('update', "{$status} paket: {$package->groupname}", 'package', $package->groupname);

        return back()->with('success', "Paket \"{$package->groupname}\" berhasil {$status}.");
    }

    public function destroy(Package $package): RedirectResponse
    {
        $groupname = $package->groupname;
        $this->service->delete($package);

        ActivityLogService::log('delete', "menghapus paket: {$groupname}", 'package', $groupname);

        return redirect()
            ->route('admin.packages.index')
            ->with('success', "Paket \"{$groupname}\" berhasil dihapus.");
    }

    /**
     * Auto-import profil lama dari radius tables ke packages table,
     * lalu redirect ke form edit normal.
     */
    public function legacyEdit(string $groupname): RedirectResponse
    {
        if (! $this->service->existsInRadius($groupname)) {
            abort(404, 'Profil tidak ditemukan.');
        }

        // Sudah diimport sebelumnya? Langsung ke edit.
        $package = Package::where('groupname', $groupname)->first()
            ?? $this->service->importFromRadius($groupname);

        ActivityLogService::log(
            'create',
            "mengimpor profil dari panel lama: {$groupname}",
            'package',
            $groupname,
        );

        return redirect()->route('admin.packages.edit', $package);
    }
}
