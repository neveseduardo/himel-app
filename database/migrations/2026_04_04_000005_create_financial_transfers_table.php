<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->uuid('user_uid');
            $table->uuid('from_account_uid');
            $table->uuid('to_account_uid');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->foreign('user_uid')->references('uid')->on('users')->onDelete('cascade');
            $table->foreign('from_account_uid')->references('uid')->on('accounts')->onDelete('cascade');
            $table->foreign('to_account_uid')->references('uid')->on('accounts')->onDelete('cascade');
            $table->index('user_uid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
