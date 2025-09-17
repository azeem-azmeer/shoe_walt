<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user(); // session for web; token for API if using Sanctum on API routes

        // Not logged in
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Check admin via role OR boolean flag; be case-insensitive
        $isAdmin = strtolower((string)($user->role ?? '')) === 'admin'
                || (bool)($user->is_admin ?? false);

        if (!$isAdmin) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403); // will use resources/views/errors/403.blade.php if present
        }

        return $next($request);
    }
}
