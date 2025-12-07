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
        // Index for reference_no (autocomplete)
        Schema::table('new_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('new_bookings', 'reference_no')) return;
            $table->index('reference_no', 'idx_reference_no');
        });

        // Index for job_order_no (autocomplete)
        Schema::table('booking_items', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_items', 'job_order_no')) return;
            $table->index('job_order_no', 'idx_job_order_no');
        });

        // Index for user_code and name for Lab / Marketing autocomplete
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_code')) {
                $table->index('user_code', 'idx_user_code');
            }
            if (Schema::hasColumn('users', 'name')) {
                $table->index('name', 'idx_user_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_bookings', function (Blueprint $table) {
            $table->dropIndex('idx_reference_no');
        });

        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropIndex('idx_job_order_no');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_user_code');
            $table->dropIndex('idx_user_name');
        });
    }
};
