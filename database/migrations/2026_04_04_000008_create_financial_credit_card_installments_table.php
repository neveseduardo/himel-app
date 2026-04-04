<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_credit_card_installments', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->uuid('credit_card_charge_uid');
            $table->uuid('financial_transaction_uid')->nullable();
            $table->tinyInteger('installment_number');
            $table->dateTime('due_date');
            $table->decimal('amount', 15, 2);
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('credit_card_charge_uid', 'fk_cc_charge_uid')->references('uid')->on('financial_credit_card_charges')->onDelete('cascade');
            $table->foreign('financial_transaction_uid', 'fk_tx_uid')->references('uid')->on('financial_transactions')->onDelete('set null');
            $table->index('credit_card_charge_uid');
            $table->index('financial_transaction_uid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_credit_card_installments');
    }
};
