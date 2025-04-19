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
        Schema::table('consumer_reading', function (Blueprint $table) {
            $table->boolean('sms_sent')->default(false)->after('meter_reader');
            $table->timestamp('sms_sent_at')->nullable()->after('sms_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumer_reading', function (Blueprint $table) {
            $table->dropColumn(['sms_sent', 'sms_sent_at']);
        });
    }
};
