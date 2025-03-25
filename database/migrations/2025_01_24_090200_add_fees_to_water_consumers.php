<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('water_consumers', function (Blueprint $table) {
            $table->decimal('application_fee', 10, 2)->default(0.00)->after('status');
            $table->decimal('service_fee', 10, 2)->default(0.00)->after('application_fee');
        });
    }

    public function down()
    {
        Schema::table('water_consumers', function (Blueprint $table) {
            $table->dropColumn(['application_fee', 'service_fee']);
        });
    }
};
