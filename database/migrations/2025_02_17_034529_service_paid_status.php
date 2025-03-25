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
    Schema::table('conn_payment', function (Blueprint $table) {
        $table->enum('service_paid_status', ['paid', 'unpaid'])->default('unpaid');
    });
}

public function down(): void
{
    Schema::table('conn_payment', function (Blueprint $table) {
        $table->dropColumn('service_paid_status');
    });
}
};
