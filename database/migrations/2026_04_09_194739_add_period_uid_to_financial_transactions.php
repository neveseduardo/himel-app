<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->uuid('period_uid')->nullable()->after('reference_id');
            $table->foreign('period_uid')->references('uid')->on('financial_periods')->onDelete('set null');
            $table->index('period_uid');
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['period_uid']);
            $table->dropIndex(['period_uid']);
            $table->dropColumn('period_uid');
        });
    }
};
