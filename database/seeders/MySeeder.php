<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\ConsumerReading;
use App\Models\Cov_date;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Updated readings with more realistic values
        $readings = [
            [
                'customer_id' => 'B01-01',
                'covdate_id' => '4',
                'reading_date' => '2025-03-25',
                'due_date' => '2025-04-01',
                'previous_reading' => 150,   
                'present_reading' => 170,    
                'consumption' => 10,    
                'meter_reader' => 'John Doe'
            ],
        ];

        foreach ($readings as $reading) {
            ConsumerReading::create($reading);
        }
    }
}
