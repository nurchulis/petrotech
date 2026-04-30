<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'user', 'guard_name' => 'web', 'display_name' => 'User', 'description' => 'Basic user – VDI and ticketing access'],
            ['name' => 'admin', 'guard_name' => 'web', 'display_name' => 'Admin', 'description' => 'Operational admin – all modules except settings'],
            ['name' => 'super_admin', 'guard_name' => 'web', 'display_name' => 'Super Admin', 'description' => 'Full platform access including settings and user management'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
            ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                $role
            );
        }
    }
}