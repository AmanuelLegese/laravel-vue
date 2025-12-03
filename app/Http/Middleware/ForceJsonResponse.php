<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        Response::macro('forceJson', function ($data = [], $status = 200) {
            return response()->json($data, $status);
        });
        // Force JSON response for API routes
        if ($request->is('api/*') && !$response->headers->has('Content-Type')) {
            return response()->json($response->getData(), $response->getStatusCode());
        }

        return $response;
    }
}
