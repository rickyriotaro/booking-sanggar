<?php

namespace App\Providers;

use App\Models\Costume;
use App\Models\MakeupService;
use App\Models\DanceService;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Observers\CostumeObserver;
use App\Observers\MakeupServiceObserver;
use App\Observers\DanceServiceObserver;
use App\Observers\OrderObserver;
use App\Observers\OrderDetailObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers untuk auto-sync stock snapshot
        Costume::observe(CostumeObserver::class);
        MakeupService::observe(MakeupServiceObserver::class);
        DanceService::observe(DanceServiceObserver::class);
        Order::observe(OrderObserver::class);
        OrderDetail::observe(OrderDetailObserver::class);
    }
}
