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
        Schema::create('meter_reader_substitutions', function (Blueprint $table) {
            $table->id();
            $table->string('absent_reader_id');
            $table->string('substitute_reader_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('active');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_reader_substitutions');
    }
};
