<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillRateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_rate', function (Blueprint $table) {
            $table->id();
            $table->string('consumer_type')->unique();
            $table->decimal('cubic_meter', 8, 2);
            $table->decimal('max_cubic', 8, 2);
            $table->decimal('value', 10, 2);
            $table->decimal('excess_value_per_cubic', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bill_rate');
    }
}
