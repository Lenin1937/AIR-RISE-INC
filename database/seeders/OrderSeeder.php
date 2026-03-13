<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, let's create a test user if one doesn't exist
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'client@icorppro.com'],
            [
                'name' => 'Test Client',
                'first_name' => 'Test',
                'last_name' => 'Client',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign client role
        if (!$user->hasRole('client')) {
            $user->assignRole('client');
        }

        // Create sample orders
        $orders = [
            [
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'service_type' => 'llc',
                'state' => 'CA',
                'package_type' => 'standard',
                'entity_name' => 'Tech Innovations LLC',
                'business_purpose' => 'Software development and consulting services',
                'status' => 'in_progress',
                'service_fee' => 499.00,
                'state_fee' => 70.00,
                'processing_fee' => 14.97,
                'subtotal' => 499.00,
                'total_amount' => 583.97,
                'contact_info' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@techinnov.com',
                    'phone' => '(555) 123-4567'
                ],
                'business_details' => [
                    'members' => [
                        ['name' => 'John Doe', 'ownership' => 100]
                    ]
                ],
                'requirements' => ['registered_agent', 'ein'],
                'estimated_completion_date' => now()->addDays(10),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ],
            [
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'service_type' => 'c_corp',
                'state' => 'DE',
                'package_type' => 'premium',
                'entity_name' => 'StartupCorp Inc.',
                'business_purpose' => 'Technology startup focused on AI solutions',
                'status' => 'completed',
                'service_fee' => 799.00,
                'state_fee' => 89.00,
                'processing_fee' => 23.97,
                'subtotal' => 799.00,
                'total_amount' => 911.97,
                'contact_info' => [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane@startupcorp.com',
                    'phone' => '(555) 987-6543'
                ],
                'business_details' => [
                    'directors' => [
                        ['name' => 'Jane Smith', 'title' => 'CEO']
                    ],
                    'shareholders' => [
                        ['name' => 'Jane Smith', 'shares' => 1000000]
                    ]
                ],
                'requirements' => ['registered_agent', 'ein', 'corporate_kit'],
                'estimated_completion_date' => now()->subDays(5),
                'completed_at' => now()->subDays(3),
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(3),
            ],
            [
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'service_type' => 's_corp',
                'state' => 'NY',
                'package_type' => 'basic',
                'entity_name' => 'Consulting Services Corp',
                'business_purpose' => 'Business consulting and advisory services',
                'status' => 'pending',
                'service_fee' => 399.00,
                'state_fee' => 125.00,
                'processing_fee' => 11.97,
                'subtotal' => 399.00,
                'total_amount' => 535.97,
                'contact_info' => [
                    'first_name' => 'Mike',
                    'last_name' => 'Johnson',
                    'email' => 'mike@consultingcorp.com',
                    'phone' => '(555) 456-7890'
                ],
                'business_details' => [
                    'directors' => [
                        ['name' => 'Mike Johnson', 'title' => 'President']
                    ]
                ],
                'requirements' => ['registered_agent'],
                'estimated_completion_date' => now()->addDays(14),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(1),
            ]
        ];

        foreach ($orders as $orderData) {
            \App\Models\Order::create($orderData);
        }
    }
}
