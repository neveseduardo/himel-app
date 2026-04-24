<?php

use App\Domain\Dashboard\Controllers\DashboardPageController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', DashboardPageController::class)->name('dashboard');
