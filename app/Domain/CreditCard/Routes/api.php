<?php

use App\Domain\CreditCard\Controllers\CreditCardController;
use Illuminate\Support\Facades\Route;

Route::apiResource('credit-cards', CreditCardController::class)->parameters(['credit-cards' => 'uid']);
