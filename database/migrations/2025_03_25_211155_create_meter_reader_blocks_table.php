<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meter_reader_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('block_id');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('block_id')->references('block_id')->on('blocks')->onDelete('cascade');
            
            // Prevent duplicate assignments
            $table->unique(['user_id', 'block_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('meter_reader_blocks');
    }
};
