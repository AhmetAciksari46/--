<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subscription;

class EnsureUserHasActivePackage
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $subscription = Subscription::activeForUser($user);

        if (!$subscription) {
            return response()->json([
                'message' => 'Bu işlemi gerçekleştirmek için aktif bir paketiniz olmalıdır.'
            ], 403);
        }

        return $next($request);
    }
}
