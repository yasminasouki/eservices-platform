<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    // Show "please verify your email" notice
    public function notice()
    {
        if (request()->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('citizen.dashboard'));
        }

        return view('auth.verify-email');
    }

    // Handle the verification link clicked from email
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($this->dashboard())->with('success', 'Email already verified.');
        }

        if ($request->fulfill()) {
            event(new Verified($request->user()));
        }

        return redirect()->route($this->dashboard())
            ->with('success', 'Your email has been verified successfully!');
    }

    // Resend the verification email
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route($this->dashboard());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'A new verification link has been sent to your email address.');
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
