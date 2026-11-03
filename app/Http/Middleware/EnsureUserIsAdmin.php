<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'You are not authorized to perform this action.',
                'errors' => [
                    'authorization' => [
                        'Only administrators can perform this action.',
                    ],
                ],
            ], 403);
        }

        return $next($request);
    }
}