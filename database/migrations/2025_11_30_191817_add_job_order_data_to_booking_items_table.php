<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('booking_items', 'job_order_date')) {
            Schema::table('booking_items', function (Blueprint $table) {
                $table->date('job_order_date')->nullable()->after('job_order_no');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('booking_items', 'job_order_date')) {
            Schema::table('booking_items', function (Blueprint $table) {
                $table->dropColumn('job_order_date');
            });
        }
    }
};
