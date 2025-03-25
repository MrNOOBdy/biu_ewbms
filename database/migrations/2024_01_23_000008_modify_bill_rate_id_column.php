<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Get current max id
        $maxId = DB::table('bill_rate')->max('id') ?? 0;

        // Add new billrate_id column
        Schema::table('bill_rate', function (Blueprint $table) {
            $table->unsignedBigInteger('billrate_id')->nullable()->after('id');
        });

        // Copy data
        DB::statement('UPDATE bill_rate SET billrate_id = id');

        // Drop old id and set billrate_id as primary key
        Schema::table('bill_rate', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        DB::statement('ALTER TABLE bill_rate MODIFY billrate_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // Set auto increment start value
        if ($maxId > 0) {
            DB::statement("ALTER TABLE bill_rate AUTO_INCREMENT = " . ($maxId + 1));
        }
    }

    public function down()
    {
        Schema::table('bill_rate', function (Blueprint $table) {
            $table->dropPrimary();
            $table->id()->first();
            $table->dropColumn('billrate_id');
        });
    }
};
