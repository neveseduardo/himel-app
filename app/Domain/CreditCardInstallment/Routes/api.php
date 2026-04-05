<?php

use App\Domain\CreditCardInstallment\Controllers\CreditCardInstallmentController;
use Illuminate\Support\Facades\Route;

Route::apiResource('credit-card-installments', CreditCardInstallmentController::class)->parameters(['credit-card-installments' => 'uid']);

Route::patch('credit-card-installments/{uid}/mark-as-paid', [CreditCardInstallmentController::class, 'markAsPaid']);
