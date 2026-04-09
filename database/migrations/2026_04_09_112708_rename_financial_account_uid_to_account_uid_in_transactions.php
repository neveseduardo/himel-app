<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->renameColumn('financial_account_uid', 'account_uid');
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->renameColumn('financial_category_uid', 'category_uid');
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->renameColumn('account_uid', 'financial_account_uid');
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->renameColumn('category_uid', 'financial_category_uid');
        });
    }
};
