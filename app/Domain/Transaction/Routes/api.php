<?php

use App\Domain\Transaction\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('transactions', TransactionController::class)->parameters(['transactions' => 'uid']);
