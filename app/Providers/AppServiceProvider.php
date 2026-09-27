<?php

namespace App\Providers;

use App\Interfaces\AIInterface;
use App\Interfaces\AlertInterface;
use App\Interfaces\BuildingInterface;
use App\Interfaces\DashboardInterface;
use App\Interfaces\FloorInterface;
use App\Interfaces\MachineInterface;
use App\Interfaces\MachineLogInterface;
use App\Interfaces\MaintenanceInterface;
use App\Interfaces\PermissionInterface;
use App\Interfaces\ReportInterface;
use App\Interfaces\RoleInterface;
use App\Interfaces\SettingInterface;
use App\Interfaces\SuggestedQuestionInterface;
use App\Interfaces\UserInterface;
use App\Interfaces\WorkOrderInterface;
use App\Services\AlertService;
use App\Services\BuildingService;
use App\Services\DashboardService;
use App\Services\FloorService;
use App\Services\GeminiService;
use App\Services\MachineLogService;
use App\Services\MachineService;
use App\Services\MaintenanceService;
use App\Services\OpenAIService;
use App\Services\PermissionService;
use App\Services\ReportService;
use App\Services\RoleService;
use App\Services\SettingService;
use App\Services\SuggestedQuestionService;
use App\Services\UserService;
use App\Services\WorkOrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        $this->app->bind(AlertInterface::class, AlertService::class);
        $this->app->bind(WorkOrderInterface::class, WorkOrderService::class);
        $this->app->bind(MaintenanceInterface::class, MaintenanceService::class);
        $this->app->bind(DashboardInterface::class, DashboardService::class);
        $this->app->bind(ReportInterface::class, ReportService::class);
        $this->app->bind(SettingInterface::class, SettingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Shares the header bell's unread count/list on every authenticated page — a query
        // belongs here, not in a @php block in the layout (see docs/02-architecture.md
        // anti-patterns). Both views are always rendered together (header is @included
        // from app.blade.php), but each gets an explicit share since a composer only fires
        // for the exact view name it's registered against.
        View::composer(['layouts.app', 'layouts.header'], function ($view) {
            $user = Auth::user();

            $view->with('unreadAlertCount', $user ? $user->unreadNotifications()->count() : 0);
            $view->with('recentAlertNotifications', $user ? $user->notifications()->latest()->limit(5)->get() : collect());
        });

        // The sidebar's brand text was hardcoded ("BMS VISHAY") before Phase 10's Settings
        // page made company_name a real, editable value — shared the same way as the bell
        // data above, since the sidebar isn't behind any one controller action either.
        View::composer('layouts.app', function ($view) {
            $view->with('companyName', $this->app->make(SettingInterface::class)->get('company_name'));
        });
    }
}
