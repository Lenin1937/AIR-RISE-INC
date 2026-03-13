<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test users for development';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test users...');

        // Create admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@icorppro.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@icorppro.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'user_type' => 'business',
                'company_name' => 'iCorp Pro Admin',
                'phone' => '+1 (555) 123-4567',
            ]
        );

        $admin->assignRole('admin');
        $this->info('✓ Admin user created: admin@icorppro.com / password123');

        // Create client user
        $client = User::updateOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Test Client',
                'email' => 'client@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'user_type' => 'individual',
                'phone' => '+1 (555) 987-6543',
                'preferred_state' => 'CA',
            ]
        );

        $client->assignRole('client');
        $this->info('✓ Client user created: client@example.com / password123');

        // Create a super-admin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@icorppro.com'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@icorppro.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'user_type' => 'business',
                'company_name' => 'iCorp Pro',
                'phone' => '+1 (555) 000-0000',
            ]
        );

        $superAdmin->assignRole('super-admin');
        $this->info('✓ Super Admin user created: superadmin@icorppro.com / password123');

        $this->info("\nTest users created successfully!");
        $this->info("You can now log in with any of these accounts using password: password123");
    }
}
