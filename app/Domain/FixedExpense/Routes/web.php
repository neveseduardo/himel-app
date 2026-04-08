<?php

use App\Domain\FixedExpense\Controllers\FixedExpensePageController;
use Illuminate\Support\Facades\Route;

Route::resource('fixed-expenses', FixedExpensePageController::class)
    ->parameters(['fixed-expenses' => 'uid'])
    ->names('finance.fixed-expenses')
    ->except(['show', 'create', 'edit']);
