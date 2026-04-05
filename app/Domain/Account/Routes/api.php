<?php

use App\Domain\Account\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::apiResource('accounts', AccountController::class)->parameters(['accounts' => 'uid']);
