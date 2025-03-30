<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class MySeeder extends Seeder
{
    public function run()
    {
        Permission::create([
            'name' => 'Access Meter Readers Block',
            'slug' => 'access-meter-readers-block',
            'description' => 'Can access meter readers block management'
        ]);
    }
}
