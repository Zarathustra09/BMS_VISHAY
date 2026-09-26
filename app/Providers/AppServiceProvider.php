<?php

namespace App\Providers;

use App\Interfaces\AIInterface;
use App\Interfaces\BuildingInterface;
use App\Interfaces\FloorInterface;
use App\Interfaces\MachineInterface;
use App\Interfaces\MachineLogInterface;
use App\Interfaces\PermissionInterface;
use App\Interfaces\RoleInterface;
use App\Interfaces\SuggestedQuestionInterface;
use App\Interfaces\UserInterface;
use App\Services\BuildingService;
use App\Services\FloorService;
use App\Services\GeminiService;
use App\Services\MachineLogService;
use App\Services\MachineService;
use App\Services\OpenAIService;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Services\SuggestedQuestionService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIInterface::class, function ($app) {
            return match (config('services.ai.provider', 'openai')) {
                'gemini' => $app->make(GeminiService::class),
                default => $app->make(OpenAIService::class),
            };
        });

        $this->app->bind(BuildingInterface::class, BuildingService::class);
        $this->app->bind(FloorInterface::class, FloorService::class);
        $this->app->bind(MachineInterface::class, MachineService::class);
        $this->app->bind(MachineLogInterface::class, MachineLogService::class);
        $this->app->bind(SuggestedQuestionInterface::class, SuggestedQuestionService::class);
        $this->app->bind(UserInterface::class, UserService::class);
        $this->app->bind(RoleInterface::class, RoleService::class);
        $this->app->bind(PermissionInterface::class, PermissionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
