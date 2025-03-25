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
        $maxId = DB::table('manage_notice')->max('id') ?? 0;

        // Add new notice_id column
        Schema::table('manage_notice', function (Blueprint $table) {
            $table->unsignedBigInteger('notice_id')->nullable()->after('id');
        });

        // Copy data
        DB::statement('UPDATE manage_notice SET notice_id = id');

        // Drop old id and set notice_id as primary key
        Schema::table('manage_notice', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        DB::statement('ALTER TABLE manage_notice MODIFY notice_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');

        // Set auto increment start value
        if ($maxId > 0) {
            DB::statement("ALTER TABLE manage_notice AUTO_INCREMENT = " . ($maxId + 1));
        }
    }

    public function down()
    {
        Schema::table('manage_notice', function (Blueprint $table) {
            $table->dropPrimary();
            $table->id()->first();
            $table->dropColumn('notice_id');
        });
    }
};
