<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_card_installments', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->uuid('credit_card_charge_uid');
            $table->uuid('transaction_uid')->nullable();
            $table->tinyInteger('installment_number');
            $table->dateTime('due_date');
            $table->decimal('amount', 15, 2);
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('credit_card_charge_uid', 'fk_cc_charge_uid')->references('uid')->on('credit_card_charges')->onDelete('cascade');
            $table->foreign('transaction_uid', 'fk_tx_uid')->references('uid')->on('transactions')->onDelete('set null');
            $table->index('credit_card_charge_uid', 'idx_charge_uid');
            $table->index('transaction_uid', 'idx_tx_uid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_card_installments');
    }
};
