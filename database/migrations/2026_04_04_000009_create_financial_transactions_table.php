<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->uuid('user_uid');
            $table->uuid('financial_account_uid');
            $table->uuid('financial_category_uid');
            $table->decimal('amount', 15, 2);
            $table->enum('direction', ['INFLOW', 'OUTFLOW']);
            $table->enum('status', ['PENDING', 'PAID', 'OVERDUE'])->default('PENDING');
            $table->enum('source', ['MANUAL', 'CREDIT_CARD', 'FIXED', 'TRANSFER'])->default('MANUAL');
            $table->dateTime('occurred_at');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->timestamps();

            $table->foreignUuid('user_uid')->references('uid')->on('users')->onDelete('cascade');
            $table->foreignUuid('financial_account_uid')->references('uid')->on('financial_accounts')->onDelete('cascade');
            $table->foreignUuid('financial_category_uid')->references('uid')->on('financial_categories')->onDelete('restrict');
            $table->index('user_uid');
            $table->index('financial_account_uid');
            $table->index('reference_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
