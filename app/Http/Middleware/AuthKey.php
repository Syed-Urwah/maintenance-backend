<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Api-Token');
        if($token != 'A4yRjSS3F4fcZ3eq69rLShvgwnjchQg7Vmt5N753Sy'){

            return response()->json([

                'response' => [
                    'response_id' => 1,
                    'response_status' => 401,
                    'response_desc' => 'App key not found'
                ],
            ], 401);
        }
        return $next($request);
    }
}
