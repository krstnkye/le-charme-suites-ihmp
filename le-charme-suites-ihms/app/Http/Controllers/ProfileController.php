<?php

namespace App\Http\Controllers;

use App\Rules\WorkEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        if ($request->user()?->role === 'owner') {
            return redirect()->route('owner.page', ['page' => 'profile']);
        }

        $view = match ($request->user()?->role) {
            'housekeeping' => 'profile.housekeeping-edit',
            'cashier' => 'profile.billing-edit',
            default => 'profile.edit',
        };

        return view($view, [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new WorkEmail, Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('status', 'Your profile was updated successfully.');
    }
}
