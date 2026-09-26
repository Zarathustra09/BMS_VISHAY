<?php

use App\Http\Controllers\API\MachineLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Note: AI chat is served from routes/web.php (ai.chat, ai.check-status, ai.questions) —
// it needs the web middleware group (session auth + CSRF) since it's called from a Blade view,
// so it doesn't belong in this stateless api.php file.

// Hardware sensor ingestion — authenticated via per-machine X-Device-Key header, not user sessions.
Route::prefix('machine-logs')->middleware(['device.auth', 'throttle:120,1'])->group(function () {
    Route::post('/', [MachineLogController::class, 'store'])->name('api.machine-logs.store');
});
