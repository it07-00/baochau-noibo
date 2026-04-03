<?php

namespace App\Providers;

use App\Models\ContractPaymentSchedule;
use App\Observers\ContractPaymentScheduleObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        ContractPaymentSchedule::observe(ContractPaymentScheduleObserver::class);
    }
}
