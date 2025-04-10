<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'username' => 'johndoe',
            'password' => bcrypt('password123'),
            'contactnum' => '09123456789',
            'role' => 'user',
            'status' => 'activate',
        ]);

        $this->call([
            TabPermissionsSeeder::class,
            MySeeder::class
        ]);
    }
}
