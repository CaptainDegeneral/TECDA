<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->role_id === 1) {
            return $next($request);
        }

        abort(JsonResponse::HTTP_FORBIDDEN, __('messages.errors.unauthorized'));
    }
}
