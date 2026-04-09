<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::inertia('/', 'finance/Index')->name('index');

        require base_path('app/Domain/Account/Routes/web.php');
        require base_path('app/Domain/Category/Routes/web.php');
        require base_path('app/Domain/Transaction/Routes/web.php');
        require base_path('app/Domain/Transfer/Routes/web.php');
        require base_path('app/Domain/FixedExpense/Routes/web.php');
        require base_path('app/Domain/CreditCard/Routes/web.php');
        require base_path('app/Domain/CreditCardCharge/Routes/web.php');
        require base_path('app/Domain/Period/Routes/web.php');
    });
    require base_path('app/Domain/Settings/Routes/web.php');
});
