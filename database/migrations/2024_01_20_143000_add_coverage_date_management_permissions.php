<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCoverageDateManagementPermissions extends Migration
{
    public function up()
    {
        // Get the next available permission_id
        $lastPermissionId = DB::table('permissions')->max('permission_id') ?? 0;
        
        DB::table('permissions')->insert([
            [
                'permission_id' => $lastPermissionId + 1,
                'name' => 'Access Coverage Date',
                'slug' => 'access-coverage-date',
                'description' => 'Can access coverage date management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => $lastPermissionId + 2,
                'name' => 'Add Coverage Date',
                'slug' => 'add-coverage-date',
                'description' => 'Can add new coverage date',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => $lastPermissionId + 3,
                'name' => 'Edit Coverage Date',
                'slug' => 'edit-coverage-date',
                'description' => 'Can edit coverage date',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'permission_id' => $lastPermissionId + 4,
                'name' => 'Delete Coverage Date',
                'slug' => 'delete-coverage-date',
                'description' => 'Can delete coverage date',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Assign permissions to admin role by default
        $adminRoleId = DB::table('roles')->where('name', 'Admin')->value('role_id');
        if ($adminRoleId) {
            $permissions = DB::table('permissions')
                ->whereIn('slug', [
                    'access-coverage-date',
                    'add-coverage-date',
                    'edit-coverage-date',
                    'delete-coverage-date'
                ])
                ->get();

            foreach ($permissions as $permission) {
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $adminRoleId,
                    'permission_id' => $permission->permission_id
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('permissions')
            ->whereIn('slug', [
                'access-coverage-date',
                'add-coverage-date',
                'edit-coverage-date',
                'delete-coverage-date'
            ])
            ->delete();
    }
}
