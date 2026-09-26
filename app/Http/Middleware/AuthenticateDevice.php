<?php

namespace App\Http\Middleware;

use App\Models\Machine;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDevice
{
    /**
     * Authenticate a hardware device via its per-machine API key (X-Device-Key header)
     * and bind the resolved machine onto the request for the controller to use.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Device-Key');

        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'Missing X-Device-Key header.',
            ], 401);
        }

        $machine = Machine::findByDeviceKey($key);

        if (!$machine) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid device key.',
            ], 401);
        }

        $request->attributes->set('device_machine', $machine);

        return $next($request);
    }
}
