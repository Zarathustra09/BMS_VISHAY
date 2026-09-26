<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : view('auth.login');
});

// Public registration is disabled — accounts are created by an admin from the Users page.
Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

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

    // Machine Routes
    // Same ordering requirement as buildings.data above.
    Route::get('/machines/data', [App\Http\Controllers\MachineController::class, 'data'])->name('machines.data');
    Route::resource('machines', App\Http\Controllers\MachineController::class);
    Route::get('/machines/{machine}/delete', [App\Http\Controllers\MachineController::class, 'confirmDelete'])->name('machines.confirm-delete');
    Route::get('/machines/{machine}/logs/data', [App\Http\Controllers\MachineController::class, 'logsData'])->name('machines.logs.data');
    Route::get('/machines/{machine}/readings', [App\Http\Controllers\MachineController::class, 'readings'])->name('machines.readings');
    Route::post('/machines/{machine}/device-key', [App\Http\Controllers\MachineController::class, 'generateDeviceKey'])
        ->middleware('permission:machines.view-device-key')
        ->name('machines.device-key.generate');
    Route::put('/machines/{machine}/thresholds', [App\Http\Controllers\MachineController::class, 'updateThresholds'])
        ->name('machines.thresholds.update');

    // Alert Routes
    Route::get('/alerts', [App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/data', [App\Http\Controllers\AlertController::class, 'data'])->name('alerts.data');
    Route::post('/alerts/{alert}/acknowledge', [App\Http\Controllers\AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
    Route::post('/alerts/{alert}/resolve', [App\Http\Controllers\AlertController::class, 'resolve'])->name('alerts.resolve');

    // Notifications (header bell) — thin wrapper over Laravel's own notification API, no
    // domain logic to justify an interface/service for this.
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('/work-orders', [App\Http\Controllers\WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/maintenance', [App\Http\Controllers\MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
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
    Route::post('/roles/{role}/users', [App\Http\Controllers\RoleController::class, 'assignUsers'])->name('roles.assign-users');

    // Permission Routes (read-only)
    Route::get('/permissions', [App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');

    // AI Chat Routes
    Route::get('/ai/questions/{machine}', [App\Http\Controllers\AIController::class, 'getSuggestedQuestions'])->name('ai.questions');
    Route::post('/ai/chat', [App\Http\Controllers\AIController::class, 'chat'])->name('ai.chat');
    Route::post('/ai/check-status', [App\Http\Controllers\AIController::class, 'checkStatus'])->name('ai.check-status');
});

