<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BasicAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $authorization = $request->header('Authorization');

        if (!$authorization || strpos($authorization, 'Basic ') !== 0) {
            return response()->json(
                ['error' => 'Unauthorized'], 401
            );
        }

        list($username, $password) = explode(':', base64_decode(substr($authorization, 6)));

        $envUsername = env('BASIC_AUTH_USER');
        $envPassword = env('BASIC_AUTH_PASSWD');

        if ($username !== $envUsername || $password !== $envPassword) {
            return response()->json(
                ['error' => 'Unauthorized'], 401
            );
        }

        return $next($request);
    }
}
