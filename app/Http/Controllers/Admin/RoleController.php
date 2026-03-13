<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        // Get roles with permissions and users count from database
        $roles = Role::withCount(['permissions', 'users'])
            ->with('permissions')
            ->get()
            ->map(function ($role) {
                $displayNames = [
                    'administrator' => 'Administrator',
                    'client' => 'Client', 
                    'moderator' => 'Moderator',
                    'super-admin' => 'Super Admin',
                    'staff' => 'Staff'
                ];
                
                $descriptions = [
                    'administrator' => 'Full access to all administrative functions',
                    'client' => 'Access to client dashboard and services',
                    'moderator' => 'Limited administrative access',
                    'super-admin' => 'System administrator with full access',
                    'staff' => 'Basic administrative functions'
                ];

                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $displayNames[$role->name] ?? ucfirst($role->name),
                    'description' => $descriptions[$role->name] ?? 'System role',
                    'permissions_count' => $role->permissions_count,
                    'users_count' => $role->users_count,
                    'created_at' => $role->created_at->toISOString(),
                    'permissions' => $role->permissions->pluck('name')->toArray()
                ];
            });

        // Get all available permissions
        $permissions = Permission::all()->pluck('name')->toArray();
        
        // Calculate statistics
        $totalUsers = \App\Models\User::count();
        $adminRoles = Role::whereIn('name', ['administrator', 'super-admin', 'moderator', 'staff'])->count();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'stats' => [
                'total_roles' => $roles->count(),
                'total_permissions' => count($permissions),
                'total_users' => $totalUsers,
                'admin_roles' => $adminRoles
            ]
        ]);
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::all()->pluck('name')->toArray();

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles|regex:/^[a-z0-9_-]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        // Create the role
        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        // Assign permissions if provided
        if (isset($validated['permissions']) && !empty($validated['permissions'])) {
            $role->givePermissionTo($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully!');
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        $role = Role::with(['permissions', 'users'])
            ->withCount(['permissions', 'users'])
            ->findOrFail($id);

        $displayNames = [
            'administrator' => 'Administrator',
            'client' => 'Client', 
            'moderator' => 'Moderator',
            'super-admin' => 'Super Admin',
            'staff' => 'Staff'
        ];
        
        $descriptions = [
            'administrator' => 'Full access to all administrative functions',
            'client' => 'Access to client dashboard and services',
            'moderator' => 'Limited administrative access',
            'super-admin' => 'System administrator with full access',
            'staff' => 'Basic administrative functions'
        ];

        $roleData = [
            'id' => $role->id,
            'name' => $role->name,
            'display_name' => $displayNames[$role->name] ?? ucfirst($role->name),
            'description' => $descriptions[$role->name] ?? 'System role',
            'permissions' => $role->permissions->pluck('name')->toArray(),
            'users' => $role->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->toISOString()
                ];
            }),
            'permissions_count' => $role->permissions_count,
            'users_count' => $role->users_count,
            'created_at' => $role->created_at->toISOString()
        ];

        return Inertia::render('Admin/Roles/Show', [
            'role' => $roleData
        ]);
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        
        $displayNames = [
            'administrator' => 'Administrator',
            'client' => 'Client', 
            'moderator' => 'Moderator',
            'super-admin' => 'Super Admin',
            'staff' => 'Staff'
        ];
        
        $descriptions = [
            'administrator' => 'Full access to all administrative functions',
            'client' => 'Access to client dashboard and services',
            'moderator' => 'Limited administrative access',
            'super-admin' => 'System administrator with full access',
            'staff' => 'Basic administrative functions'
        ];

        $roleData = [
            'id' => $role->id,
            'name' => $role->name,
            'display_name' => $displayNames[$role->name] ?? ucfirst($role->name),
            'description' => $descriptions[$role->name] ?? 'System role',
            'permissions' => $role->permissions->pluck('name')->toArray()
        ];

        $allPermissions = Permission::all()->pluck('name')->toArray();

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $roleData,
            'permissions' => $allPermissions
        ]);
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        // Update permissions
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent deletion of essential system roles
        if (in_array($role->name, ['super-admin', 'administrator', 'client'])) {
            return back()->with('error', 'Cannot delete system roles!');
        }
        
        // Check if role has users assigned
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role that has users assigned to it!');
        }
        
        $role->delete();
        
        return back()->with('success', 'Role deleted successfully!');
    }
}