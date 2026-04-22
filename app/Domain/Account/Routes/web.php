<?php

use App\Domain\Account\Controllers\AccountPageController;
use Illuminate\Support\Facades\Route;

Route::resource('accounts', AccountPageController::class)
    ->parameters(['accounts' => 'uid'])
    ->names('accounts')
    ->except(['show', 'create', 'edit']);
