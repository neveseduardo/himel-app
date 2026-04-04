<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->string('name');
            $table->enum('type', ['CHECKING', 'SAVINGS', 'CASH', 'OTHER'])->default('CHECKING');
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->foreignUuid('user_uid')->references('uid')->on('users')->onDelete('cascade');
            $table->index('user_uid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};
