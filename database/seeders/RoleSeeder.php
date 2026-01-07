<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create 'koor' role
        Role::firstOrCreate(['name' => 'koor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'dosen', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);
        
        // Optional: Create other common roles if they don't exist
        // Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        // Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    }
}
