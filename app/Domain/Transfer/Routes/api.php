<?php

use App\Domain\Transfer\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::apiResource('transfers', TransferController::class)->parameters(['transfers' => 'uid']);
