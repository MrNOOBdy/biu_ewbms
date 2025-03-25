<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFeeManagementPermissions extends Migration
{
    public function up()
    {
        $lastPermissionId = DB::table('permissions')->max('permission_id') ?? 0;
        
        DB::table('permissions')->insert([
            'permission_id' => $lastPermissionId + 1,
            'name' => 'Edit Fees',
            'slug' => 'edit-fees',
            'description' => 'Permission to edit application and reconnection fees',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('permissions')->where('slug', 'edit-fees')->delete();
    }
}
