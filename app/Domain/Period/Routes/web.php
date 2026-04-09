<?php

use App\Domain\Period\Controllers\PeriodPageController;
use Illuminate\Support\Facades\Route;

Route::resource('periods', PeriodPageController::class)
    ->names('finance.periods')
    ->only(['index', 'store', 'show', 'destroy'])
    ->parameters(['periods' => 'uid']);

Route::post('periods/{uid}/initialize', [PeriodPageController::class, 'initialize'])
    ->name('finance.periods.initialize');
