<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\WorkEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserRoleController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', $this->viewData());
    }

    public function roles(): View
    {
        return view('admin.users.roles', $this->viewData());
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new WorkEmail, Rule::unique('users', 'email')],
            'role' => ['required', 'in:admin,owner,receptionist,cashier,housekeeping'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create($validated);

        return to_route('admin.users.index')->with('status', 'User account provisioned.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new WorkEmail, Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,owner,receptionist,cashier,housekeeping'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->role === 'admin' && $validated['role'] !== 'admin' && User::where('role', 'admin')->whereKeyNot($user->id)->doesntExist()) {
            return back()->withErrors(['role' => 'At least one system admin account must remain active.']);
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated);

        return to_route('admin.users.index')->with('status', 'User account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete the account you are currently using.']);
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->whereKeyNot($user->id)->doesntExist()) {
            return back()->withErrors(['user' => 'At least one system admin account must remain active.']);
        }

        $user->delete();

        return to_route('admin.users.index')->with('status', 'User account deleted.');
    }

    public function permissions(Request $request): View
    {
        $roles = ['admin', 'owner', 'receptionist', 'cashier', 'housekeeping'];
        $selectedRole = $request->string('role')->toString();
        $selectedRole = in_array($selectedRole, $roles, true) ? $selectedRole : 'admin';

        return view('admin.users.permissions', [
            ...$this->viewData(),
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'permissionState' => session('permission_matrix', []),
        ]);
    }

    public function savePermissions(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,owner,receptionist,cashier,housekeeping'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['boolean'],
        ]);

        $matrix = session('permission_matrix', []);
        $matrix[$validated['role']] = $validated['permissions'] ?? [];
        session(['permission_matrix' => $matrix]);

        return to_route('admin.permissions.index', ['role' => $validated['role']])
            ->with('status', 'Permission changes saved for this role.');
    }

    public function resetPermissions(Request $request): RedirectResponse
    {
        $role = $request->validate([
            'role' => ['required', 'in:admin,owner,receptionist,cashier,housekeeping'],
        ])['role'];

        $matrix = session('permission_matrix', []);
        unset($matrix[$role]);
        session(['permission_matrix' => $matrix]);

        return to_route('admin.permissions.index', ['role' => $role])
            ->with('status', 'Permission changes reset to the standard defaults.');
    }

    private function viewData(): array
    {
        return [
            'users' => User::latest()->paginate(10),
            'totalUsers' => User::count(),
            'roleCounts' => User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
        ];
    }
}
