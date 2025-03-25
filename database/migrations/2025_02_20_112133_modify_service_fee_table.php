<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_fee_payment', function (Blueprint $table) {
            // Simply change column type to string
            $table->string('customer_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_fee_payment', function (Blueprint $table) {
            // Change back to unsignedBigInteger if needed
            $table->unsignedBigInteger('customer_id')->change();
        });
    }
};
