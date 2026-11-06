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
// Throttled per device (see AppServiceProvider's `device-api` limiter), sized for a sensor that
// posts every 100 ms.
Route::prefix('machine-logs')->middleware(['device.auth', 'throttle:device-api'])->group(function () {
    Route::post('/', [MachineLogController::class, 'store'])->name('api.machine-logs.store');
});
