<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/buildings', [App\Http\Controllers\BuildingController::class, 'index'])->name('buildings.index');
//Route::get('/tenants', [App\Http\Controllers\TenantController::class, 'index'])->name('tenants.index');
Route::get('/machines', [App\Http\Controllers\MachineController::class, 'index'])->name('machines.index');
Route::get('/machines/{machine}', [App\Http\Controllers\MachineController::class, 'show'])->name('machines.show');
Route::get('/work-orders', [App\Http\Controllers\WorkOrderController::class, 'index'])->name('work-orders.index');
Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
Route::get('/maintenance', [App\Http\Controllers\MaintenanceController::class, 'index'])->name('maintenance.index');
Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');

// AI Chat Routes
Route::get('/ai/questions/{machine}', [App\Http\Controllers\AIController::class, 'getInsightQuestions'])->name('ai.questions');
Route::post('/ai/chat', [App\Http\Controllers\AIController::class, 'chat'])->name('ai.chat');
Route::post('/ai/check-status', [App\Http\Controllers\AIController::class, 'checkStatus'])->name('ai.check-status');
Route::post('/ai/quick-insight/{machine}', [App\Http\Controllers\AIController::class, 'quickInsight'])->name('ai.quick-insight');
