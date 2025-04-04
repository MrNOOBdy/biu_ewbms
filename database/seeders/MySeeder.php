<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Consumer;
use App\Models\Fees;
use App\Models\Block;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class MySeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('en_PH');
        
        $applicationFee = Fees::firstOrCreate(
            ['fee_type' => 'Application Fee'],
            ['amount' => 1050.00]
        );

        $blocks = Block::all();
        $consumerTypes = DB::table('bill_rate')->pluck('consumer_type')->toArray();

        for ($i = 0; $i < 100; $i++) {
            try {
                DB::beginTransaction();

                $block = $faker->randomElement($blocks->toArray());
                $blockId = $block['block_id'];
                
                $barangays = is_array($block['barangays']) ? $block['barangays'] : json_decode($block['barangays'], true);
                
                $purok = 'Purok ' . $faker->numberBetween(1, 7);
                $barangay = $faker->randomElement($barangays);
                $address = $purok . ', ' . $barangay;

                $lastConsumer = Consumer::where('block_id', $blockId)
                    ->orderBy('customer_id', 'desc')
                    ->first();

                if (!$lastConsumer) {
                    $customerId = sprintf("B%02d-01", $blockId);
                } else {
                    $parts = explode('-', $lastConsumer->customer_id);
                    $number = intval($parts[1]) + 1;
                    $customerId = sprintf("B%02d-%02d", $blockId, $number);
                }

                $consumer = Consumer::create([
                    'block_id' => $blockId,
                    'customer_id' => $customerId,
                    'firstname' => $faker->firstName,
                    'middlename' => $faker->optional(0.7)->lastName,
                    'lastname' => $faker->lastName,
                    'address' => $address,
                    'contact_no' => '09' . $faker->numberBetween(100000000, 999999999),
                    'consumer_type' => $faker->randomElement($consumerTypes),
                    'status' => 'Pending',
                    'application_fee' => $applicationFee->amount,
                    'service_fee' => 0
                ]);

                DB::table('conn_payment')->insert([
                    'customer_id' => $customerId,
                    'application_fee' => $applicationFee->amount,
                    'conn_amount_paid' => 0,
                    'conn_pay_status' => 'unpaid',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("Error creating consumer {$i}: " . $e->getMessage());
            }
        }

        $this->command->info('Successfully seeded 100 consumers with their application fees!');
    }
}
