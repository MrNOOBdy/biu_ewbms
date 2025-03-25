<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCoverageDatePermissions extends Migration
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
                'name' => 'Access Coverage Date',
                'slug' => 'access-coverage-date',
                'description' => 'Permission to access coverage date management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => $lastPermissionId + 2,
                'name' => 'Add Coverage Date',
                'slug' => 'add-coverage-date',
                'description' => 'Permission to add new coverage date',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => $lastPermissionId + 3,
                'name' => 'Edit Coverage Date',
                'slug' => 'edit-coverage-date',
                'description' => 'Permission to edit coverage date',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => $lastPermissionId + 4,
                'name' => 'Delete Coverage Date',
                'slug' => 'delete-coverage-date',
                'description' => 'Permission to delete coverage date',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Assign permissions to admin role by default
        $adminRoleId = DB::table('roles')->where('name', 'Admin')->value('role_id');
        if ($adminRoleId) {
            for ($i = 0; $i < 4; $i++) {
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $adminRoleId,
                    'permission_id' => $lastPermissionId + ($i + 1)
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->whereIn('slug', [
            'access-coverage-date',
            'add-coverage-date',
            'edit-coverage-date',
            'delete-coverage-date'
        ])->delete();
    }
}
