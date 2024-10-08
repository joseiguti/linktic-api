<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateProductsParametersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $requiredParams = ['name', 'price', 'description', 'stock'];

        foreach ($requiredParams as $param) {
            if (!$request->has($param)) {
                return response()->json(['error' => "The parameter $param is required"], 400);
            }
        }

        return $next($request);
    }
}
