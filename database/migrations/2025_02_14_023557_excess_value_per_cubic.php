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
        Schema::table('bill_rate', function (Blueprint $table) {
            $table->decimal('excess_value_per_cubic', 8, 2)->after('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_rate', function (Blueprint $table) {
            $table->dropColumn('excess_value_per_cubic');
        });
    }
};
