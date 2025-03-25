<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First, check if the table exists and recreate it if needed
        if (!Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->string('email')->nullable();
                $table->string('phone_number', 11)->nullable();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
                $table->index(['email']);
                $table->index(['phone_number']);
            });
            return;
        }

        // If table exists, modify it
        Schema::table('password_resets', function (Blueprint $table) {
            // Make email nullable if it's not already
            DB::statement('ALTER TABLE password_resets MODIFY email VARCHAR(255) NULL');
            
            // Add phone_number if it doesn't exist
            if (!Schema::hasColumn('password_resets', 'phone_number')) {
                $table->string('phone_number', 11)->nullable()->after('email')
                    ->comment('For SMS-based password resets');
                $table->index('phone_number');
            }
        });
    }

    public function down()
    {
        Schema::table('password_resets', function (Blueprint $table) {
            if (Schema::hasColumn('password_resets', 'phone_number')) {
                $table->dropIndex(['phone_number']);
                $table->dropColumn('phone_number');
            }
            // Make email not nullable again
            DB::statement('ALTER TABLE password_resets MODIFY email VARCHAR(255) NOT NULL');
        });
    }
};
