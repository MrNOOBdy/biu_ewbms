<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    public function up()
    {
        // Schema::table('users', function (Blueprint $table) {
        //     if (!Schema::hasColumn('users', 'firstname')) {
        //         $table->string('firstname')->after('id');
        //     }
        //     if (!Schema::hasColumn('users', 'lastname')) {
        //         $table->string('lastname')->after('firstname');
        //     }
        //     if (!Schema::hasColumn('users', 'username')) {
        //         $table->string('username')->unique()->after('lastname');
        //     }
        //     if (!Schema::hasColumn('users', 'contactnum')) {
        //         $table->string('contactnum')->unique()->after('username');
        //     }
        // });
    }    

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['firstname', 'lastname', 'username', 'contactnum']);
        });
    }
}
