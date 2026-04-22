<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->uuid('user_uid');
            $table->uuid('account_uid');
            $table->uuid('category_uid')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('direction', ['INFLOW', 'OUTFLOW']);
            $table->enum('status', ['PENDING', 'PAID', 'OVERDUE'])->default('PENDING');
            $table->enum('source', ['MANUAL', 'CREDIT_CARD', 'FIXED', 'TRANSFER'])->default('MANUAL');
            $table->string('description', 255)->nullable();
            $table->dateTime('occurred_at');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('period_uid')->nullable();
            $table->timestamps();

            $table->foreign('user_uid')->references('uid')->on('users')->onDelete('cascade');
            $table->foreign('account_uid')->references('uid')->on('accounts')->onDelete('cascade');
            $table->foreign('category_uid')->references('uid')->on('categories')->onDelete('restrict');
            $table->foreign('period_uid')->references('uid')->on('periods')->onDelete('set null');
            $table->index('user_uid');
            $table->index('account_uid');
            $table->index('reference_id');
            $table->index('period_uid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
