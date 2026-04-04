<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->string('name');
            $table->enum('direction', ['INFLOW', 'OUTFLOW']);
            $table->timestamps();

            $table->foreignUuid('user_uid')->references('uid')->on('users')->onDelete('cascade');
            $table->index('user_uid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_categories');
    }
};
