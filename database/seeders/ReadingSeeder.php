<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Consumer;
use App\Models\ConsumerReading;
use App\Models\Cov_date;
use App\Models\MeterReaderBlock;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class ReadingSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        
        // Get all active consumers grouped by block
        $consumers = Consumer::where('status', 'Active')
            ->get()
            ->groupBy('block_id');

        // Get current open coverage date
        $coverageDate = Cov_date::where('status', 'Open')->first();
        if (!$coverageDate) {
            $this->command->error('No open coverage date found!');
            return;
        }

        // Set reading date to March 2024
        $readingDate = '2025-04-23';
        
        foreach ($consumers as $blockId => $blockConsumers) {
            try {
                // Get meter reader for this block
                $meterReader = MeterReaderBlock::with('user')
                    ->where('block_id', $blockId)
                    ->first();

                if (!$meterReader || !$meterReader->user) {
                    $this->command->error("No meter reader assigned to block {$blockId}");
                    continue;
                }

                $meterReaderName = $meterReader->user->firstname . ' ' . $meterReader->user->lastname;

                foreach ($blockConsumers as $consumer) {
                    DB::beginTransaction();
                    try {
                        // Generate realistic meter readings
                        $previousReading = $faker->numberBetween(100, 500);
                        $presentReading = $previousReading + $faker->numberBetween(5, 30);
                        $consumption = $presentReading - $previousReading;

                        // Create the reading record
                        $reading = ConsumerReading::create([
                            'customer_id' => $consumer->customer_id,
                            'covdate_id' => $coverageDate->covdate_id,
                            'reading_date' => $readingDate,
                            'due_date' => $coverageDate->due_date,
                            'previous_reading' => $previousReading,
                            'present_reading' => $presentReading,
                            'consumption' => $consumption,
                            'meter_reader' => $meterReaderName,
                            'created_at' => $readingDate,
                            'updated_at' => $readingDate
                        ]);

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->command->error("Error creating reading for consumer {$consumer->customer_id}: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                $this->command->error("Error processing block {$blockId}: " . $e->getMessage());
            }
        }

        $this->command->info('Successfully seeded readings and bills for all active consumers!');
    }
}
