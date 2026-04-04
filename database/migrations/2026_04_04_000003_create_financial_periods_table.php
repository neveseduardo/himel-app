<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_periods', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->uuid('user_uid');
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->timestamps();

            $table->foreignUuid('user_uid')->references('uid')->on('users')->onDelete('cascade');
            $table->unique(['user_uid', 'month', 'year']);
            $table->index('user_uid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_periods');
    }
};
