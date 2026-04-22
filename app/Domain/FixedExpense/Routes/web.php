<?php

use App\Domain\FixedExpense\Controllers\FixedExpensePageController;
use Illuminate\Support\Facades\Route;

Route::resource('fixed-expenses', FixedExpensePageController::class)
    ->parameters(['fixed-expenses' => 'uid'])
    ->names('fixed-expenses')
    ->except(['show', 'create', 'edit']);
