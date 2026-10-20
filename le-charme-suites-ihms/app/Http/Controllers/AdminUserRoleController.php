<?php

namespace App\Http\Controllers;

use App\Models\RolePermission;
use App\Models\User;
use App\Rules\WorkEmail;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'role' => ['required', 'in:admin,receptionist,cashier,housekeeping'],
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
            'role' => ['required', 'in:admin,receptionist,cashier,housekeeping'],
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

    public function savePermissions(Request $request): RedirectResponse
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

        return to_route('admin.permissions.index', ['role' => $validated['role']])
            ->with('status', 'Permission changes saved for this role.');
    }

    public function resetPermissions(Request $request): RedirectResponse
    {
        $role = $request->validate([
            'role' => ['required', Rule::in(config('rbac.roles'))],
        ])['role'];

        RolePermission::where('role', $role)->delete();

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
