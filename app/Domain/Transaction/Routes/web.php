<?php

use App\Domain\Transaction\Controllers\TransactionPageController;
use Illuminate\Support\Facades\Route;

Route::resource('transactions', TransactionPageController::class)
    ->parameters(['transactions' => 'uid'])
    ->names('transactions')
    ->except(['show', 'create', 'edit']);
