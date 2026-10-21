<?php

namespace App\Http\Controllers;

use App\Models\AuthActivityLog;
use App\Models\User;
use App\Rules\WorkEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showAdminLogin(): View
    {
        return view('auth.admin-login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'email' => $request->string('email')->toString(),
            'token' => $token,
        ]);
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email', new WorkEmail],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        $status = PasswordBroker::sendResetLink($credentials);

        $this->recordActivity(
            $request,
            'PASSWORD_RESET_REQUESTED',
            $status === PasswordBroker::RESET_LINK_SENT,
            $user,
            'Password reset instructions requested.',
        );

        return back()->with(
            'status',
            'If that work email belongs to an account, password reset instructions have been sent.'
        );
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $credentials = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', new WorkEmail],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $status = PasswordBroker::reset(
            $credentials,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        $user = User::where('email', $credentials['email'])->first();
        $this->recordActivity(
            $request,
            'PASSWORD_RESET_COMPLETED',
            true,
            $user,
            'Staff account password was reset.',
        );

        return redirect()->route('login')->with('status', 'Your password has been reset. You can now sign in.');
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
                'role' => ['required', 'in:receptionist,cashier,housekeeping'],
            ], [
                'email.required' => 'Email address is required.',
            ]);
        } catch (ValidationException $exception) {
            $this->recordActivity($request, 'LOGIN_FAILED', false, null, 'Login rejected during validation.');

            throw $exception;
        }

        if (Auth::attemptWhen($credentials, fn (User $user): bool => $user->isApproved(), $request->boolean('remember'))) {
            $request->session()->regenerate();
            $this->recordActivity($request, 'LOGIN_SUCCESS', true, Auth::user(), 'Staff member signed in.');

            return redirect()->intended(route('dashboard'));
        }

        $account = Auth::validate([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]) ? User::where('email', $credentials['email'])->first() : null;

        if ($account && ! $account->isApproved()) {
            $this->recordActivity($request, 'LOGIN_FAILED', false, $account, 'Account is still awaiting administrator approval.');

            return back()
                ->withErrors(['email' => 'Your account is still awaiting approval from a system administrator.'])
                ->onlyInput('email', 'role');
        }

        $this->recordActivity($request, 'LOGIN_FAILED', false, null, 'Email and password did not match an account.');

        $message = $account && $account->role !== 'admin' && $account->role !== $credentials['role']
            ? 'The selected department does not match this account. Choose '.config("hotel.staff_roles.{$account->role}.label", ucfirst($account->role)).' and try again.'
            : 'The email or password does not match our records.';

        return back()
            ->withErrors(['email' => $message])
            ->onlyInput('email', 'role');
    }

    public function adminLogin(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        try {
            $credentials = $request->validate([
                'email' => ['required', 'email', new WorkEmail],
                'password' => ['required', 'string'],
            ], [
                'email.required' => 'Email address is required.',
            ]);
        } catch (ValidationException $exception) {
            $this->recordActivity($request, 'ADMIN_LOGIN_FAILED', false, null, 'Administrator login rejected during validation.');

            throw $exception;
        }

        // The role is forced server-side, so this form can only ever sign in administrators.
        if (Auth::attemptWhen($credentials + ['role' => 'admin'], fn (User $user): bool => $user->isApproved(), $request->boolean('remember'))) {
            $request->session()->regenerate();
            $this->recordActivity($request, 'ADMIN_LOGIN_SUCCESS', true, Auth::user(), 'Administrator signed in.');

            return redirect()->intended(route('dashboard'));
        }

        $this->recordActivity($request, 'ADMIN_LOGIN_FAILED', false, null, 'Administrator credentials did not match an account.');

        return back()
            ->withErrors(['email' => 'The provided credentials do not match an administrator account.'])
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
            'role' => ['required', 'in:receptionist,cashier,housekeeping'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $user = User::create($data);

        $this->recordActivity($request, 'ACCOUNT_REGISTERED', true, $user, 'New staff account registered and awaiting administrator approval.');

        return redirect()
            ->route('login')
            ->with('status', 'Account request submitted. A system administrator must approve it before you can sign in.')
            ->withInput([
                'email' => $user->email,
                'role' => $user->role,
            ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $wasAdmin = $request->user()?->role === 'admin';

        $this->recordActivity($request, 'LOGOUT', true, $request->user(), 'Staff member signed out.');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($wasAdmin ? 'admin.login' : 'login');
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
