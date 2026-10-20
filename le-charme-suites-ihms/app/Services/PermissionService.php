<?php

namespace App\Services;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PermissionService
{
    public function allows(User $user, string $permission): bool
    {
        [$capability, $action] = array_pad(explode('.', $permission, 2), 2, null);

        if (! $capability || ! $action || ! in_array($action, config('rbac.actions', []), true)) {
            return false;
        }

        $default = (bool) config("rbac.capabilities.{$capability}.defaults.{$user->role}.{$action}", false);

        if (! Schema::hasTable('role_permissions')) {
            return $default;
        }

        $override = RolePermission::query()
            ->where('role', $user->role)
            ->where('permission', $permission)
            ->value('granted');

        return $override === null ? $default : (bool) $override;
    }

    public function matrix(): array
    {
        $matrix = [];

        foreach (config('rbac.roles', []) as $role) {
            foreach (config('rbac.capabilities', []) as $key => $capability) {
                foreach (config('rbac.actions', []) as $action) {
                    $matrix[$role][$key][$action] = (bool) data_get($capability, "defaults.{$role}.{$action}", false);
                }
            }
        }

        if (! Schema::hasTable('role_permissions')) {
            return $matrix;
        }

        foreach (RolePermission::all() as $override) {
            [$capability, $action] = array_pad(explode('.', $override->permission, 2), 2, null);

            if (isset($matrix[$override->role][$capability][$action])) {
                $matrix[$override->role][$capability][$action] = $override->granted;
            }
        }

        return $matrix;
    }
}
