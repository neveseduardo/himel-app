<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_card_charges', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->uuid('credit_card_uid');
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->tinyInteger('total_installments');
            $table->date('purchase_date')->nullable();
            $table->timestamps();

            $table->foreign('credit_card_uid')->references('uid')->on('credit_cards')->onDelete('cascade');
            $table->index('credit_card_uid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_card_charges');
    }
};
