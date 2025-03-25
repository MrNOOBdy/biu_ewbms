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
        // First, create a coverage date if not exists
        $covDate = Cov_date::firstOrCreate([
            'coverage_date_from' => '2025-03-01',
            'coverage_date_to' => '2025-03-31',
            'reading_date' => '2025-03-25',
            'due_date' => '2025-04-05',
            'status' => 'Open'
        ]);

        // Updated readings with more realistic values
        $readings = [
            [
                'customer_id' => 'B01-01',
                'covdate_id' => $covDate->covdate_id,
                'reading_date' => '2025-03-25',
                'due_date' => '2025-04-05',
                'previous_reading' => 150,    // Previous month reading
                'present_reading' => 170,     // Current month reading
                'consumption' => 10,          // Difference: 10 cubic meters
                'meter_reader' => 'John Doe'
            ],
            [
                'customer_id' => 'B01-02',
                'covdate_id' => $covDate->covdate_id,
                'reading_date' => '2025-03-25',
                'due_date' => '2025-04-05',
                'previous_reading' => 170,
                'present_reading' => 195,
                'consumption' => 11,
                'meter_reader' => 'John Doe'
            ],
            [
                'customer_id' => 'B01-03',
                'covdate_id' => $covDate->covdate_id,
                'reading_date' => '2025-03-25',
                'due_date' => '2025-04-05',
                'previous_reading' => 275,
                'present_reading' => 375,
                'consumption' => 15,          // Industrial user with higher consumption
                'meter_reader' => 'John Doe'
            ],
            [
                'customer_id' => 'B02-01',
                'covdate_id' => $covDate->covdate_id,
                'reading_date' => '2025-03-25',
                'due_date' => '2025-04-05',
                'previous_reading' => 165,
                'present_reading' => 220,
                'consumption' => 12,
                'meter_reader' => 'John Doe'
            ],
            [
                'customer_id' => 'B03-01',
                'covdate_id' => $covDate->covdate_id,
                'reading_date' => '2025-03-25',
                'due_date' => '2025-04-05',
                'previous_reading' => 190,
                'present_reading' => 200,
                'consumption' => 10,
                'meter_reader' => 'John Doe'
            ],
        ];

        foreach ($readings as $reading) {
            ConsumerReading::create($reading);
        }
    }
}
