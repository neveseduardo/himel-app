<?php

use Illuminate\Support\Facades\Route;

require base_path('app/Domain/Auth/Routes/api.php');

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    require base_path('app/Domain/Account/Routes/api.php');
    require base_path('app/Domain/Category/Routes/api.php');
    require base_path('app/Domain/Transaction/Routes/api.php');
    require base_path('app/Domain/Transfer/Routes/api.php');
    require base_path('app/Domain/FixedExpense/Routes/api.php');
    require base_path('app/Domain/CreditCard/Routes/api.php');
    require base_path('app/Domain/CreditCardCharge/Routes/api.php');
    require base_path('app/Domain/CreditCardInstallment/Routes/api.php');
    require base_path('app/Domain/Period/Routes/api.php');
});
