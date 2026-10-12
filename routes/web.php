<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : view('auth.login');
});

// Public registration is disabled — accounts are created by an admin from the Users page.
Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    // The dashboard's alert counters and recent-alerts list, re-fetched when an alert changes.
    Route::get('/home/alerts-live', [App\Http\Controllers\HomeController::class, 'alertsLive'])
        ->middleware('permission:alerts.view')
        ->name('home.alerts-live');

    // Switchable modules (Buildings & Floors, Work Orders, Maintenance, Reports) are wrapped in
    // `module:{name}` — when a deployment switches one off (config/modules.php) its routes stay
    // registered, so route() calls in shared views never throw, but answer 404.
    Route::middleware('module:buildings')->group(function () {
        // Building Routes
        // /buildings/data must be registered before the resource route below — otherwise
        // Route::resource's GET /buildings/{building} (show) would swallow "data" as the
        // {building} parameter, since routes match in registration order.
        Route::get('/buildings/data', [App\Http\Controllers\BuildingController::class, 'data'])->name('buildings.data');
        Route::resource('buildings', App\Http\Controllers\BuildingController::class);
        Route::get('/buildings/{building}/delete', [App\Http\Controllers\BuildingController::class, 'confirmDelete'])->name('buildings.confirm-delete');

        // Floor Routes
        Route::prefix('floors')->name('floors.')->group(function () {
            Route::get('/', [App\Http\Controllers\FloorController::class, 'index'])->name('floors.index');
            Route::post('/', [App\Http\Controllers\FloorController::class, 'store'])->name('store');
            Route::get('/{floor}', [App\Http\Controllers\FloorController::class, 'show'])->name('show');
            Route::put('/{floor}', [App\Http\Controllers\FloorController::class, 'update'])->name('update');
            Route::delete('/{floor}', [App\Http\Controllers\FloorController::class, 'destroy'])->name('destroy');
        });
    });

    // Machine Routes
    // Same ordering requirement as buildings.data above.
    Route::get('/machines/data', [App\Http\Controllers\MachineController::class, 'data'])->name('machines.data');
    Route::resource('machines', App\Http\Controllers\MachineController::class);
    Route::get('/machines/{machine}/delete', [App\Http\Controllers\MachineController::class, 'confirmDelete'])->name('machines.confirm-delete');
    Route::get('/machines/{machine}/logs/data', [App\Http\Controllers\MachineController::class, 'logsData'])->name('machines.logs.data');
    // The machine page's headline counters (last reading, readings today, log totals, open alerts),
    // re-fetched as readings and alerts arrive.
    Route::get('/machines/{machine}/summary', [App\Http\Controllers\MachineController::class, 'summary'])->name('machines.summary');
    // The machine page's Active Alerts + Alert trail, re-fetched when one of its alerts changes.
    Route::get('/machines/{machine}/alerts-panel', [App\Http\Controllers\MachineController::class, 'alertsPanel'])->name('machines.alerts-panel');
    Route::get('/machines/{machine}/readings', [App\Http\Controllers\MachineController::class, 'readings'])->name('machines.readings');
    Route::get('/machines/{machine}/parameters', [App\Http\Controllers\MachineController::class, 'parameters'])->name('machines.parameters');
    Route::post('/machines/{machine}/device-key', [App\Http\Controllers\MachineController::class, 'generateDeviceKey'])
        ->middleware(['permission:machines.view-device-key', 'permission:machines.update'])
        ->name('machines.device-key.generate');

    // Machine Type Routes
    // A distinct /machine-types prefix, not /machines/types — the Machines sidebar item
    // matches request()->is('machines*'), which would also (wrongly) light up on the latter.
    Route::resource('machine-types', App\Http\Controllers\MachineTypeController::class)
        ->except('show')
        ->parameters(['machine-types' => 'machineType']);
    Route::get('/machine-types/{machineType}/delete', [App\Http\Controllers\MachineTypeController::class, 'confirmDelete'])
        ->name('machine-types.confirm-delete');

    // Alert Routes
    Route::get('/alerts', [App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/data', [App\Http\Controllers\AlertController::class, 'data'])->name('alerts.data');
    // Live counters for the Alerts page (re-fetched whenever an alert changes), and one alert's trail.
    // /alerts/stats must stay above /alerts/{alert}, or "stats" would be taken as an alert id.
    Route::get('/alerts/stats', [App\Http\Controllers\AlertController::class, 'stats'])->name('alerts.stats');
    Route::get('/alerts/{alert}', [App\Http\Controllers\AlertController::class, 'show'])->name('alerts.show');
    Route::post('/alerts/{alert}/acknowledge', [App\Http\Controllers\AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
    Route::post('/alerts/{alert}/resolve', [App\Http\Controllers\AlertController::class, 'resolve'])->name('alerts.resolve');

    // Notifications (header bell) — thin wrapper over Laravel's own notification API, no
    // domain logic to justify an interface/service for this.
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Work Order Routes
    // Same ordering requirement as buildings.data above.
    Route::middleware('module:work-orders')->group(function () {
        Route::get('/work-orders/data', [App\Http\Controllers\WorkOrderController::class, 'data'])->name('work-orders.data');
        Route::resource('work-orders', App\Http\Controllers\WorkOrderController::class)
            ->parameters(['work-orders' => 'workOrder']);
        Route::get('/work-orders/{workOrder}/delete', [App\Http\Controllers\WorkOrderController::class, 'confirmDelete'])->name('work-orders.confirm-delete');
        Route::patch('/work-orders/{workOrder}/status', [App\Http\Controllers\WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
    });

    Route::middleware('module:reports')->group(function () {
        Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('module:maintenance')->group(function () {
        Route::get('/maintenance', [App\Http\Controllers\MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::get('/maintenance/data', [App\Http\Controllers\MaintenanceController::class, 'data'])->name('maintenance.data');
    });

    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-email', [App\Http\Controllers\SettingController::class, 'testEmail'])->name('settings.test-email');
    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // User Management Routes
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('/users/data', [App\Http\Controllers\UserController::class, 'data'])->name('users.data');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/password', [App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

    // Role Routes
    Route::resource('roles', App\Http\Controllers\RoleController::class);
    Route::get('/roles/{role}/delete', [App\Http\Controllers\RoleController::class, 'confirmDelete'])->name('roles.confirm-delete');
    // Moving users between roles changes their accounts, so it needs users.update on top of the
    // controller's roles.update.
    Route::post('/roles/{role}/users', [App\Http\Controllers\RoleController::class, 'assignUsers'])
        ->middleware('permission:users.update')
        ->name('roles.assign-users');

    // Group Routes - a group holds roles/permissions that its members inherit, and can be a
    // machine's assignment group (alert emails go to every member).
    Route::resource('groups', App\Http\Controllers\GroupController::class);
    Route::get('/groups/{group}/delete', [App\Http\Controllers\GroupController::class, 'confirmDelete'])->name('groups.confirm-delete');

    // Permission Routes (read-only)
    Route::get('/permissions', [App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');

    // AI Chat Routes
    Route::get('/ai/questions/{machine}', [App\Http\Controllers\AIController::class, 'getSuggestedQuestions'])->name('ai.questions');
    Route::post('/ai/chat', [App\Http\Controllers\AIController::class, 'chat'])->middleware('throttle:20,1')->name('ai.chat');
    Route::post('/ai/check-status', [App\Http\Controllers\AIController::class, 'checkStatus'])->name('ai.check-status');
});
