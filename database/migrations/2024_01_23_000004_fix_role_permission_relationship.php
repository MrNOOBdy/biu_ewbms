<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. First check and drop any existing foreign keys
        Schema::table('role_permission', function (Blueprint $table) {
            // Get foreign key name if it exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'role_permission'
                AND REFERENCED_TABLE_NAME = 'roles'
            ");

            foreach ($foreignKeys as $key) {
                DB::statement("ALTER TABLE role_permission DROP FOREIGN KEY `{$key->CONSTRAINT_NAME}`");
            }
        });

        // 2. Make sure both columns have the same type
        DB::statement('ALTER TABLE role_permission MODIFY role_id BIGINT UNSIGNED');
        DB::statement('ALTER TABLE roles MODIFY role_id BIGINT UNSIGNED AUTO_INCREMENT');

        // 3. Create the foreign key with the correct column types
        Schema::table('role_permission', function (Blueprint $table) {
            $table->foreign('role_id')
                  ->references('role_id')
                  ->on('roles')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('role_permission', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
    }
};
