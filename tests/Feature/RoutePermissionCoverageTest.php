<?php

namespace Tests\Feature;

use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Routing\Router;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Tests\TestCase;

/**
 * Safety net for "every endpoint is protected by the right permission": any route behind
 * authentication must also carry a permission: middleware (route-level or from the controller's
 * constructor), unless it is deliberately open to every signed-in user and listed below.
 * A method added to a controller without being added to its `only([...])` guard fails here.
 */
class RoutePermissionCoverageTest extends TestCase
{
    /** Route names that any authenticated user may reach on purpose. */
    private const OPEN_TO_ANY_SIGNED_IN_USER = [
        'home',                    // the dashboard; its cards check permissions themselves
        'profile.index',
        'profile.update',
        'profile.update-password',
        'notifications.read',      // act only on the user's own notifications
        'notifications.read-all',
        'logout',                  // Laravel UI auth endpoints
        'password.confirm',
    ];

    public function test_every_authenticated_route_requires_a_permission(): void
    {
        $router = app(Router::class);
        $unguarded = [];

        foreach ($router->getRoutes() as $route) {
            /** @var LaravelRoute $route */
            $middleware = $router->resolveMiddleware($route->gatherMiddleware(), $route->excludedMiddleware());

            $requiresLogin = collect($middleware)->contains(
                fn ($m) => is_string($m) && str_starts_with($m, 'Illuminate\Auth\Middleware\Authenticate')
            );

            if (! $requiresLogin) {
                continue;
            }

            $hasPermission = collect($middleware)->contains(
                fn ($m) => is_string($m) && str_starts_with($m, PermissionMiddleware::class)
            );

            if ($hasPermission || in_array($route->getName(), self::OPEN_TO_ANY_SIGNED_IN_USER, true)) {
                continue;
            }

            // Framework/vendor endpoints guarded by their own mechanism (sanctum token, broadcasting).
            if (in_array($route->uri(), ['api/user', 'broadcasting/auth', 'password/confirm'], true)) {
                continue;
            }

            $unguarded[] = implode('|', $route->methods()).' /'.$route->uri().' ('.($route->getName() ?? $route->getActionName()).')';
        }

        $this->assertSame([], $unguarded, "Routes behind login with no permission guard:\n".implode("\n", $unguarded));
    }
}
