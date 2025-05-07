<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $lastPermissionId = DB::table('permissions')->max('permission_id') ?? 0;
        
        DB::table('permissions')->insert([
            'permission_id' => ++$lastPermissionId,
            'name' => 'Add Meter Reading',
            'slug' => 'add-meter-reading',
            'description' => 'Permission to add new meter readings',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('permissions')->where('slug', 'add-meter-reading')->delete();
    }
};