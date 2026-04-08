<?php

namespace App\Providers;

use App\Domain\Account\Contracts\AccountServiceInterface;
use App\Domain\Account\Services\AccountService;
use App\Domain\Category\Contracts\CategoryServiceInterface;
use App\Domain\Category\Listeners\CreateDefaultCategoriesListener;
use App\Domain\Category\Services\CategoryService;
use App\Domain\CreditCard\Contracts\CreditCardServiceInterface;
use App\Domain\CreditCard\Services\CreditCardService;
use App\Domain\CreditCardCharge\Contracts\CreditCardChargeServiceInterface;
use App\Domain\CreditCardCharge\Services\CreditCardChargeService;
use App\Domain\CreditCardInstallment\Contracts\CreditCardInstallmentServiceInterface;
use App\Domain\CreditCardInstallment\Services\CreditCardInstallmentService;
use App\Domain\FixedExpense\Contracts\FixedExpenseServiceInterface;
use App\Domain\FixedExpense\Services\FixedExpenseService;
use App\Domain\Period\Contracts\PeriodServiceInterface;
use App\Domain\Period\Services\PeriodService;
use App\Domain\Transaction\Contracts\TransactionServiceInterface;
use App\Domain\Transaction\Services\TransactionService;
use App\Domain\Transfer\Contracts\TransferServiceInterface;
use App\Domain\Transfer\Services\TransferService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerListeners();
    }

    protected function registerServices(): void
    {
        $this->app->singleton(AccountServiceInterface::class, AccountService::class);
        $this->app->singleton(CategoryServiceInterface::class, CategoryService::class);
        $this->app->singleton(CreditCardServiceInterface::class, CreditCardService::class);
        $this->app->singleton(CreditCardChargeServiceInterface::class, CreditCardChargeService::class);
        $this->app->singleton(CreditCardInstallmentServiceInterface::class, CreditCardInstallmentService::class);
        $this->app->singleton(FixedExpenseServiceInterface::class, FixedExpenseService::class);
        $this->app->singleton(PeriodServiceInterface::class, PeriodService::class);
        $this->app->singleton(TransactionServiceInterface::class, TransactionService::class);
        $this->app->singleton(TransferServiceInterface::class, TransferService::class);
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

    protected function registerListeners(): void
    {
        Event::listen(Login::class, CreateDefaultCategoriesListener::class);
    }
}
