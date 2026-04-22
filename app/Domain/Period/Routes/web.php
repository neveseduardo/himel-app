<?php

use App\Domain\Period\Controllers\PeriodPageController;
use Illuminate\Support\Facades\Route;

Route::resource('periods', PeriodPageController::class)
    ->names('periods')
    ->only(['index', 'store', 'show', 'destroy'])
    ->parameters(['periods' => 'uid']);

Route::post('periods/{uid}/initialize', [PeriodPageController::class, 'initialize'])
    ->name('periods.initialize');

Route::post('periods/{uid}/transactions', [PeriodPageController::class, 'storeTransaction'])
    ->name('periods.transactions.store');

Route::delete('periods/{uid}/transactions', [PeriodPageController::class, 'detachTransactions'])
    ->name('periods.transactions.detach');
