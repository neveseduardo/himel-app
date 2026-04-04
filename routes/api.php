<?php

use App\Http\Api\Controllers\ApiAuthController;
use App\Http\Api\Controllers\FinancialAccountController;
use App\Http\Api\Controllers\FinancialCategoryController;
use App\Http\Api\Controllers\FinancialCreditCardChargeController;
use App\Http\Api\Controllers\FinancialCreditCardController;
use App\Http\Api\Controllers\FinancialCreditCardInstallmentController;
use App\Http\Api\Controllers\FinancialFixedExpenseController;
use App\Http\Api\Controllers\FinancialPeriodController;
use App\Http\Api\Controllers\FinancialTransactionController;
use App\Http\Api\Controllers\FinancialTransferController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [ApiAuthController::class, 'login']);
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [ApiAuthController::class, 'logout']);
    Route::get('/auth/me', [ApiAuthController::class, 'me']);

    Route::apiResource('accounts', FinancialAccountController::class)->parameters(['accounts' => 'uid']);
    Route::apiResource('categories', FinancialCategoryController::class)->parameters(['categories' => 'uid']);
    Route::apiResource('transactions', FinancialTransactionController::class)->parameters(['transactions' => 'uid']);
    Route::apiResource('transfers', FinancialTransferController::class)->parameters(['transfers' => 'uid']);
    Route::apiResource('fixed-expenses', FinancialFixedExpenseController::class)->parameters(['fixed-expenses' => 'uid']);
    Route::apiResource('credit-cards', FinancialCreditCardController::class)->parameters(['credit-cards' => 'uid']);
    Route::apiResource('credit-card-charges', FinancialCreditCardChargeController::class)->parameters(['credit-card-charges' => 'uid']);
    Route::apiResource('credit-card-installments', FinancialCreditCardInstallmentController::class)->parameters(['credit-card-installments' => 'uid']);
    Route::apiResource('periods', FinancialPeriodController::class)->parameters(['periods' => 'uid']);

    Route::patch('credit-card-installments/{uid}/mark-as-paid', [FinancialCreditCardInstallmentController::class, 'markAsPaid']);
    Route::get('periods/current', [FinancialPeriodController::class, 'current']);
});
