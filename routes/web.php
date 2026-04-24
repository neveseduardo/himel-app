<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    require base_path('app/Domain/Dashboard/Routes/web.php');
    require base_path('app/Domain/Account/Routes/web.php');
    require base_path('app/Domain/Category/Routes/web.php');
    require base_path('app/Domain/Transaction/Routes/web.php');
    require base_path('app/Domain/Transfer/Routes/web.php');
    require base_path('app/Domain/FixedExpense/Routes/web.php');
    require base_path('app/Domain/CreditCard/Routes/web.php');
    require base_path('app/Domain/CreditCardCharge/Routes/web.php');
    require base_path('app/Domain/Period/Routes/web.php');
    require base_path('app/Domain/Settings/Routes/web.php');
});
