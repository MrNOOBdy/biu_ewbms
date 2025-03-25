<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if the primary key exists first
        $hasPrimaryKey = DB::select("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY'");
        
        if ($hasPrimaryKey) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropPrimary();
            });
        }

        // Set the column type and make it auto-increment
        DB::statement('ALTER TABLE users MODIFY user_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // Get the current max value to set auto_increment start
        $maxId = DB::table('users')->max('user_id') ?? 0;
        if ($maxId > 0) {
            DB::statement("ALTER TABLE users AUTO_INCREMENT = " . ($maxId + 1));
        }
    }

    public function down()
    {
        // Check if the primary key exists first
        $hasPrimaryKey = DB::select("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY'");
        
        if ($hasPrimaryKey) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropPrimary();
            });
        }

        // Revert to non-auto-increment
        DB::statement('ALTER TABLE users MODIFY user_id BIGINT NOT NULL');
        
        // Re-add primary key
        Schema::table('users', function (Blueprint $table) {
            $table->primary('user_id');
        });
    }
};
