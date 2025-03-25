<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateFeesTable extends Migration
{
    public function up()
    {
        DB::table('fees')
            ->where('fee_type', 'Service Fee')
            ->update(['fee_type' => 'Reconnection Fee']);
    }

    public function down()
    {
        DB::table('fees')
            ->where('fee_type', 'Reconnection Fee')
            ->update(['fee_type' => 'Service Fee']);
    }
}
