<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pushes an event to browsers over Laravel Reverb without ever letting Reverb be the reason
 * something else fails.
 *
 * Reverb is strictly an add-on: a stopped or unreachable server must never turn a device's
 * already-stored reading, or a user's Acknowledge click, into a 500 - the pages just fall back to
 * polling/reloading. A failure also opens a short circuit breaker (DOWN_KEY) so the next events
 * skip the attempt instead of each waiting out the connect timeout, and logs one line rather
 * than a stack trace per event. Shared by live readings and live alerts so one outage trips both.
 */
class SafeBroadcaster
{
    /**
     * Set for PAUSE_SECONDS after a failed broadcast, so a stopped Reverb costs one timeout
     * instead of one per event.
     */
    public const DOWN_KEY = 'live-broadcast-down';

    public const PAUSE_SECONDS = 30;

    public function dispatch(object $event): void
    {
        if (Cache::has(self::DOWN_KEY)) {
            return;
        }

        rescue(fn () => event($event), function (Throwable $e) use ($event) {
            Cache::put(self::DOWN_KEY, true, self::PAUSE_SECONDS);

            Log::warning('Live broadcast of '.class_basename($event).' failed (is Reverb running?); data is still stored and pages fall back to polling. '.$e->getMessage());
        }, report: false);
    }
}
