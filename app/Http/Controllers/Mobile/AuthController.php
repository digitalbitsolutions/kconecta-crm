<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MobileTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(private readonly MobileTokenService $tokenService)
    {
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        $user = User::where('email', $email)->first();
        if (! $user || ! $this->credentialsAreValid($user, $password)) {
            return $this->unauthorized('Invalid email or password.', 'AUTH_INVALID_CREDENTIALS');
        }

        if (isset($user->is_active) && (int) $user->is_active === 0) {
            return $this->forbidden('User account is disabled.', 'USER_DISABLED');
        }

        Auth::setUser($user);
        $tokenPair = $this->tokenService->issueTokenPair($user);

        return response()->json([
            'data' => $tokenPair,
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $refreshToken = (string) $request->input('refresh_token');
        $claims = $this->tokenService->decode($refreshToken, 'refresh');
        if (! is_array($claims)) {
            return $this->unauthorized('Refresh token is invalid or expired.', 'INVALID_REFRESH_TOKEN');
        }

        $userId = isset($claims['sub']) ? (int) $claims['sub'] : 0;
        $user = $userId > 0 ? User::find($userId) : null;
        if (! $user) {
            return $this->unauthorized('User not found for refresh token.', 'TOKEN_USER_NOT_FOUND');
        }

        if (isset($user->is_active) && (int) $user->is_active === 0) {
            return $this->forbidden('User account is disabled.', 'USER_DISABLED');
        }

        Auth::setUser($user);
        $tokenPair = $this->tokenService->issueTokenPair($user);

        return response()->json([
            'data' => $tokenPair,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->unauthorized('Invalid or expired access token.', 'INVALID_ACCESS_TOKEN');
        }

        $session = $this->tokenService->buildSessionPayload($user);

        return response()->json([
            'data' => [
                'role' => $session['role'],
                'provider_id' => $session['provider_id'],
                'subject' => $session['subject'],
                'display_name' => $session['display_name'],
            ],
        ]);
    }

    private function credentialsAreValid(User $user, string $password): bool
    {
        $storedPassword = (string) $user->password;
        $hashInfo = Hash::info($storedPassword);

        if (! empty($hashInfo['algo'])) {
            return Hash::check($password, $storedPassword);
        }

        if (! hash_equals($storedPassword, $password)) {
            return false;
        }

        // Legacy plain-text password records are re-hashed on first successful API login.
        $user->password = Hash::make($password);
        $user->save();

        return true;
    }

    private function unauthorized(string $message, string $code): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], 401);
    }

    private function forbidden(string $message, string $code): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], 403);
    }

    private function validationError(array $fields): JsonResponse
    {
        return response()->json([
            'message' => 'Validation failed.',
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Validation failed.',
                'fields' => $fields,
            ],
            'meta' => [
                'retryable' => false,
            ],
        ], 422);
    }
}
