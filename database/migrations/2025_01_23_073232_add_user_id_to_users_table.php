<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable()->after('id');
        });

        // Copy data from id to user_id
        DB::statement('UPDATE users SET user_id = id');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->primary('user_id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary();
            $table->bigIncrements('id')->first();
            $table->dropColumn('user_id');
        });
    }
};
