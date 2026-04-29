<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*', headers:
            \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
            \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'ensure.admin' => \App\Http\Middleware\EnsureAdmin::class,
            'role'         => \App\Http\Middleware\EnsureRole::class,
            'session.timeout' => \App\Http\Middleware\SessionTimeout::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\SessionTimeout::class);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->command('vouchers:sync')->everyMinute()->withoutOverlapping();
        $schedule->command('wa:reminders')->hourly()->withoutOverlapping();
        // Log retention
        $schedule->command('logs:archive')->monthlyOn(1, '02:00')->withoutOverlapping();
        $schedule->command('logs:cleanup')->dailyAt('04:00')->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
