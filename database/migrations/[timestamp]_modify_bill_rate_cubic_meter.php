<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bill_rate', function (Blueprint $table) {
            $table->decimal('cubic_meter', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('bill_rate', function (Blueprint $table) {
            $table->integer('cubic_meter')->change();
        });
    }
};
