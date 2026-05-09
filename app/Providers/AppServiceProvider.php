<?php

namespace App\Providers;

use App\Enums\Role;
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
        \Illuminate\Support\Facades\Gate::before(function ($user) {
            if ($user->hasRole(Role::IT->value)) {
                return true;
            }
        });

        if (config('app.env') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        ContractPaymentSchedule::observe(ContractPaymentScheduleObserver::class);

        \Illuminate\Support\Facades\Event::listen(
            [
                \Illuminate\Auth\Events\Login::class,
                \Illuminate\Auth\Events\Logout::class,
                \Illuminate\Auth\Events\Failed::class,
            ],
            [\App\Listeners\LogAuthActivity::class, 'handle']
        );

        \Opcodes\LogViewer\Facades\LogViewer::auth(function ($request) {
            return $request->user() && $request->user()->hasRole('it');
        });
    }
}
