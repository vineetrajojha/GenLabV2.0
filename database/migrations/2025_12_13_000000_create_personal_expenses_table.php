<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('personal_expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_code')->index();
            $table->string('section')->nullable();
            $table->date('expense_date')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=pending,1=approved,2=rejected');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personal_expenses');
    }
};
