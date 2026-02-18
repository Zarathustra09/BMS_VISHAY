<?php

use App\Http\Controllers\AIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// AI Chat Routes - using web middleware for CSRF protection
Route::prefix('ai')->middleware(['web'])->group(function () {
    Route::post('/chat', [AIController::class, 'chat'])->name('api.ai.chat');
    Route::post('/check-status', [AIController::class, 'checkStatus'])->name('api.ai.check-status');
    Route::get('/questions/{machine}', [AIController::class, 'getSuggestedQuestions'])->name('api.ai.questions');
});
