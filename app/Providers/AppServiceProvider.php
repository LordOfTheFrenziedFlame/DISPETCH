<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Components\SaveMedia;
use App\Models\Measurement;
use App\Observers\MeasurementObserver;
use App\Models\Contract;
use App\Observers\ContractObserver;
use App\Models\Documentation;
use App\Observers\DocumentationObserver;
use App\Models\Installation;
use App\Observers\InstallationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SaveMedia::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Measurement::observe(MeasurementObserver::class);
        Contract::observe(ContractObserver::class);
        Documentation::observe(DocumentationObserver::class);
        Installation::observe(InstallationObserver::class);
    }
}
