<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class MunicipalityAuthController extends Controller
{
    public function __construct(private Google2FA $google2fa) {}

    public function showLoginForm()
    {
        return view('auth.municipality-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->withInput($request->only('email'));
        }

        if ($user->role !== 'office_user') {
            return back()
                ->withErrors(['email' => 'This portal is restricted to municipality staff only.'])
                ->withInput($request->only('email'));
        }

        if (! $user->is_active) {
            return back()
                ->withErrors(['email' => 'Your account has been deactivated. Please contact your administrator.'])
                ->withInput($request->only('email'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        // 2FA already confirmed — require per-session verification
        if ($user->two_factor_confirmed_at) {
            return redirect()->route('2fa.verify');
        }

        // First login — provision TOTP secret if not yet generated
        if (! $user->two_factor_secret) {
            $user->forceFill([
                'two_factor_secret'         => encrypt($this->google2fa->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
            ])->save();
        }

        return redirect()->route('2fa.setup')
            ->with('info', 'You must set up two-factor authentication before accessing the system.');
    }

    private function generateRecoveryCodes(): array
    {
        return array_map(
            fn () => strtoupper(Str::random(5)) . '-' . strtoupper(Str::random(5)),
            range(1, 8)
        );
    }
}
