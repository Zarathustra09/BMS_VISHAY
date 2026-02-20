<?php

namespace App\Providers;

use App\Interfaces\AIInterface;
use App\Interfaces\BuildingInterface;
use App\Interfaces\MachineInterface;
use App\Services\BuildingService;
use App\Services\MachineService;
use App\Services\OpenAIService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIInterface::class, OpenAIService::class);
        $this->app->bind(BuildingInterface::class, BuildingService::class);
        $this->app->bind(MachineInterface::class, MachineService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
