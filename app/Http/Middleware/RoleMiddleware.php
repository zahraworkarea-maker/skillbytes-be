<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'errors' => null,
            ], 401);
        }

        $userRole = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
        $allowedRoles = array_map('strtolower', $roles);

        if (!in_array(strtolower($userRole), $allowedRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: insufficient role',
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
