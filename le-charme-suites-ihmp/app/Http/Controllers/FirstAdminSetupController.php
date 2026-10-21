<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\WorkEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class FirstAdminSetupController extends Controller
{
    /**
     * One-time setup for a fresh installation: available only while no administrator exists.
     */
    public function create(): View
    {
        abort_if(self::hasAdministrator(), 404);

        return view('auth.setup');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(self::hasAdministrator(), 404);

        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new WorkEmail, Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        DB::transaction(function () use ($data): void {
            abort_if(User::where('role', 'admin')->lockForUpdate()->exists(), 404);

            User::create($data + ['role' => 'admin', 'approved_at' => now()]);
        });

        return to_route('admin.login')
            ->with('status', 'Administrator account created. Sign in to continue.')
            ->withInput(['email' => $data['email']]);
    }

    public static function hasAdministrator(): bool
    {
        return User::where('role', 'admin')->exists();
    }
}
