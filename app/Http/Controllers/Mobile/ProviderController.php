<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\ProviderAvailabilityStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
    public function __construct(private readonly ProviderAvailabilityStore $availabilityStore)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $claims = $this->claimsFromRequest($request);
        if (! $this->canReadProviders($claims)) {
            return $this->forbidden('Role scope cannot list providers.', 'ROLE_SCOPE_FORBIDDEN');
        }

        $roleFilter = $this->normalizeNullableString($request->query('role'));
        $statusFilter = $this->normalizeNullableString($request->query('status'));

        $query = User::query()->where('user_level_id', User::LEVEL_SERVICE_PROVIDER);
        if ($statusFilter === 'active') {
            $query->where(function ($builder) {
                $builder->whereNull('is_active')->orWhere('is_active', 1);
            });
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', 0);
        }

        $providers = $query->orderByDesc('id')->get();
        $providerIds = $providers->pluck('id')->map(fn ($id) => (int) $id)->all();
        $addresses = empty($providerIds)
            ? collect()
            : UserAddress::whereIn('user_id', $providerIds)->get()->keyBy('user_id');

        $records = [];
        foreach ($providers as $provider) {
            $record = $this->toProviderRecord($provider, $addresses->get($provider->id));
            if ($roleFilter !== null && $roleFilter !== '' && $record['role'] !== $roleFilter) {
                continue;
            }
            $records[] = $record;
        }

        return response()->json([
            'data' => $records,
            'meta' => [
                'count' => count($records),
                'filters' => [
                    'role' => $roleFilter,
                    'status' => $statusFilter,
                ],
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $claims = $this->claimsFromRequest($request);
        if (! $this->canReadProviders($claims)) {
            return $this->forbidden('Role scope cannot read provider detail.', 'ROLE_SCOPE_FORBIDDEN');
        }

        $provider = User::query()
            ->where('id', $id)
            ->where('user_level_id', User::LEVEL_SERVICE_PROVIDER)
            ->first();

        if (! $provider) {
            return $this->notFound('Provider not found.', 'PROVIDER_NOT_FOUND');
        }

        $address = UserAddress::where('user_id', $provider->id)->first();

        return response()->json([
            'data' => $this->toProviderRecord($provider, $address),
        ]);
    }

    public function availability(Request $request, int $id): JsonResponse
    {
        $provider = $this->resolveProvider($id);
        if (! $provider) {
            return $this->notFound('Provider not found.', 'PROVIDER_NOT_FOUND');
        }

        $scopeError = $this->guardAvailabilityScope($request, $id, false);
        if ($scopeError instanceof JsonResponse) {
            return $scopeError;
        }

        $payload = $this->availabilityStore->get($id);

        return response()->json([
            'data' => [
                'provider_id' => $payload['provider_id'],
                'revision' => $payload['revision'],
                'timezone' => $payload['timezone'],
                'slots' => $payload['slots'],
            ],
            'meta' => [
                'contract' => 'mobile-provider-availability.v1',
                'source' => $payload['source'],
            ],
        ]);
    }

    public function updateAvailability(Request $request, int $id): JsonResponse
    {
        $provider = $this->resolveProvider($id);
        if (! $provider) {
            return $this->notFound('Provider not found.', 'PROVIDER_NOT_FOUND');
        }

        $scopeError = $this->guardAvailabilityScope($request, $id, true);
        if ($scopeError instanceof JsonResponse) {
            return $scopeError;
        }

        $validator = Validator::make($request->all(), [
            'revision' => ['required', 'integer', 'min:1'],
            'timezone' => ['required', 'string', 'max:64'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.day' => ['required', 'string', 'in:mon,tue,wed,thu,fri,sat,sun'],
            'slots.*.start' => ['required', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'slots.*.end' => ['required', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'slots.*.enabled' => ['required', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $slots = $request->input('slots', []);
            if (! is_array($slots)) {
                return;
            }

            foreach ($slots as $index => $slot) {
                if (! is_array($slot)) {
                    continue;
                }

                $enabled = (bool) ($slot['enabled'] ?? false);
                if (! $enabled) {
                    continue;
                }

                $start = isset($slot['start']) ? (string) $slot['start'] : '';
                $end = isset($slot['end']) ? (string) $slot['end'] : '';
                if ($this->timeToMinutes($end) <= $this->timeToMinutes($start)) {
                    $validator->errors()->add("slots.$index.end", 'End time must be later than start time.');
                }
            }
        });

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $revision = (int) $request->input('revision');
        $timezone = (string) $request->input('timezone');
        $slots = $request->input('slots', []);

        $payload = $this->availabilityStore->update($id, $revision, $timezone, $slots);
        if (! $payload) {
            return response()->json([
                'message' => 'Availability revision conflict.',
                'error' => [
                    'code' => 'AVAILABILITY_REVISION_CONFLICT',
                    'message' => 'Availability revision conflict.',
                ],
            ], 409);
        }

        return response()->json([
            'data' => [
                'provider_id' => $payload['provider_id'],
                'revision' => $payload['revision'],
                'timezone' => $payload['timezone'],
                'slots' => $payload['slots'],
                'updated_at' => $payload['updated_at'],
            ],
            'meta' => [
                'contract' => 'mobile-provider-availability.v1',
                'source' => $payload['source'],
            ],
        ]);
    }

    private function toProviderRecord(User $provider, ?UserAddress $address): array
    {
        $fullName = trim((string) ($provider->first_name ?? '') . ' ' . (string) ($provider->last_name ?? ''));
        if ($fullName === '') {
            $fullName = (string) ($provider->user_name ?: ($provider->email ?: 'Service Provider'));
        }

        return [
            'id' => (int) $provider->id,
            'name' => $fullName,
            'role' => 'provider',
            'status' => (isset($provider->is_active) && (int) $provider->is_active === 0) ? 'inactive' : 'active',
            'category' => 'General Services',
            'city' => $this->normalizeNullableString($address?->city) ?? 'Unknown City',
            'rating' => 4.0,
        ];
    }

    private function claimsFromRequest(Request $request): array
    {
        $claims = $request->attributes->get('mobile.claims');
        return is_array($claims) ? $claims : [];
    }

    private function canReadProviders(array $claims): bool
    {
        $role = strtolower((string) ($claims['role'] ?? ''));
        return in_array($role, ['admin', 'manager', 'provider'], true);
    }

    private function guardAvailabilityScope(Request $request, int $providerId, bool $write): ?JsonResponse
    {
        $claims = $this->claimsFromRequest($request);
        $role = strtolower((string) ($claims['role'] ?? ''));
        $claimedProviderId = isset($claims['provider_id']) ? (int) $claims['provider_id'] : null;

        if ($role === 'admin') {
            return null;
        }

        if ($role !== 'provider') {
            return $this->forbidden('Role scope cannot manage provider availability.', 'ROLE_SCOPE_FORBIDDEN');
        }

        if ($claimedProviderId === null || $claimedProviderId <= 0) {
            return response()->json([
                'message' => 'Provider identity is missing in this session.',
                'error' => [
                    'code' => 'PROVIDER_IDENTITY_MISSING',
                    'message' => 'Provider identity is missing in this session.',
                ],
            ], 401);
        }

        if ($claimedProviderId !== $providerId) {
            return response()->json([
                'message' => 'Provider identity mismatch.',
                'error' => [
                    'code' => 'PROVIDER_IDENTITY_MISMATCH',
                    'message' => 'Provider identity mismatch.',
                ],
                'meta' => [
                    'reason' => $write ? 'write_scope_violation' : 'read_scope_violation',
                ],
            ], 403);
        }

        return null;
    }

    private function resolveProvider(int $providerId): ?User
    {
        return User::query()
            ->where('id', $providerId)
            ->where('user_level_id', User::LEVEL_SERVICE_PROVIDER)
            ->first();
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));
        return ($hours * 60) + $minutes;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);
        return $normalized !== '' ? $normalized : null;
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

    private function notFound(string $message, string $code): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], 404);
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
