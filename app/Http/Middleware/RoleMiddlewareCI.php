<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddlewareCI
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (! $request->user()) {
            abort(403);
        }

        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode('|', $role) as $r) {
                $allowedRoles[] = strtoupper($r);
            }
        }

        $userRoles = $request->user()->getRoleNames()->map(fn($r) => strtoupper($r));

        foreach ($allowedRoles as $role) {
            if ($userRoles->contains($role)) {
                return $next($request);
            }
        }

        abort(403, 'User does not have the right roles.');
    }
}
