<?php

use App\Http\Controllers\AIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// AI Chat Routes
Route::prefix('ai')->group(function () {
    Route::post('/chat', [AIController::class, 'chat'])->name('ai.chat');
    Route::post('/check-status', [AIController::class, 'checkStatus'])->name('ai.check-status');
    Route::get('/questions/{machine}', [AIController::class, 'getSuggestedQuestions'])->name('ai.questions');
});
