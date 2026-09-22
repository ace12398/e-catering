<?php
namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Services\AdaptiveEngine;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && $request->isMethod('GET') && !$request->ajax()) {
            try {
                $routeName = $request->route()?->getName() ?? $request->path();
                ActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'page_visit',
                    'entity_type' => $routeName,
                    'entity_id' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'performed_at' => now(),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('TrackUserActivity failed', ['error' => $e->getMessage()]);
            }
        }

        return $response;
    }
}
