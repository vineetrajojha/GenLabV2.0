<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('essl_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_serial')->nullable();
            $table->string('device_name')->nullable();
            $table->string('source_ip')->nullable();
            $table->unsignedInteger('total_events')->default(0);
            $table->unsignedInteger('stored_records')->default(0);
            $table->unsignedInteger('skipped_manual')->default(0);
            $table->unsignedInteger('missing_employees')->default(0);
            $table->unsignedInteger('invalid_events')->default(0);
            $table->unsignedInteger('error_events')->default(0);
            $table->string('status')->default('success');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['device_serial', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('essl_sync_logs');
    }
};
