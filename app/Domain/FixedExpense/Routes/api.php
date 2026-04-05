<?php

use App\Domain\FixedExpense\Controllers\FixedExpenseController;
use Illuminate\Support\Facades\Route;

Route::apiResource('fixed-expenses', FixedExpenseController::class)->parameters(['fixed-expenses' => 'uid']);
