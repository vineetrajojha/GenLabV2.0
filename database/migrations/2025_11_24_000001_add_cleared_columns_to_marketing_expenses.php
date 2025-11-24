<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->timestamp('cleared_at')->nullable()->after('approved_at')->index();
            $table->unsignedBigInteger('cleared_by')->nullable()->after('cleared_at');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_expenses', function (Blueprint $table) {
            $table->dropColumn(['cleared_at','cleared_by']);
        });
    }
};
