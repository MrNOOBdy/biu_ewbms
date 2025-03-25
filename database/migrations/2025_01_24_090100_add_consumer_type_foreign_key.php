<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('water_consumers', function (Blueprint $table) {
            // First ensure the consumer_type column in bill_rate table has unique constraint
            DB::statement('ALTER TABLE bill_rate MODIFY consumer_type VARCHAR(255) UNIQUE');
            
            // Add foreign key to existing consumer_type column
            $table->foreign('consumer_type')
                  ->references('consumer_type')
                  ->on('bill_rate')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('water_consumers', function (Blueprint $table) {
            $table->dropForeign(['consumer_type']);
        });
    }
};
