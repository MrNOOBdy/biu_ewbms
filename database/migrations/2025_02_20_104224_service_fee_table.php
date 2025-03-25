<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('service_fee_payment')) {
            Schema::create('service_fee_payment', function (Blueprint $table) {
                $table->id('service_pay_id');
                $table->unsignedBigInteger('customer_id');
                $table->decimal('service_amount_paid', 10, 2);
                $table->enum('service_paid_status', ['paid', 'unpaid'])->default('unpaid');
                $table->timestamps();
                
                $table->foreign('customer_id')
                      ->references('customer_id')
                      ->on('consumers')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_fee_payment');
    }
};
