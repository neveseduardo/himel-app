<?php

use App\Domain\CreditCardCharge\Controllers\CreditCardChargeController;
use Illuminate\Support\Facades\Route;

Route::apiResource('credit-card-charges', CreditCardChargeController::class)->parameters(['credit-card-charges' => 'uid']);
