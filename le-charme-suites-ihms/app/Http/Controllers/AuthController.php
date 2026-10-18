<?php

namespace App\Http\Controllers;

use App\Models\AuthActivityLog;
use App\Models\User;
use App\Rules\WorkEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        try {
            $credentials = $request->validate([
                'email' => ['required', 'email', new WorkEmail],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $exception) {
            $this->recordActivity($request, 'LOGIN_FAILED', false, null, 'Login rejected during validation.');

            throw $exception;
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $this->recordActivity($request, 'LOGIN_SUCCESS', true, Auth::user(), 'Staff member signed in.');

            return redirect()->intended(route('dashboard'));
        }

        $this->recordActivity($request, 'LOGIN_FAILED', false, null, 'Email and password did not match an account.');

        return back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->onlyInput('email');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new WorkEmail, 'unique:users,email'],
            'role' => ['required', 'in:admin,receptionist,cashier,housekeeping,owner'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create($data);

        $this->recordActivity($request, 'ACCOUNT_REGISTERED', true, $user, 'New staff account registered.');

        Auth::login($user);
        $request->session()->regenerate();
        $this->recordActivity($request, 'LOGIN_SUCCESS', true, $user, 'Staff member signed in after registration.');

        return redirect()
            ->route('dashboard')
            ->with('status', 'Welcome to the IHMS workspace.')
            ->with('account_created', true)
            ->with('account_created_prompt_id', (string) Str::uuid());
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->recordActivity($request, 'LOGOUT', true, $request->user(), 'Staff member signed out.');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function recordActivity(
        Request $request,
        string $eventType,
        bool $successful,
        ?User $user,
        string $details,
    ): void {
        AuthActivityLog::create([
            'user_id' => $user?->id,
            'email' => $user?->email ?? Str::lower(trim((string) $request->input('email'))),
            'event_type' => $eventType,
            'successful' => $successful,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000),
            'details' => $details,
            'occurred_at' => now(),
        ]);
    }
}
