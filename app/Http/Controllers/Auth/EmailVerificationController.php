<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationCodeMail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class EmailVerificationController extends Controller
{
    // Show "please verify your email" notice
    public function notice()
    {
        $user = request()->user();

        if ($user->hasVerifiedEmail()) {
            if ($user->role === 'citizen' && is_null($user->id_document)) {
                return redirect()->route('citizen.id.verify');
            }
            return redirect()->intended(route('citizen.dashboard'));
        }

        return view('auth.verify-email');
    }

    // Handle the verification link clicked from email (keep working for PC users)
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($this->dashboard())->with('success', 'Email already verified.');
        }

        if ($request->fulfill()) {
            event(new Verified($request->user()));
        }

        $user = $request->user();

        if ($user->role === 'citizen' && is_null($user->id_document)) {
            return redirect()->route('citizen.id.verify')
                ->with('success', 'Email verified! Please complete your identity verification.');
        }

        return redirect()->route($this->dashboard())
            ->with('success', 'Your email has been verified successfully!');
    }

    // Verify using 6-digit code entered on the page
    public function verifyCode(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $user = $request->user();
        $cacheKey = 'email_verify_code_' . $user->id;
        $stored = Cache::get($cacheKey);

        if (! $stored || $stored !== $request->code) {
            return back()->withErrors(['code' => 'The code is invalid or has expired. Please request a new one.']);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        Cache::forget($cacheKey);

        if ($user->role === 'citizen' && is_null($user->id_document)) {
            return redirect()->route('citizen.id.verify')
                ->with('success', 'Email verified! Please complete your identity verification.');
        }

        return redirect()->route($this->dashboard())
            ->with('success', 'Your email has been verified successfully!');
    }

    // Send/resend verification code
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route($this->dashboard());
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('email_verify_code_' . $user->id, $code, now()->addHours(24));

        Mail::to($user->email)->send(new EmailVerificationCodeMail($user->name, $code));

        return back()->with('success', 'A 6-digit verification code has been sent to your email.');
    }

    private function dashboard(): string
    {
        return match (auth()->user()->role) {
            'admin'       => 'admin.dashboard',
            'office_user' => 'office.dashboard',
            default       => 'citizen.dashboard',
        };
    }
}
