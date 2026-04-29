<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HotspotLogController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\RadiusUserController;
use App\Http\Controllers\Admin\RouterController;
use App\Http\Controllers\Admin\TwoFactorSettingsController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Legacy 301 redirects: /admin/* → /* (radius-users → user-hotspot)
Route::permanentRedirect('/admin', '/dashboard');
Route::get('/admin/{any}', function (string $any) {
    $any = preg_replace('#^radius-users(?=$|/)#', 'user-hotspot', $any);
    return redirect('/'.$any, 301);
})->where('any', '.*');

Route::middleware(['auth', 'ensure.admin'])->group(function () {

    // ── Dashboard (semua role) ────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // ── User Hotspot ─────────────────────────────────────────────────
    // Operator: lihat, tambah, edit, toggle (semua kecuali hapus)
    Route::resource('user-hotspot', RadiusUserController::class)
        ->parameters(['user-hotspot' => 'radius_user'])
        ->except(['show', 'destroy']);
    Route::patch('user-hotspot/{radius_user}/toggle', [RadiusUserController::class, 'toggle'])
        ->name('user-hotspot.toggle');

    // Admin only: hapus user
    Route::delete('user-hotspot/{radius_user}', [RadiusUserController::class, 'destroy'])
        ->name('user-hotspot.destroy')
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

    // ── Router Management ────────────────────────────────────────────
    Route::get('/routers', [RouterController::class, 'index'])->name('routers.index');
    Route::get('/routers/{router}', [RouterController::class, 'show'])->name('routers.show');
    Route::get('/routers/{router}/stats', [RouterController::class, 'stats'])->name('routers.stats');
    Route::get('/routers/{router}/hotspot-users', [RouterController::class, 'hotspotUsers'])->name('routers.hotspot-users');
    Route::get('/routers/{router}/traffic', [RouterController::class, 'traffic'])->name('routers.traffic');

    // Admin only: aksi destruktif
    Route::middleware('role:admin')->group(function () {
        Route::post('/routers/{router}/disconnect', [RouterController::class, 'disconnect'])->name('routers.disconnect');
        Route::post('/routers/{router}/reboot', [RouterController::class, 'reboot'])->name('routers.reboot');
        Route::get('/routers/{router}/backup', [RouterController::class, 'backup'])->name('routers.backup');
    });

    // ── WhatsApp Gateway (admin only) ─────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
        Route::post('/whatsapp/send', [WhatsAppController::class, 'send'])->name('whatsapp.send');
        Route::post('/whatsapp/contacts', [WhatsAppController::class, 'storeContact'])->name('whatsapp.contacts.store');
        Route::patch('/whatsapp/contacts/{contact}', [WhatsAppController::class, 'updateContact'])->name('whatsapp.contacts.update');
        Route::delete('/whatsapp/contacts/{contact}', [WhatsAppController::class, 'destroyContact'])->name('whatsapp.contacts.destroy');
    });

    // ── Profil Admin (semua role, akses profil sendiri) ───────────────
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/info', [AdminProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::patch('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.update-password');

    // ── 2FA Settings (semua role, akun sendiri) ───────────────────────
    Route::get('/profile/two-factor', [TwoFactorSettingsController::class, 'setup'])->name('two-factor.setup');
    Route::post('/profile/two-factor', [TwoFactorSettingsController::class, 'confirm'])->name('two-factor.confirm');
    Route::get('/profile/two-factor/recovery-codes', [TwoFactorSettingsController::class, 'recoveryCodes'])->name('two-factor.recovery-codes');
    Route::post('/profile/two-factor/recovery-codes', [TwoFactorSettingsController::class, 'regenerateRecoveryCodes'])->name('two-factor.regenerate-codes');
    Route::delete('/profile/two-factor', [TwoFactorSettingsController::class, 'disable'])->name('two-factor.disable');
});

require __DIR__.'/auth.php';
