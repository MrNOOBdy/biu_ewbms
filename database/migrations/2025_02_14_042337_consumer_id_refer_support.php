<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the index exists before trying to drop it
        $indexExists = collect(DB::select("SHOW INDEXES FROM water_consumers"))
            ->where('Key_name', 'water_consumers_customer_id_index')
            ->isNotEmpty();

        if ($indexExists) {
            Schema::table('water_consumers', function (Blueprint $table) {
                $table->dropIndex(['customer_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the index doesn't exist before trying to create it
        $indexExists = collect(DB::select("SHOW INDEXES FROM water_consumers"))
            ->where('Key_name', 'water_consumers_customer_id_index')
            ->isNotEmpty();

        if (!$indexExists) {
            Schema::table('water_consumers', function (Blueprint $table) {
                $table->index('customer_id');
            });
        }
    }
};
