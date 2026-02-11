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

        // Create super admin user (idempotent: reuse if already exists)
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@luckycore.com'],
            User::factory()->make(['name' => 'Super Admin', 'email' => 'admin@luckycore.com'])->getAttributes()
        );
        $superAdmin->assignRole(UserRole::SuperAdmin->value);

        // Create test users for each role (only in local/testing)
        if (app()->environment(['local', 'testing'])) {
            $admin = User::firstOrCreate(
                ['email' => 'admin@example.com'],
                User::factory()->make(['name' => 'Admin User', 'email' => 'admin@example.com'])->getAttributes()
            );
            $admin->assignRole(UserRole::Admin->value);

            $support = User::firstOrCreate(
                ['email' => 'support@example.com'],
                User::factory()->make(['name' => 'Support User', 'email' => 'support@example.com'])->getAttributes()
            );
            $support->assignRole(UserRole::Support->value);

            $customer = User::firstOrCreate(
                ['email' => 'customer@example.com'],
                User::factory()->make(['name' => 'Test Customer', 'email' => 'customer@example.com'])->getAttributes()
            );
            $customer->assignRole(UserRole::Customer->value);

            // Seed sample data for development
            $this->call(SampleDataSeeder::class);
        }
    }
}
