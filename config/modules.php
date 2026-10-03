<?php

/*
|--------------------------------------------------------------------------
| Editions & module toggles
|--------------------------------------------------------------------------
|
| One codebase, two deployments: APP_EDITION=full (the default — every module on) or
| APP_EDITION=lite (machines, machine types, alerts, users/roles and the sensor API only).
| Any single module can still be forced on or off independently with MODULE_<NAME>, e.g.
| a lite install that also wants Buildings: APP_EDITION=lite + MODULE_BUILDINGS=true.
|
| Only modules that can be switched off are listed — see App\Enums\AppModule, which also maps
| each one to the permission prefixes it owns. Nothing here touches the database: every
| table stays, so switching a module back on later just works.
|
*/

$isFull = env('APP_EDITION', 'full') === 'full';

return [

    'edition' => env('APP_EDITION', 'full'),

    'buildings' => (bool) env('MODULE_BUILDINGS', $isFull),
    'work-orders' => (bool) env('MODULE_WORK_ORDERS', $isFull),
    'maintenance' => (bool) env('MODULE_MAINTENANCE', $isFull),
    'reports' => (bool) env('MODULE_REPORTS', $isFull),

];
