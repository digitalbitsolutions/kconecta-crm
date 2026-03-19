<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class MobileTokenService
{
    private const ACCESS_TOKEN_TYPE = 'access';
    private const REFRESH_TOKEN_TYPE = 'refresh';

    public function issueTokenPair(User $user): array
    {
        $session = $this->buildSessionPayload($user);

        $accessClaims = $this->baseClaims($session, self::ACCESS_TOKEN_TYPE, $this->accessTokenTtlMinutes() * 60);
        $refreshClaims = $this->baseClaims($session, self::REFRESH_TOKEN_TYPE, $this->refreshTokenTtlDays() * 86400);

        return [
            'access_token' => $this->encode($accessClaims),
            'refresh_token' => $this->encode($refreshClaims),
            'role' => $session['role'],
            'provider_id' => $session['provider_id'],
            'subject' => $session['subject'],
            'display_name' => $session['display_name'],
        ];
    }

    public function decode(string $token, ?string $expectedType = null): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $headerJson = $this->base64UrlDecode($encodedHeader);
        $payloadJson = $this->base64UrlDecode($encodedPayload);
        if ($headerJson === null || $payloadJson === null) {
            return null;
        }

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);
        if (! is_array($header) || ! is_array($payload)) {
            return null;
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            return null;
        }

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $this->signingKey(), true)
        );

        if (! hash_equals($expectedSignature, $encodedSignature)) {
            return null;
        }

        $expiresAt = isset($payload['exp']) && is_numeric($payload['exp']) ? (int) $payload['exp'] : null;
        if ($expiresAt === null || $expiresAt < time()) {
            return null;
        }

        if ($expectedType !== null && ($payload['typ'] ?? null) !== $expectedType) {
            return null;
        }

        return $payload;
    }

    public function resolveRole(User $user): string
    {
        if ($user->isAdmin()) {
            return 'admin';
        }

        if ($user->isServiceProvider()) {
            return 'provider';
        }

        if ($user->canManageProperties()) {
            return 'manager';
        }

        return 'user';
    }

    public function buildSessionPayload(User $user): array
    {
        $role = $this->resolveRole($user);
        $displayName = trim((string) ($user->first_name ?? '') . ' ' . (string) ($user->last_name ?? ''));
        if ($displayName === '') {
            $displayName = (string) ($user->user_name ?: ($user->email ?: 'User'));
        }

        return [
            'sub' => (string) $user->id,
            'role' => $role,
            'provider_id' => $role === 'provider' ? (int) $user->id : null,
            'subject' => (string) ($user->email ?? ''),
            'display_name' => $displayName,
        ];
    }

    private function baseClaims(array $session, string $type, int $ttlSeconds): array
    {
        $issuedAt = CarbonImmutable::now()->getTimestamp();
        $expiresAt = $issuedAt + $ttlSeconds;

        return [
            'iss' => (string) (config('app.url') ?: 'kconecta-crm'),
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'jti' => (string) Str::uuid(),
            'typ' => $type,
            'sub' => $session['sub'],
            'role' => $session['role'],
            'provider_id' => $session['provider_id'],
            'subject' => $session['subject'],
            'display_name' => $session['display_name'],
        ];
    }

    private function encode(array $claims): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $encodedPayload = $this->base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES));
        $encodedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $this->signingKey(), true)
        );

        return $encodedHeader . '.' . $encodedPayload . '.' . $encodedSignature;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        $padding = 4 - (strlen($value) % 4);
        if ($padding < 4) {
            $value .= str_repeat('=', $padding);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if ($decoded === false) {
            return null;
        }

        return $decoded;
    }

    private function signingKey(): string
    {
        $key = (string) config('app.key', '');
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $key;
    }

    private function accessTokenTtlMinutes(): int
    {
        return max(5, (int) env('MOBILE_ACCESS_TOKEN_TTL_MINUTES', 30));
    }

    private function refreshTokenTtlDays(): int
    {
        return max(1, (int) env('MOBILE_REFRESH_TOKEN_TTL_DAYS', 30));
    }
}
