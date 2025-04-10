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
        Schema::table('coverage_date', function (Blueprint $table) {
            $table->dropColumn('reading_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coverage_date', function (Blueprint $table) {
            $table->date('reading_date')->after('coverage_date_to');
        });
    }
};
