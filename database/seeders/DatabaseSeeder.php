<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolePermissionSeeder::class);
        
        // Seed sample data (orders, documents, payments, messages, notifications)
        $this->call(SampleDataSeeder::class);
        
        // Seed professional knowledge base articles
        $this->call(ArticleSeeder::class);

        $this->command->info('All seeders completed successfully!');
    }
}
