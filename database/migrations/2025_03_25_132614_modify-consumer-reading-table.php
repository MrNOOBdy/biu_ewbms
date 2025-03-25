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
        Schema::table('consumer_reading', function (Blueprint $table) {
            $table->dropColumn(['total_amount', 'bill_status']);
            $table->string('meter_reader')->after('consumption');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumer_reading', function (Blueprint $table) {
            $table->decimal('total_amount', 10, 2);
            $table->string('bill_status');
            $table->dropColumn('meter_reader');
        });
    }
};
