<?php

namespace App\Http\Controllers;

use App\Models\RolePermission;
use App\Models\User;
use App\Rules\WorkEmail;
use App\Services\AuditService;
use App\Services\CredentialRevocationService;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserRoleController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', $this->viewData());
    }

    public function createAccount(): View
    {
        return view('admin.accounts.create', [
            'roleOptions' => collect(config('hotel.staff_roles'))->except('admin')->all(),
            'totalUsers' => User::count(),
        ]);
    }

    public function storeAccount(Request $request, AuditService $audit): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new WorkEmail, Rule::unique('users', 'email')],
            'role' => ['required', 'in:receptionist,cashier,housekeeping'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        // Accounts created by an administrator are approved immediately.
        $user = User::create($validated + ['approved_at' => now()]);
        $audit->record($request, 'USER_ACCOUNT_CREATED', $user, [], $user->only(['name', 'email', 'role', 'approved_at']));

        return to_route('admin.accounts.create')
            ->with('status', "Staff account created for {$user->name} ({$user->email}).");
    }

    public function roles(): View
    {
        return view('admin.users.roles', $this->viewData());
    }

    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new WorkEmail, Rule::unique('users', 'email')],
            'role' => ['required', 'in:admin,receptionist,cashier,housekeeping'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($validated + ['approved_at' => now()]);
        $audit->record($request, 'USER_ACCOUNT_CREATED', $user, [], $user->only(['name', 'email', 'role', 'approved_at']));

        return to_route('admin.users.index')->with('status', 'User account provisioned.');
    }

    public function update(
        Request $request,
        User $user,
        CredentialRevocationService $revocation,
        AuditService $audit,
    ): RedirectResponse {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', new WorkEmail, Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:admin,receptionist,cashier,housekeeping'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->role === 'admin' && $validated['role'] !== 'admin' && User::where('role', 'admin')->whereKeyNot($user->id)->doesntExist()) {
            return back()->withErrors(['role' => 'At least one system admin account must remain active.']);
        }

        $passwordChanged = filled($validated['password'] ?? null);

        if (! $passwordChanged) {
            unset($validated['password']);
        }

        $before = $user->only(['name', 'email', 'role', 'approved_at']);
        $user->update($validated);

        if ($passwordChanged) {
            $revocation->revokeFor(
                $user,
                $request->user()->is($user) ? $request->session()->getId() : null,
            );
        }

        $audit->record($request, 'USER_ACCOUNT_UPDATED', $user, $before, [
            ...$user->only(['name', 'email', 'role', 'approved_at']),
            'password_changed' => $passwordChanged,
        ]);

        return to_route('admin.users.index')->with('status', 'User account updated.');
    }

    public function approve(Request $request, User $user, AuditService $audit): RedirectResponse
    {
        if (! $user->isApproved()) {
            $user->update(['approved_at' => now()]);
            $audit->record($request, 'USER_ACCOUNT_APPROVED', $user, ['approved_at' => null], ['approved_at' => $user->approved_at]);
        }

        return to_route('admin.users.index')->with('status', "{$user->name} can now sign in.");
    }

    public function destroy(Request $request, User $user, AuditService $audit): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete the account you are currently using.']);
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->whereKeyNot($user->id)->doesntExist()) {
            return back()->withErrors(['user' => 'At least one system admin account must remain active.']);
        }

        $before = $user->only(['name', 'email', 'role', 'approved_at']);
        $audit->record($request, 'USER_ACCOUNT_DELETED', $user, $before);
        $user->delete();

        return to_route('admin.users.index')->with('status', 'User account deleted.');
    }

    public function permissions(Request $request, PermissionService $permissions): View
    {
        $roles = config('rbac.roles');
        $selectedRole = $request->string('role')->toString();
        $selectedRole = in_array($selectedRole, $roles, true) ? $selectedRole : 'admin';

        return view('admin.users.permissions', [
            ...$this->viewData(),
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'permissionState' => $permissions->matrix(),
            'capabilities' => config('rbac.capabilities'),
            'actions' => array_combine(
                config('rbac.actions'),
                array_map(fn (string $action) => ucfirst($action), config('rbac.actions')),
            ),
        ]);
    }

    public function savePermissions(Request $request, AuditService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(config('rbac.roles'))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array:'.implode(',', config('rbac.actions'))],
            'permissions.*.*' => ['boolean'],
        ]);

        $capabilities = array_keys(config('rbac.capabilities'));
        $actions = config('rbac.actions');

        DB::transaction(function () use ($request, $validated, $capabilities, $actions): void {
            foreach ($capabilities as $capability) {
                foreach ($actions as $action) {
                    RolePermission::updateOrCreate(
                        ['role' => $validated['role'], 'permission' => "{$capability}.{$action}"],
                        [
                            'granted' => (bool) data_get($validated, "permissions.{$capability}.{$action}", false),
                            'updated_by' => $request->user()->id,
                        ],
                    );
                }
            }
        });

        $audit->record($request, 'ROLE_PERMISSIONS_UPDATED', null, [], [
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return to_route('admin.permissions.index', ['role' => $validated['role']])
            ->with('status', 'Permission changes saved for this role.');
    }

    public function resetPermissions(Request $request, AuditService $audit): RedirectResponse
    {
        $role = $request->validate([
            'role' => ['required', Rule::in(config('rbac.roles'))],
        ])['role'];

        RolePermission::where('role', $role)->delete();
        $audit->record($request, 'ROLE_PERMISSIONS_RESET', null, ['role' => $role], ['defaults_restored' => true]);

        return to_route('admin.permissions.index', ['role' => $role])
            ->with('status', 'Permission changes reset to the standard defaults.');
    }

    private function viewData(): array
    {
        return [
            'users' => User::latest()->paginate(10),
            'totalUsers' => User::count(),
            'pendingUsers' => User::whereNull('approved_at')->oldest()->get(),
            'roleCounts' => User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
        ];
    }
}
