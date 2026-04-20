<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_credit_cards', function (Blueprint $table) {
            $table->tinyInteger('closing_day')->nullable()->after('due_day');
            $table->string('last_four_digits', 4)->nullable()->after('closing_day');
        });
    }

    public function down(): void
    {
        Schema::table('financial_credit_cards', function (Blueprint $table) {
            $table->dropColumn(['closing_day', 'last_four_digits']);
        });
    }
};
