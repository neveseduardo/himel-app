<?php

use App\Domain\Category\Controllers\CategoryPageController;
use Illuminate\Support\Facades\Route;

Route::resource('categories', CategoryPageController::class)
    ->parameters(['categories' => 'uid'])
    ->names('finance.categories')
    ->except(['show']);
