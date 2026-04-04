<?php

namespace App\Providers;

use App\Services\AccountService;
use App\Services\CategoryService;
use App\Services\CreditCardService;
use App\Services\FixedExpenseService;
use App\Services\InstallmentService;
use App\Services\Interfaces\IAccountService;
use App\Services\Interfaces\ICategoryService;
use App\Services\Interfaces\ICreditCardService;
use App\Services\Interfaces\IFixedExpenseService;
use App\Services\Interfaces\IInstallmentService;
use App\Services\Interfaces\IPeriodService;
use App\Services\Interfaces\ITransactionService;
use App\Services\Interfaces\ITransferService;
use App\Services\PeriodService;
use App\Services\TransactionService;
use App\Services\TransferService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IAccountService::class, AccountService::class);
        $this->app->bind(ICategoryService::class, CategoryService::class);
        $this->app->bind(ICreditCardService::class, CreditCardService::class);
        $this->app->bind(IFixedExpenseService::class, FixedExpenseService::class);
        $this->app->bind(IInstallmentService::class, InstallmentService::class);
        $this->app->bind(IPeriodService::class, PeriodService::class);
        $this->app->bind(ITransactionService::class, TransactionService::class);
        $this->app->bind(ITransferService::class, TransferService::class);
    }

    public function boot(): void
    {
        $this->configureDefaults();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
