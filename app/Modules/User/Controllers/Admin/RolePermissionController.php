<?php

namespace App\Modules\User\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        $stats = [
            'roles' => $roles->count(),
            'permissions' => $permissions->count(),
            'assigned_links' => $roles->sum(fn ($role) => $role->permissions->count()),
        ];

        return view('admin.roles-permissions.index', compact('roles', 'permissions', 'stats'));
    }
}