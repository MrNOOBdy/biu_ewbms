<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddBlockManagementPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get the next available permission_id
        $lastPermissionId = DB::table('permissions')->max('permission_id') ?? 0;
        
        DB::table('permissions')->insert([
            [
                'permission_id' => $lastPermissionId + 1,
                'name' => 'Access Block Management',
                'slug' => 'access-block-management',
                'description' => 'Permission to access block management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => $lastPermissionId + 2,
                'name' => 'Add New Block',
                'slug' => 'add-new-block',
                'description' => 'Permission to add a new block',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => $lastPermissionId + 3,
                'name' => 'Edit Block',
                'slug' => 'edit-block',
                'description' => 'Permission to edit a block',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => $lastPermissionId + 4,
                'name' => 'Delete Block',
                'slug' => 'delete-block',
                'description' => 'Permission to delete a block',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->whereIn('slug', [
            'access-block-management',
            'add-new-block',
            'edit-block',
            'delete-block'
        ])->delete();
    }
}
