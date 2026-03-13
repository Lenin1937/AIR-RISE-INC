<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Clear cache first
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions - matching the UI exactly
        $permissions = [
            'view_dashboard',
            'manage_users',
            'manage_orders',
            'manage_payments',
            'manage_documents',
            'manage_messages',
            'view_reports',
            'manage_roles',
            'system_settings'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles - matching your UI
        $administrator = Role::firstOrCreate(['name' => 'administrator']);
        $client = Role::firstOrCreate(['name' => 'client']); 
        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        // Assign permissions to roles
        $administrator->syncPermissions([
            'view_dashboard',
            'manage_users',
            'manage_orders', 
            'manage_payments',
            'manage_documents',
            'manage_messages',
            'view_reports',
            'manage_roles',
            'system_settings'
        ]);

        $client->syncPermissions([
            'view_dashboard'
        ]);

        $moderator->syncPermissions([
            'view_dashboard',
            'manage_orders',
            'manage_documents', 
            'manage_messages',
            'view_reports'
        ]);

        $superAdmin->syncPermissions(Permission::all());

        $staff->syncPermissions([
            'view_dashboard',
            'manage_orders',
            'manage_documents',
            'manage_messages'
        ]);

        // Create Super Admin User
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@icorp.pro'],
            [
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdminUser->assignRole('super-admin');

        // Create Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@icorp.pro'],
            [
                'name' => 'Administrator',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole('administrator');

        // Create Moderator User
        $moderatorUser = User::firstOrCreate(
            ['email' => 'moderator@icorp.pro'],
            [
                'name' => 'Content Moderator',
                'first_name' => 'Content',
                'last_name' => 'Moderator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $moderatorUser->assignRole('moderator');

        // Create Sample Client Users
        $clientUser1 = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'John Smith',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'phone' => '+1-555-0123',
                'company_name' => 'Tech Innovations LLC',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $clientUser1->assignRole('client');

        $clientUser2 = User::firstOrCreate(
            ['email' => 'sarah@innovatecorp.com'],
            [
                'name' => 'Sarah Johnson',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'phone' => '+1-555-0124',
                'company_name' => 'Innovate Corp',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $clientUser2->assignRole('client');

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
