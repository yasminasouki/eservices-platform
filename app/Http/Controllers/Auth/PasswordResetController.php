<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class PasswordResetController extends Controller
{
    // ── Forgot Password ───────────────────────────────────────────────────────

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'A password reset link has been sent to your email address.')
            : back()->withErrors(['email' => __($status)])->withInput();
    }

    // ── Reset Password ────────────────────────────────────────────────────────

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()],
        ]);

        $loggedInUser = null;

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use (&$loggedInUser) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                $loggedInUser = $user;
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            Auth::login($loggedInUser);

            return match ($loggedInUser->role) {
                'admin'       => redirect()->route('admin.dashboard')->with('success', 'Password reset successfully.'),
                'office_user' => redirect()->route('office.dashboard')->with('success', 'Password reset successfully.'),
                default       => redirect()->route('citizen.dashboard')->with('success', 'Password reset successfully.'),
            };
        }

        return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }

    // ── Admin Forgot / Reset Password ─────────────────────────────────────────

    public function showAdminForgotForm()
    {
        return view('auth.admin-forgot-password');
    }

    public function sendAdminResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'A password reset link has been sent to your email address.')
            : back()->withErrors(['email' => __($status)])->withInput();
    }

    public function showAdminResetForm(Request $request, string $token)
    {
        return view('auth.admin-reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetAdminPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()],
        ]);

        $loggedInUser = null;

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use (&$loggedInUser) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                $loggedInUser = $user;
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            Auth::login($loggedInUser);

            return redirect()->route('admin.dashboard')->with('success', 'Password reset successfully.');
        }

        return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }
}
