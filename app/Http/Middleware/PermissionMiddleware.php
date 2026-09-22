<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->can($permission) && ! $request->user()->isAdmin()) {
            abort(403, 'Permission denied for action: ' . $permission);
        }

        return $next($request);
    }
}
