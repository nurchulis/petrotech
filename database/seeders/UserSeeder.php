<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'       => 'Super Administrator',
                'email'      => 'superadmin@petrotech.id',
                'password'   => Hash::make('password'),
                'employee_id'=> 'EMP-001',
                'department' => 'IT Infrastructure',
                'phone'      => '+62-21-5550001',
                'is_active'  => true,
                'role'       => 'super_admin',
            ],
            [
                'name'       => 'Admin Petrotech',
                'email'      => 'admin@petrotech.id',
                'password'   => Hash::make('password'),
                'employee_id'=> 'EMP-002',
                'department' => 'IT Operations',
                'phone'      => '+62-21-5550002',
                'is_active'  => true,
                'role'       => 'admin',
            ],
            [
                'name'       => 'Budi Santoso',
                'email'      => 'user@petrotech.id',
                'password'   => Hash::make('password'),
                'employee_id'=> 'EMP-003',
                'department' => 'Upstream Engineering',
                'phone'      => '+62-21-5550003',
                'is_active'  => true,
                'role'       => 'user',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);
            $user = User::updateOrCreate(['email' => $data['email']], $data);
            $user->syncRoles([$role]);
        }
    }
}
