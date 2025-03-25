<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('coverage_date', function (Blueprint $table) {
            $table->id('covdate_id');
            $table->date('coverage_date_from');
            $table->date('coverage_date_to');
            $table->date('reading_date');
            $table->date('due_date');
            $table->enum('status', ['Open', 'Close'])->default('Open');
            $table->timestamps();

            // Add indexes
            $table->index(['coverage_date_from', 'coverage_date_to'], 'idx_coverage_dates');
            $table->index('reading_date', 'idx_reading_date');
            $table->index('due_date', 'idx_due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coverage_date');
    }
};
