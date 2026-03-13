<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        // Get search and filter parameters
        $search = $request->get('search');
        $role = $request->get('role');
        $status = $request->get('status');

        // Build query
        $query = User::with(['roles', 'orders'])
            ->withCount('orders');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Apply status filter
        if ($status === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($status === 'unverified') {
            $query->whereNull('email_verified_at');
        } elseif ($status === 'online') {
            $query->where('last_login_at', '>', now()->subMinutes(15));
        }

        // Get users with pagination
        $users = $query->latest()
            ->paginate(20)
            ->withQueryString();

        // Transform users data with comprehensive null safety
        $usersData = $users->through(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name ?? 'Unknown User',
                'email' => $user->email ?? 'no-email@example.com',
                'role' => $user->roles->first()?->name ?? 'client',
                'role_display' => $user->roles->first() ? ucfirst($user->roles->first()->name) : 'Client',
                'status' => $user->last_login_at && $user->last_login_at->gt(now()->subMinutes(15)) ? 'online' : 'offline',
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at' => $user->created_at ? $user->created_at->toISOString() : now()->toISOString(),
                'last_login' => $user->last_login_at?->toISOString(),
                'orders_count' => $user->orders_count ?? 0,
                'total_spent' => $user->orders ? $user->orders->whereNotIn('status', ['cancelled', 'refunded'])->sum('total_amount') : 0,
                'profile_picture_url' => $user->profile_picture_url ?? null,
                'company_name' => $user->company_name ?? null,
                'phone' => $user->phone ?? null,
                'initials' => $this->getInitials($user->name ?? 'Unknown User'),
                'is_verified' => !is_null($user->email_verified_at),
                'created_at_human' => $user->created_at ? $user->created_at->format('M j, Y') : 'Unknown',
                'last_login_human' => $user->last_login_at ? $user->last_login_at->format('M j, Y') : 'Never'
            ];
        });

        // Calculate statistics
        $totalUsers = User::count();
        $onlineUsers = User::where('last_login_at', '>', now()->subMinutes(15))->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $totalRevenue = round(Payment::where('status', 'succeeded')->sum('amount'), 2);

        $stats = [
            'total_users' => $totalUsers,
            'online_users' => $onlineUsers,
            'verified_users' => $verifiedUsers,
            'new_users_today' => $newUsersToday,
            'total_revenue' => $totalRevenue
        ];

        // Get available roles for filters
        $availableRoles = Role::all()->map(function ($role) {
            $displayNames = [
                'administrator' => 'Administrator',
                'client' => 'Client', 
                'moderator' => 'Moderator',
                'super-admin' => 'Super Admin',
                'staff' => 'Staff'
            ];
            
            return [
                'value' => $role->name,
                'label' => $displayNames[$role->name] ?? ucfirst($role->name)
            ];
        });

        return Inertia::render('Admin/Users/Index', [
            'users' => $usersData,
            'stats' => $stats,
            'filters' => [
                'role' => $role,
                'status' => $status
            ],
            'available_roles' => $availableRoles,
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all()->map(function ($role) {
            $displayNames = [
                'administrator' => 'Administrator',
                'client' => 'Client', 
                'moderator' => 'Moderator',
                'super-admin' => 'Super Admin',
                'staff' => 'Staff'
            ];
            
            return [
                'value' => $role->name,
                'label' => $displayNames[$role->name] ?? ucfirst($role->name)
            ];
        });

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name'
        ]);

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now() // Auto-verify admin created users
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with(['roles', 'orders'])
            ->withCount(['orders'])
            ->findOrFail($id);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'company_name' => $user->company_name,
            'role' => $user->roles->first()?->name ?? 'client',
            'role_display' => $user->roles->first() ? ucfirst($user->roles->first()->name) : 'Client',
            'status' => $user->last_login_at && $user->last_login_at->gt(now()->subMinutes(15)) ? 'online' : 'offline',
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'created_at' => $user->created_at->toISOString(),
            'last_login' => $user->last_login_at?->toISOString(),
            'orders_count' => $user->orders_count ?? 0,
            'total_spent' => $user->orders->sum('total_amount') ?? 0,
            'profile_picture_url' => $user->profile_picture_url,
            'address_line_1' => $user->address_line_1,
            'address_line_2' => $user->address_line_2,
            'city' => $user->city,
            'state' => $user->state,
            'zip_code' => $user->zip_code,
            'country' => $user->country,
            'citizenship' => $user->citizenship,
            'preferred_language' => $user->preferred_language,
            'timezone' => $user->timezone,
            'email_notifications' => $user->email_notifications,
            'sms_notifications' => $user->sms_notifications,
            'kyc_verified' => $user->kyc_verified,
            'kyc_verified_at' => $user->kyc_verified_at?->toISOString(),
        ];

        return Inertia::render('Admin/Users/Show', [
            'user' => $userData
        ]);
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        
        $roles = Role::all()->map(function ($role) {
            $displayNames = [
                'administrator' => 'Administrator',
                'client' => 'Client', 
                'moderator' => 'Moderator',
                'super-admin' => 'Super Admin',
                'staff' => 'Staff'
            ];
            
            return [
                'value' => $role->name,
                'label' => $displayNames[$role->name] ?? ucfirst($role->name)
            ];
        });

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'company_name' => $user->company_name,
            'role' => $user->roles->first()?->name ?? 'client',
            'email_verified_at' => $user->email_verified_at?->toISOString(),
            'created_at' => $user->created_at->toISOString(),
        ];

        return Inertia::render('Admin/Users/Edit', [
            'user' => $userData,
            'roles' => $roles
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'role' => 'required|string|exists:roles,name',
            'password' => 'nullable|string|min:8|confirmed',
            'email_verified' => 'boolean'
        ]);

        // Update user details
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'company_name' => $validated['company_name'],
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update email verification status
        if (isset($validated['email_verified'])) {
            $user->email_verified_at = $validated['email_verified'] ? now() : null;
            $user->save();
        }

        // Update role
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deletion of the currently authenticated user
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }
        
        // Prevent deletion of super admin users (optional safety check)
        if ($user->hasRole('super-admin')) {
            return back()->with('error', 'Super Admin users cannot be deleted!');
        }
        
        // Check if user has orders - you might want to prevent deletion or handle cascade
        if ($user->orders()->count() > 0) {
            return back()->with('error', 'Cannot delete user with existing orders!');
        }
        
        $user->delete();
        
        return back()->with('success', 'User deleted successfully!');
    }

    /**
     * Bulk actions for users
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,activate,deactivate,verify_email,assign_role',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required_if:action,assign_role|exists:roles,name'
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->get();
        $currentUserId = auth()->id();

        switch ($validated['action']) {
            case 'delete':
                // Prevent deletion of current user
                $users = $users->filter(fn($user) => $user->id !== $currentUserId);
                foreach ($users as $user) {
                    if (!$user->hasRole('super-admin') && $user->orders()->count() === 0) {
                        $user->delete();
                    }
                }
                return back()->with('success', 'Selected users deleted successfully!');

            case 'verify_email':
                $users->each(fn($user) => $user->update(['email_verified_at' => now()]));
                return back()->with('success', 'Selected users verified successfully!');

            case 'assign_role':
                $users->each(fn($user) => $user->syncRoles([$validated['role']]));
                return back()->with('success', 'Role assigned to selected users successfully!');

            default:
                return back()->with('error', 'Invalid action!');
        }
    }

    /**
     * Get user initials for avatar display
     */
    private function getInitials($name)
    {
        if (!$name) {
            return 'U';
        }
        
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($name, 0, 2));
    }
}