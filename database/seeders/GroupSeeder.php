<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use App\Models\Vm;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample groups
        $it      = Group::firstOrCreate(['name' => 'IT'],      ['description' => 'Information Technology Department']);
        $finance = Group::firstOrCreate(['name' => 'Finance'], ['description' => 'Finance & Accounting Department']);
        $hr      = Group::firstOrCreate(['name' => 'HR'],      ['description' => 'Human Resources Department']);

        // Assign users to groups (use existing users if available)
        $users = User::where('is_active', true)->get();
        if ($users->count() >= 3) {
            $it->users()->syncWithoutDetaching($users->take(3)->pluck('id'));
            $finance->users()->syncWithoutDetaching($users->skip(1)->take(2)->pluck('id'));
            $hr->users()->syncWithoutDetaching($users->skip(2)->take(2)->pluck('id'));
        }

        // Assign VMs to groups
        $vms = Vm::all();
        if ($vms->count() >= 2) {
            $it->vms()->syncWithoutDetaching($vms->take(3)->pluck('id'));
            $finance->vms()->syncWithoutDetaching($vms->take(1)->pluck('id'));
        }

        // Direct user access: give first user access to first VM
        $firstUser = User::first();
        $firstVm   = Vm::first();
        if ($firstUser && $firstVm) {
            $firstUser->directVmAccess()->syncWithoutDetaching([$firstVm->id]);
        }
    }
}
