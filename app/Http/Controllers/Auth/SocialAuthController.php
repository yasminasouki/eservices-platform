<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['google', 'facebook'];

    public function redirect(string $provider)
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Social login failed. Please try again.']);
        }

        $user = User::where('email', $socialUser->getEmail())
            ->orWhere(function ($q) use ($provider, $socialUser) {
                $q->where('social_provider', $provider)
                  ->where('social_provider_id', $socialUser->getId());
            })->first();

        if (!$user) {
            $user = User::create([
                'name'               => $socialUser->getName(),
                'email'              => $socialUser->getEmail(),
                'role'               => 'citizen',
                'social_provider'    => $provider,
                'social_provider_id' => $socialUser->getId(),
                'profile_photo'      => $socialUser->getAvatar(),
                'is_active'          => true,
                'email_verified_at'  => now(),
            ]);
        } else {
            if (!$user->social_provider) {
                $user->update([
                    'social_provider'    => $provider,
                    'social_provider_id' => $socialUser->getId(),
                ]);
            }
        }

        if (!$user->is_active) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
        }

        Auth::login($user);
        $user->update(['last_login_at' => now()]);

        return redirect()->route('citizen.dashboard');
    }

    private function validateProvider(string $provider): void
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS), 404);
    }
}
