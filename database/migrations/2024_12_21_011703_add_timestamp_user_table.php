<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampUserTable extends Migration
{
    public function up()
    {
        // Schema::table('users', function (Blueprint $table) {
        //     $table->timestamp('created_at')->nullable();
        //     $table->timestamp('updated_at')->nullable();
        // });
    }

    public function down()
    {
        // Schema::table('users', function (Blueprint $table) {
        //     $table->dropColumn(['created_at', 'updated_at']);
        // });
    }
}
