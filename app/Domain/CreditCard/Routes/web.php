<?php

use App\Domain\CreditCard\Controllers\CreditCardPageController;
use Illuminate\Support\Facades\Route;

Route::resource('credit-cards', CreditCardPageController::class)
    ->parameters(['credit-cards' => 'uid'])
    ->names('credit-cards')
    ->except(['show', 'create', 'edit']);
