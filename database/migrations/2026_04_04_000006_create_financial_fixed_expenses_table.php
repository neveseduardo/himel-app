<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_fixed_expenses', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->uuid('user_uid');
            $table->uuid('financial_category_uid');
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->tinyInteger('due_day');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('user_uid')->references('uid')->on('users')->onDelete('cascade');
            $table->foreign('financial_category_uid')->references('uid')->on('financial_categories')->onDelete('restrict');
            $table->index('user_uid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_fixed_expenses');
    }
};
