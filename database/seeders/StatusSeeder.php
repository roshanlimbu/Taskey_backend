<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'pending',
                'color' => '#6b7280',
                'description' => 'Task is pending and not yet started'
            ],
            [
                'name' => 'in_progress',
                'color' => '#f59e0b',
                'description' => 'Task is currently being worked on'
            ],
            [
                'name' => 'review',
                'color' => '#6366f1',
                'description' => 'Task is under review'
            ],
            [
                'name' => 'done',
                'color' => '#10b981',
                'description' => 'Task has been completed successfully'
            ],
            [
                'name' => 'blocked',
                'color' => '#ef4444',
                'description' => 'Task is blocked and cannot proceed'
            ],
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}
