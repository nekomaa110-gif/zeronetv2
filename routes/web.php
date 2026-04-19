<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HotspotLogController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\RadiusUserController;
use App\Http\Controllers\Admin\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'ensure.admin'])
    ->group(function () {

        // ── Dashboard (semua role) ────────────────────────────────────────
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

        // ── User Hotspot ─────────────────────────────────────────────────
        // Operator: lihat, tambah, edit, toggle (semua kecuali hapus)
        Route::resource('radius-users', RadiusUserController::class)
            ->parameters(['radius-users' => 'radius_user'])
            ->except(['show', 'destroy']);
        Route::patch('radius-users/{radius_user}/toggle', [RadiusUserController::class, 'toggle'])
            ->name('radius-users.toggle');

        // Admin only: hapus user
        Route::delete('radius-users/{radius_user}', [RadiusUserController::class, 'destroy'])
            ->name('radius-users.destroy')
            ->middleware('role:admin');

        // ── Paket / Profile RADIUS ────────────────────────────────────────
        // Operator: lihat saja
        Route::get('packages', [PackageController::class, 'index'])->name('packages.index');

        // Admin only: semua operasi tulis
        Route::middleware('role:admin')->group(function () {
            Route::get('packages/create', [PackageController::class, 'create'])->name('packages.create');
            Route::post('packages', [PackageController::class, 'store'])->name('packages.store');
            // Import profil lama → HARUS sebelum {package} agar tidak di-bind sebagai model
            Route::get('packages/legacy/{groupname}/edit', [PackageController::class, 'legacyEdit'])->name('packages.legacy-edit');
            Route::get('packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
            Route::put('packages/{package}', [PackageController::class, 'update'])->name('packages.update');
            Route::delete('packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');
            Route::patch('packages/{package}/toggle', [PackageController::class, 'toggle'])->name('packages.toggle');
        });

        // ── Voucher ───────────────────────────────────────────────────────
        // Operator: lihat, generate, print, disable
        Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
        Route::get('/vouchers/print', [VoucherController::class, 'print'])->name('vouchers.print');
        Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
        Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
        Route::patch('/vouchers/{voucher}/disable', [VoucherController::class, 'disable'])->name('vouchers.disable');

        // Admin only: enable & hapus voucher
        Route::middleware('role:admin')->group(function () {
            Route::patch('/vouchers/{voucher}/enable', [VoucherController::class, 'enable'])->name('vouchers.enable');
            Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');
        });

        // ── Log User Hotspot (radpostauth) ────────────────────────────────
        Route::get('/hotspot-logs', [HotspotLogController::class, 'index'])->name('hotspot-logs.index');
        Route::get('/hotspot-logs/poll', [HotspotLogController::class, 'poll'])->name('hotspot-logs.poll');

        // ── Log Aktivitas (admin only) ────────────────────────────────────
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index')
            ->middleware('role:admin');

        // ── Profil Admin (semua role, akses profil sendiri) ───────────────
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile/info', [AdminProfileController::class, 'updateInfo'])->name('profile.update-info');
        Route::patch('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.update-password');
    });

require __DIR__.'/auth.php';
