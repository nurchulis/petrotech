<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Ticket;
use App\Models\User;

class TicketDummySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@petrotech.id')->first();
        $user = User::where('email', 'user@petrotech.id')->first();

        if (!$user) {
            $this->command->info('No users found (admin/user), please run UserSeeder first.');
            return;
        }

        $statuses = ['open', 'in_progress', 'resolved', 'closed'];
        $priorities = ['critical', 'high', 'medium', 'low'];

        $now = Carbon::now();

        // Create a handful of tickets distributed over the last 30 days
        for ($i = 0; $i < 20; $i++) {
            $createdAt = $now->copy()->subDays(rand(0, 29))->subHours(rand(0, 23));
            $status = $statuses[array_rand($statuses)];
            $priority = $priorities[array_rand($priorities)];

            Ticket::create([
                'title' => "Sample Issue #" . rand(1000, 9999),
                'description' => 'Automatically generated sample ticket for analytics.',
                'category' => 'General',
                'priority' => $priority,
                'status' => $status,
                'assigned_to' => $admin?->id,
                'created_by' => $user->id,
                'created_at' => $createdAt,
            ]);
        }

        $this->command->info('Inserted 20 sample tickets for analytics.');
    }
}
