<?php

namespace App\Providers;

use App\Services\ActivityLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event) {
            ActivityLogService::log(
                action: 'login',
                description: "Login berhasil: {$event->user->username}",
                subjectType: 'user',
                subjectId: (string) $event->user->id,
                userId: $event->user->id,
            );
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (! $event->user) return;
            ActivityLogService::log(
                action: 'logout',
                description: "Logout: {$event->user->username}",
                subjectType: 'user',
                subjectId: (string) $event->user->id,
                userId: $event->user->id,
            );
        });

        Event::listen(Failed::class, function (Failed $event) {
            $attempted = $event->credentials['username'] ?? $event->credentials['email'] ?? '(unknown)';
            ActivityLogService::log(
                action: 'login_failed',
                description: "Login gagal untuk: {$attempted}",
                subjectType: 'user',
                subjectId: $event->user?->id ? (string) $event->user->id : null,
                userId: $event->user?->id,
            );
        });
    }
}
