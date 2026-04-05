<?php

use App\Domain\Period\Controllers\PeriodController;
use Illuminate\Support\Facades\Route;

Route::apiResource('periods', PeriodController::class)->parameters(['periods' => 'uid']);

Route::get('periods/current', [PeriodController::class, 'current']);
