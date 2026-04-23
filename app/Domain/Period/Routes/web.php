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

Route::put('periods/{uid}/transactions/{transactionUid}', [PeriodPageController::class, 'updateTransaction'])
    ->name('periods.transactions.update');

Route::delete('periods/{uid}/transactions/{transactionUid}', [PeriodPageController::class, 'destroyTransaction'])
    ->name('periods.transactions.destroy');

Route::delete('periods/{uid}/transactions', [PeriodPageController::class, 'detachTransactions'])
    ->name('periods.transactions.detach');

Route::get('periods/{uid}/report', [PeriodPageController::class, 'report'])
    ->name('periods.report');
