<?php

use App\Domain\Transfer\Controllers\TransferPageController;
use Illuminate\Support\Facades\Route;

Route::resource('transfers', TransferPageController::class)
    ->parameters(['transfers' => 'uid'])
    ->names('transfers')
    ->except(['show', 'create', 'edit', 'update']);
