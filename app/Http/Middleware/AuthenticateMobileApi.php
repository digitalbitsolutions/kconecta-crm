<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\MobileTokenService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMobileApi
{
    public function __construct(private readonly MobileTokenService $tokenService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (! $token) {
            return $this->unauthorized('Missing bearer token.', 'MISSING_TOKEN');
        }

        $claims = $this->tokenService->decode($token, 'access');
        if (! is_array($claims)) {
            return $this->unauthorized('Invalid or expired access token.', 'INVALID_ACCESS_TOKEN');
        }

        $userId = isset($claims['sub']) ? (int) $claims['sub'] : 0;
        if ($userId <= 0) {
            return $this->unauthorized('Invalid access token subject.', 'INVALID_ACCESS_TOKEN_SUBJECT');
        }

        $user = User::find($userId);
        if (! $user) {
            return $this->unauthorized('User not found for access token.', 'TOKEN_USER_NOT_FOUND');
        }

        if (isset($user->is_active) && (int) $user->is_active === 0) {
            return response()->json([
                'error' => [
                    'code' => 'USER_DISABLED',
                    'message' => 'User account is disabled.',
                ],
            ], 403);
        }

        Auth::setUser($user);
        $request->attributes->set('mobile.claims', $claims);

        return $next($request);
    }

    private function unauthorized(string $message, string $code): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], 401);
    }
}
