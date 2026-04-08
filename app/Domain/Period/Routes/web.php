<?php

use App\Domain\Period\Controllers\PeriodPageController;
use Illuminate\Support\Facades\Route;

Route::resource('periods', PeriodPageController::class)
    ->names('finance.periods')
    ->only(['index']);
