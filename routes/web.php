<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('home') : view('auth.login');
});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // Building Routes
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
    Route::resource('machines', App\Http\Controllers\MachineController::class);
    Route::get('/machines/{machine}/delete', [App\Http\Controllers\MachineController::class, 'confirmDelete'])->name('machines.confirm-delete');
    Route::post('/machines/{machine}/device-key', [App\Http\Controllers\MachineController::class, 'generateDeviceKey'])
        ->middleware('permission:machines.view-device-key')
        ->name('machines.device-key.generate');

    Route::get('/work-orders', [App\Http\Controllers\WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/maintenance', [App\Http\Controllers\MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');

    // AI Chat Routes
    Route::get('/ai/questions/{machine}', [App\Http\Controllers\AIController::class, 'getSuggestedQuestions'])->name('ai.questions');
    Route::post('/ai/chat', [App\Http\Controllers\AIController::class, 'chat'])->name('ai.chat');
    Route::post('/ai/check-status', [App\Http\Controllers\AIController::class, 'checkStatus'])->name('ai.check-status');
});

