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
        // Add reconnection_fee to service_fee_payment
        Schema::table('service_fee_payment', function (Blueprint $table) {
            $table->decimal('reconnection_fee', 10, 2)->nullable()->after('service_amount_paid');
        });

        // Remove reconnection_fee from conn_payment
        Schema::table('conn_payment', function (Blueprint $table) {
            $table->dropColumn('reconnection_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back reconnection_fee to conn_payment
        Schema::table('conn_payment', function (Blueprint $table) {
            $table->decimal('reconnection_fee', 10, 2)->nullable();
        });

        // Remove reconnection_fee from service_fee_payment
        Schema::table('service_fee_payment', function (Blueprint $table) {
            $table->dropColumn('reconnection_fee');
        });
    }
};
