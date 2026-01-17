<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolesAndPermissionsSeeder::class);

        // Create super admin user
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@luckycore.com',
        ]);
        $superAdmin->assignRole(UserRole::SuperAdmin->value);

        // Create test users for each role (only in local/testing)
        if (app()->environment(['local', 'testing'])) {
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
            $admin->assignRole(UserRole::Admin->value);

            $support = User::factory()->create([
                'name' => 'Support User',
                'email' => 'support@example.com',
            ]);
            $support->assignRole(UserRole::Support->value);

            $customer = User::factory()->create([
                'name' => 'Test Customer',
                'email' => 'customer@example.com',
            ]);
            $customer->assignRole(UserRole::Customer->value);

            // Seed sample data for development
            $this->call(SampleDataSeeder::class);
        }
    }
}
