<?php

use App\Domain\Category\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('categories', CategoryController::class)->parameters(['categories' => 'uid']);
