<?php

use App\Domain\CreditCardCharge\Controllers\CreditCardChargePageController;
use Illuminate\Support\Facades\Route;

Route::resource('credit-card-charges', CreditCardChargePageController::class)
    ->parameters(['credit-card-charges' => 'uid'])
    ->names('finance.credit-card-charges')
    ->except(['show', 'create', 'edit', 'update', 'destroy']);
