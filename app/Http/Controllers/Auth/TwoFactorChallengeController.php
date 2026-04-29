<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function __construct(private readonly TwoFactorAuthService $twoFactor)
    {
    }

    public function show(Request $request): View|RedirectResponse
    {
        if (! $this->pendingUser($request)) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget('login.2fa');

        return redirect()->route('login');
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'code'          => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $code         = trim((string) $request->input('code'));
        $recoveryCode = trim((string) $request->input('recovery_code'));

        if ($code === '' && $recoveryCode === '') {
            throw ValidationException::withMessages([
                'code' => 'Masukkan kode dari authenticator atau recovery code.',
            ]);
        }

        $throttleKey = $this->throttleKey($request, $user);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'code' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if ($code !== '') {
            if (! $this->twoFactor->verify($user->two_factor_secret, $code)) {
                RateLimiter::hit($throttleKey);
                throw ValidationException::withMessages([
                    'code' => 'Kode otentikasi tidak valid.',
                ]);
            }
        } else {
            $hashed   = $user->recoveryCodes();
            $newCodes = $this->twoFactor->consumeRecoveryCode($hashed, $recoveryCode);

            if ($newCodes === null) {
                RateLimiter::hit($throttleKey);
                throw ValidationException::withMessages([
                    'recovery_code' => 'Recovery code tidak valid.',
                ]);
            }

            $user->two_factor_recovery_codes = json_encode($newCodes);
            $user->save();
        }

        RateLimiter::clear($throttleKey);

        $remember = (bool) ($request->session()->get('login.2fa.remember', false));
        $request->session()->forget('login.2fa');

        Auth::login($user, $remember);
        $request->session()->regenerate();
        session(['last_activity_time' => time()]);
        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function pendingUser(Request $request): ?User
    {
        $payload = $request->session()->get('login.2fa');

        if (! is_array($payload) || empty($payload['user_id']) || empty($payload['expires'])) {
            return null;
        }

        if ($payload['expires'] < time()) {
            $request->session()->forget('login.2fa');
            return null;
        }

        return User::find($payload['user_id']);
    }

    private function throttleKey(Request $request, User $user): string
    {
        return Str::lower('2fa|' . $user->id . '|' . $request->ip());
    }
}
