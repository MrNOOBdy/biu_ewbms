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
        Schema::create('consumer_reading', function (Blueprint $table) {
            $table->id('consread_id');
            $table->foreignId('customer_id')->references('watercon_id')->on('water_consumers')->onDelete('cascade');
            $table->foreignId('covdate_id')->constrained('coverage_date')->onDelete('cascade');
            $table->date('reading_date');
            $table->date('due_date');
            $table->decimal('previous_reading', 10, 2);
            $table->decimal('present_reading', 10, 2);
            $table->decimal('consumption', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('bill_status', ['paid', 'unpaid'])->default('unpaid');
            $table->timestamps();

            // Add indexes for better query performance
            $table->index('reading_date');
            $table->index('due_date');
            $table->index('bill_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    
    public function down(): void
    {
        Schema::dropIfExists('consumer_reading');
    }
};
