<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['name', 'email']); // Drop the old columns
        $table->string('firstname');
        $table->string('lastname');
        $table->string('username')->unique();
        $table->string('contactnum')->unique();
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('name');
        $table->string('email')->unique();
        $table->dropColumn(['firstname', 'lastname', 'username', 'contactnum']);
    });
}

};
