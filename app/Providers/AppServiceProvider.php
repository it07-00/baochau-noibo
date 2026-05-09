<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractPaymentSchedule;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Observers\ContractPaymentScheduleObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
            'waste'          => ContractWaste::class,
            'consulting'     => ContractLegal::class,
            'project'        => ContractTechnical::class,
            'commercial'     => ContractResearch::class,
            'sustainability' => ContractSustainability::class,
            'energy'         => ContractEmission::class,
        ]);

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
