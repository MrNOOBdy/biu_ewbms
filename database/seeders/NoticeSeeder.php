<?php

namespace Database\Seeders;

use App\Models\ManageNotice;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate the table to remove all existing data
        ManageNotice::truncate();

        $types = ['Maintenance', 'Advisory', 'Emergency', 'Schedule', 'Update', 'Service', 'Notice'];
        $messages = [
            'Water interruption in %s area. Expected duration: %d hours.',
            'Scheduled maintenance for %s starting at %d:00.',
            'Emergency repair needed in %s district.',
            'System upgrade notification for %s region.',
            'Important advisory for residents of %s zone.',
            'Water pressure might be low in %s area for %d hours.',
            'Quality check scheduled for %s district at %d:00.',
            'Service improvement in %s area. Duration: %d hours.',
            'Routine maintenance check in %s zone.',
            'System flushing activity in %s region.'
        ];
        $areas = [
            'North', 'South', 'East', 'West', 'Central',
            'Northwest', 'Northeast', 'Southwest', 'Southeast',
            'Upper', 'Lower', 'Mid', 'Downtown', 'Uptown',
            'Phase 1', 'Phase 2', 'Phase 3', 'Block A', 'Block B', 'Block C'
        ];
        
        for ($i = 1; $i <= 200; $i++) {
            $type = $types[array_rand($types)];
            $message = $messages[array_rand($messages)];
            $area = $areas[array_rand($areas)];
            $hours = rand(1, 12);
            
            ManageNotice::create([
                'type' => $type,
                'announcement' => sprintf($message, $area, $hours),
            ]);
        }
    }
}
