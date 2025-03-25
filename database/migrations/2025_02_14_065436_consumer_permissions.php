<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $lastPermissionId = DB::table('permissions')->max('permission_id') ?? 0;
        $newPermissions = [
            [
                'permission_id' => ++$lastPermissionId,
                'name' => 'Add New Consumer',
                'slug' => 'add-new-consumer',
                'description' => 'Permission to add new water consumers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => ++$lastPermissionId,
                'name' => 'Edit Consumer',
                'slug' => 'edit-consumer',
                'description' => 'Permission to edit existing water consumers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => ++$lastPermissionId,
                'name' => 'View Consumer Billings',
                'slug' => 'view-consumer-billings',
                'description' => 'Permission to view consumer billing history',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => ++$lastPermissionId,
                'name' => 'Delete Consumer',
                'slug' => 'delete-consumer',
                'description' => 'Permission to delete water consumers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => ++$lastPermissionId,
                'name' => 'Reconnect Consumer',
                'slug' => 'reconnect-consumer',
                'description' => 'Permission to reconnect inactive water consumers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('permissions')->insert($newPermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('slug', [
            'add-new-consumer',
            'edit-consumer',
            'view-consumer-billings',
            'delete-consumer',
            'reconnect-consumer'
        ])->delete();
    }
};
