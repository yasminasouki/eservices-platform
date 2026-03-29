<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function __construct(private Google2FA $google2fa) {}

    // ── Registration ──────────────────────────────────────────────────────────

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name'               => $request->name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'password'           => $request->password,
            'role'               => 'citizen',
            'id_document_status' => 'pending',
            'is_active'          => true,
        ]);

        $secret = $this->google2fa->generateSecretKey();
        $user->forceFill([
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
        ])->save();

        $user->sendEmailVerificationNotification();
        Auth::login($user);

        return redirect()->route('2fa.setup')
            ->with('info', 'Please set up two-factor authentication to secure your account.');
    }

    // ── Login ─────────────────────────────────────────────────────────────────

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showAdminLoginForm()
    {
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        return $this->authenticateAndLogin($request);
    }

    public function adminLogin(Request $request)
    {
        return $this->authenticateAndLogin($request, 'admin');
    }

    private function authenticateAndLogin(Request $request, ?string $requiredRole = null)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->withInput($request->only('email'));
        }

        // Citizen/office portal: administrators must use /admin/login only
        if ($requiredRole === null && $user->role === 'admin') {
            return back()
                ->withErrors([
                    'email' => 'Administrator accounts must sign in at the admin portal.',
                ])
                ->withInput($request->only('email'));
        }

        // Citizen portal: municipality users must use /municipality/login only
        if ($requiredRole === null && $user->role === 'office_user') {
            return back()
                ->withErrors([
                    'email' => 'Municipality staff accounts must sign in at the municipality portal.',
                ])
                ->withInput($request->only('email'));
        }

        if ($requiredRole !== null && $user->role !== $requiredRole) {
            return back()
                ->withErrors(['email' => 'This portal is restricted to administrators only.'])
                ->withInput($request->only('email'));
        }

        if (!$user->is_active) {
            return back()
                ->withErrors(['email' => 'Your account has been deactivated. Please contact support.'])
                ->withInput($request->only('email'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        // If 2FA is confirmed, require verification this session
        if ($user->two_factor_confirmed_at) {
            return redirect()->route('2fa.verify');
        }

        // Municipality users and admins: provision TOTP on first login (citizens get this at registration).
        if (
            !$user->social_provider
            && ($user->isOfficeUser() || $user->role === 'admin')
            && !$user->two_factor_secret
        ) {
            $secret = $this->google2fa->generateSecretKey();
            $user->forceFill([
                'two_factor_secret'         => encrypt($secret),
                'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
            ])->save();

            return redirect()->route('2fa.setup')
                ->with('info', 'You must set up two-factor authentication before accessing the system.');
        }

        return $this->redirectToDashboard($user);
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        $role = $request->user()?->role;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $targetRoute = match ($role) {
            'admin' => 'admin.login',
            'office_user' => 'municipality.login',
            default => 'login',
        };

        return redirect()
            ->route($targetRoute)
            ->with('success', 'You have been logged out successfully.');
    }

    // ── 2FA Setup ─────────────────────────────────────────────────────────────

    public function show2faSetup()
    {
        $user = Auth::user();

        if (!$user->two_factor_secret) {
            $secret = $this->google2fa->generateSecretKey();
            $user->forceFill([
                'two_factor_secret'         => encrypt($secret),
                'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
            ])->save();
            $user->refresh();
        }

        $secret        = decrypt($user->two_factor_secret);
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $qrCode = $this->generateQrCode($qrCodeUrl);

        return view('auth.2fa-setup', compact('secret', 'qrCode', 'recoveryCodes'));
    }

    public function confirm2faSetup(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $user   = Auth::user();
        $secret = decrypt($user->two_factor_secret);

        if (!$this->google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'The code you entered is invalid. Please try again.']);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        session(['2fa_verified' => true]);

        // New citizen accounts must verify their email before reaching the dashboard.
        if ($user->role === 'citizen' && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('info', 'Please verify your email address to complete your account setup.');
        }

        return redirect()->route($this->getDashboardRoute($user))
            ->with('success', 'Two-factor authentication has been enabled successfully.');
    }

    // ── 2FA Verify (on login) ─────────────────────────────────────────────────

    public function show2faVerify()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.2fa-verify');
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user   = Auth::user();
        $secret = decrypt($user->two_factor_secret);
        $code   = preg_replace('/\s+/', '', $request->code);

        $valid = strlen($code) === 6
            ? $this->google2fa->verifyKey($secret, $code, 1)
            : $this->verifyRecoveryCode($user, $code);

        if (!$valid) {
            return back()->withErrors(['code' => 'The code you entered is invalid.']);
        }

        session(['2fa_verified' => true]);

        // If the user was redirected to login mid-flow (e.g. clicking the email
        // verification link while logged out), honor that intended URL now.
        if (session()->has('url.intended')) {
            return redirect()->intended();
        }

        return $this->redirectToDashboard($user);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function redirectToDashboard(User $user)
    {
        if ($user->role === 'citizen' && is_null($user->id_document)) {
            return redirect()->route('citizen.id.verify');
        }

        return redirect()->route($this->getDashboardRoute($user));
    }

    private function getDashboardRoute(User $user): string
    {
        return match ($user->role) {
            'admin'       => 'admin.dashboard',
            'office_user' => 'office.dashboard',
            default       => 'citizen.dashboard',
        };
    }

    private function generateRecoveryCodes(): array
    {
        return array_map(
            fn() => strtoupper(Str::random(5)) . '-' . strtoupper(Str::random(5)),
            range(1, 8)
        );
    }

    private function verifyRecoveryCode(User $user, string $code): bool
    {
        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        if (!$codes || !in_array($code, $codes)) {
            return false;
        }

        $remaining = array_values(array_filter($codes, fn($c) => $c !== $code));
        $user->forceFill(['two_factor_recovery_codes' => encrypt(json_encode($remaining))])->save();

        return true;
    }

    private function generateQrCode(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($url));
    }
}
