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
        Schema::table('conn_payment', function (Blueprint $table) {
            $table->decimal('service_amount_paid', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conn_payment', function (Blueprint $table) {
            $table->dropColumn('service_amount_paid');
        });
    }
};
