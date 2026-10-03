<?php

namespace App\Http\Middleware;

use App\Enums\AppModule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `module:{name}` route middleware — a switched-off module's routes stay registered (so
 * `route('buildings.show', …)` in a shared view never throws RouteNotFoundException) but
 * answer 404, as if the feature didn't exist in this edition. See config/modules.php.
 */
class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $appModule = AppModule::tryFrom($module);

        abort_if($appModule !== null && ! $appModule->isEnabled(), 404);

        return $next($request);
    }
}
