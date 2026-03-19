<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyAddress;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\MobileTokenService;
use App\Services\PropertyWorkflowStore;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    public function __construct(
        private readonly ApiController $legacyApiController,
        private readonly MobileTokenService $tokenService,
        private readonly PropertyWorkflowStore $workflowStore,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        if (! $token) {
            return $this->legacyApiController->searchProperties($request);
        }

        $claims = $this->tokenService->decode($token, 'access');
        $user = is_array($claims) ? User::find((int) ($claims['sub'] ?? 0)) : null;
        if (! $user) {
            return $this->errorResponse(401, 'INVALID_ACCESS_TOKEN', 'Invalid or expired access token.');
        }
        if (! $this->canManage($user)) {
            return $this->errorResponse(403, 'ROLE_SCOPE_FORBIDDEN', 'Role scope cannot access manager properties.');
        }

        return $this->portfolio($request, $user);
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->errorResponse(401, 'INVALID_ACCESS_TOKEN', 'Invalid or expired access token.');
        }
        if (! $this->canManage($user)) {
            return $this->errorResponse(403, 'ROLE_SCOPE_FORBIDDEN', 'Role scope cannot access manager summary.');
        }

        $kpis = $this->kpis($this->scope($user));
        $queue = $this->queueItems($user, null, null, 4);

        return response()->json([
            'data' => [
                'kpis' => $kpis,
                'priorities' => array_map(fn (array $item) => [
                    'id' => $item['id'],
                    'category' => $item['category'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'severity' => $item['severity'],
                    'due_at' => $item['sla_due_at'],
                    'updated_at' => $item['updated_at'],
                ], $queue),
            ],
            'meta' => [
                'contract' => 'mobile-manager-summary.v1',
                'generated_at' => now()->toIso8601String(),
                'source' => 'database',
            ],
        ]);
    }

    public function priorityQueue(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->errorResponse(401, 'INVALID_ACCESS_TOKEN', 'Invalid or expired access token.');
        }
        if (! $this->canManage($user)) {
            return $this->errorResponse(403, 'ROLE_SCOPE_FORBIDDEN', 'Role scope cannot access manager queue.');
        }

        $category = $this->strOrNull($request->query('category'));
        $severity = $this->strOrNull($request->query('severity'));
        $limit = max(1, min(100, (int) ($request->query('limit', 25))));
        $items = $this->queueItems($user, $category, $severity, $limit);

        return response()->json([
            'data' => ['items' => $items],
            'meta' => [
                'contract' => 'mobile-manager-priority-queue.v1',
                'generated_at' => now()->toIso8601String(),
                'source' => 'database',
                'filters' => ['category' => $category, 'severity' => $severity, 'limit' => $limit],
                'count' => count($items),
            ],
        ]);
    }

    public function completePriorityQueueItem(Request $request, string $queueItemId): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->errorResponse(401, 'INVALID_ACCESS_TOKEN', 'Invalid or expired access token.');
        }
        if (! $this->canManage($user)) {
            return $this->errorResponse(403, 'ROLE_SCOPE_FORBIDDEN', 'Role scope cannot complete queue items.');
        }

        $queue = $this->queueItems($user, null, null, 300);
        $item = collect($queue)->firstWhere('id', $queueItemId);
        if (! is_array($item)) {
            return $this->errorResponse(404, 'PRIORITY_QUEUE_ITEM_NOT_FOUND', 'Queue item not found.');
        }

        $resolutionCode = $this->strOrNull($request->input('resolution_code'));
        $note = $this->strOrNull($request->input('note'));
        $completion = $this->workflowStore->completeQueueItem($queueItemId, $resolutionCode, $note);

        $item['completed'] = true;
        $item['completed_at'] = $completion['completed_at'];
        $item['resolution_code'] = $completion['resolution_code'];
        $item['note'] = $completion['note'];
        $item['updated_at'] = $completion['completed_at'];

        return response()->json([
            'data' => ['item' => $item],
            'meta' => [
                'contract' => 'mobile-manager-priority-queue.v1',
                'flow' => 'queue_item_completion',
                'reason' => 'completed',
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->errorResponse(401, 'INVALID_ACCESS_TOKEN', 'Invalid or expired access token.');
        }
        if (! $this->canManage($user)) {
            return $this->errorResponse(403, 'ROLE_SCOPE_FORBIDDEN', 'Role scope cannot access property detail.');
        }

        $property = $this->scope($user)->where('id', $id)->first();
        if (! $property) {
            return $this->errorResponse(404, 'PROPERTY_NOT_FOUND', 'Property not found.');
        }

        $address = PropertyAddress::where('property_id', $property->id)->first();
        $assignment = $this->workflowStore->getAssignment((int) $property->id);
        $record = $this->propertyRecord($property, $address, $assignment);
        $record['timeline'] = [
            [
                'id' => 'property-' . $property->id . '-updated',
                'type' => 'note',
                'occurred_at' => ($property->updated_at ?: now())->toIso8601String(),
                'actor' => 'system',
                'summary' => 'Property synced from CRM.',
                'metadata' => [],
            ],
        ];

        return response()->json(['data' => $record]);
    }

    public function reserve(Request $request, int $id): JsonResponse
    {
        return $this->updateState($request, $id, 'reserved');
    }

    public function release(Request $request, int $id): JsonResponse
    {
        return $this->updateState($request, $id, 'available');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->errorResponse(401, 'INVALID_ACCESS_TOKEN', 'Invalid or expired access token.');
        }
        if (! $this->canManage($user)) {
            return $this->errorResponse(403, 'ROLE_SCOPE_FORBIDDEN', 'Role scope cannot update properties.');
        }

        $property = $this->scope($user)->where('id', $id)->first();
        if (! $property) {
            return $this->errorResponse(404, 'PROPERTY_NOT_FOUND', 'Property not found.');
        }

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:150'],
            'city' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'in:available,reserved,maintenance'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'manager_id' => ['sometimes', 'integer', 'min:1'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        if ($request->has('title')) {
            $property->title = $this->strOrNull($request->input('title'));
        }
        if ($request->has('status')) {
            $property->state_id = $this->statusToState((string) $request->input('status'));
        }
        if ($request->has('price')) {
            $property->sale_price = $request->input('price') !== null ? (int) round((float) $request->input('price')) : null;
        }
        if ($request->has('manager_id') && $user->isAdmin()) {
            $property->user_id = (int) $request->input('manager_id');
        }
        $property->save();

        if ($request->has('city')) {
            $address = PropertyAddress::firstOrNew(['property_id' => $property->id]);
            $address->city = (string) $request->input('city');
            $address->address = $address->address ?: $address->city;
            $address->save();
        }

        $address = PropertyAddress::where('property_id', $property->id)->first();
        $assignment = $this->workflowStore->getAssignment((int) $property->id);

        return response()->json([
            'data' => $this->propertyRecord($property, $address, $assignment),
            'meta' => ['contract' => 'mobile-property-mutation.v1', 'flow' => 'property_update', 'reason' => 'updated'],
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->errorResponse(401, 'INVALID_ACCESS_TOKEN', 'Invalid or expired access token.');
        }
        if (! $this->canManage($user)) {
            return $this->errorResponse(403, 'ROLE_SCOPE_FORBIDDEN', 'Role scope cannot create properties.');
        }

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:available,reserved,maintenance'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'manager_id' => ['nullable', 'integer', 'min:1'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $ownerId = $user->isAdmin() ? (int) ($request->input('manager_id') ?: $user->id) : (int) $user->id;
        $property = Property::create([
            'reference' => 'MOB-' . Str::upper(Str::random(8)),
            'title' => (string) $request->input('title'),
            'state_id' => $this->statusToState((string) $request->input('status')),
            'sale_price' => $request->input('price') !== null ? (int) round((float) $request->input('price')) : null,
            'user_id' => max(1, $ownerId),
        ]);

        PropertyAddress::create([
            'property_id' => $property->id,
            'city' => (string) $request->input('city'),
            'address' => (string) $request->input('city'),
        ]);

        return response()->json([
            'data' => $this->propertyRecord($property, PropertyAddress::where('property_id', $property->id)->first(), null),
            'meta' => ['contract' => 'mobile-property-mutation.v1', 'flow' => 'property_create', 'reason' => 'created'],
        ], 201);
    }

    public function providerCandidates(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $property = $user instanceof User ? $this->scope($user)->where('id', $id)->first() : null;
        if (! $property) {
            return $this->errorResponse(404, 'PROPERTY_NOT_FOUND', 'Property not found.');
        }

        $providers = User::where('user_level_id', User::LEVEL_SERVICE_PROVIDER)->orderByDesc('id')->get();
        $addresses = UserAddress::whereIn('user_id', $providers->pluck('id'))->get()->keyBy('user_id');
        $candidates = $providers->map(function (User $provider) use ($addresses): array {
            $name = trim((string) ($provider->first_name ?? '') . ' ' . (string) ($provider->last_name ?? ''));
            if ($name === '') {
                $name = (string) ($provider->user_name ?: ($provider->email ?: 'Service Provider'));
            }
            return [
                'id' => (int) $provider->id,
                'name' => $name,
                'role' => 'provider',
                'status' => (isset($provider->is_active) && (int) $provider->is_active === 0) ? 'inactive' : 'active',
                'category' => 'General Services',
                'city' => $this->strOrNull($addresses->get($provider->id)?->city),
                'rating' => 4.0,
            ];
        })->values()->all();

        return response()->json([
            'data' => ['property_id' => (int) $property->id, 'candidates' => $candidates],
            'meta' => ['contract' => 'mobile-property-provider-assignment.v1', 'flow' => 'provider_candidates', 'reason' => 'snapshot', 'source' => 'database'],
        ]);
    }

    public function assignProvider(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $property = $user instanceof User ? $this->scope($user)->where('id', $id)->first() : null;
        if (! $property) {
            return $this->errorResponse(404, 'PROPERTY_NOT_FOUND', 'Property not found.');
        }
        $providerId = (int) $request->input('provider_id');
        $provider = User::where('id', $providerId)->where('user_level_id', User::LEVEL_SERVICE_PROVIDER)->first();
        if (! $provider) {
            return $this->errorResponse(404, 'PROVIDER_NOT_FOUND', 'Provider not found.');
        }

        $assignment = $this->workflowStore->setAssignment((int) $property->id, $providerId, $this->strOrNull($request->input('note')));
        $address = PropertyAddress::where('property_id', $property->id)->first();

        return response()->json([
            'data' => [
                'property_id' => (int) $property->id,
                'provider_id' => $providerId,
                'assigned_at' => $assignment['assigned_at'],
                'property' => $this->propertyRecord($property, $address, $assignment),
            ],
            'meta' => ['contract' => 'mobile-property-provider-assignment.v1', 'flow' => 'provider_assignment', 'reason' => 'assigned'],
        ]);
    }

    public function assignmentContext(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $property = $user instanceof User ? $this->scope($user)->where('id', $id)->first() : null;
        if (! $property) {
            return $this->errorResponse(404, 'PROPERTY_NOT_FOUND', 'Property not found.');
        }

        $assignment = $this->workflowStore->getAssignment((int) $property->id);
        if (! $assignment) {
            return response()->json(['data' => ['property_id' => $id, 'assignment' => ['assigned' => false, 'provider' => null, 'assigned_at' => null, 'note' => null, 'state' => 'unassigned']], 'meta' => ['contract' => 'mobile-property-provider-assignment.v1', 'flow' => 'assignment_context', 'reason' => 'unassigned']]);
        }

        $provider = User::where('id', (int) $assignment['provider_id'])->first();
        if (! $provider) {
            return response()->json(['data' => ['property_id' => $id, 'assignment' => ['assigned' => true, 'provider' => null, 'assigned_at' => $assignment['assigned_at'], 'note' => $assignment['note'], 'state' => 'provider_missing']], 'meta' => ['contract' => 'mobile-property-provider-assignment.v1', 'flow' => 'assignment_context', 'reason' => 'provider_missing']]);
        }

        $providerAddress = UserAddress::where('user_id', $provider->id)->first();
        $name = trim((string) ($provider->first_name ?? '') . ' ' . (string) ($provider->last_name ?? ''));
        if ($name === '') {
            $name = (string) ($provider->user_name ?: ($provider->email ?: 'Service Provider'));
        }

        return response()->json(['data' => ['property_id' => $id, 'assignment' => ['assigned' => true, 'provider' => ['id' => (int) $provider->id, 'name' => $name, 'category' => 'General Services', 'city' => $this->strOrNull($providerAddress?->city), 'status' => (isset($provider->is_active) && (int) $provider->is_active === 0) ? 'inactive' : 'active', 'rating' => 4.0], 'assigned_at' => $assignment['assigned_at'], 'note' => $assignment['note'], 'state' => 'assigned']], 'meta' => ['contract' => 'mobile-property-provider-assignment.v1', 'flow' => 'assignment_context', 'reason' => 'assigned']]);
    }

    private function portfolio(Request $request, User $user): JsonResponse
    {
        $query = $this->applyFilters($this->scope($user), $request);
        $page = max(1, (int) ($request->query('page', 1)));
        $perPage = min(max(1, (int) ($request->query('per_page', 20))), 50);
        $total = (clone $query)->count();
        $rows = $query->orderByDesc('id')->forPage($page, $perPage)->get();
        $addresses = PropertyAddress::whereIn('property_id', $rows->pluck('id'))->get()->keyBy('property_id');
        $items = $rows->map(fn (Property $property) => $this->propertyRecord($property, $addresses->get($property->id), $this->workflowStore->getAssignment((int) $property->id)))->values()->all();
        $totalPages = max(1, (int) ceil($total / $perPage));

        return response()->json([
            'data' => $items,
            'meta' => [
                'count' => count($items),
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next_page' => $page < $totalPages,
                'filters' => ['status' => $this->strOrNull($request->query('status')), 'city' => $this->strOrNull($request->query('city')), 'manager_id' => $this->strOrNull($request->query('manager_id')), 'search' => $this->strOrNull($request->query('search'))],
                'kpis' => $this->kpis(clone $query),
                'source' => 'database',
            ],
        ]);
    }

    private function queueItems(User $user, ?string $category, ?string $severity, int $limit): array
    {
        $rows = $this->scope($user)->orderByDesc('updated_at')->limit(200)->get();
        $addresses = PropertyAddress::whereIn('property_id', $rows->pluck('id'))->get()->keyBy('property_id');
        $items = [];

        foreach ($rows as $property) {
            $assignment = $this->workflowStore->getAssignment((int) $property->id);
            $status = $this->stateToStatus((int) ($property->state_id ?? 0));
            $cat = $status === 'maintenance' ? 'maintenance_follow_up' : ($status === 'reserved' ? 'portfolio_review' : ($assignment ? 'quality_alert' : 'provider_assignment'));
            $sev = $cat === 'maintenance_follow_up' ? 'high' : (($cat === 'quality_alert') ? 'low' : 'medium');
            if ($category && $cat !== $category) {
                continue;
            }
            if ($severity && $sev !== $severity) {
                continue;
            }

            $id = 'property-' . $property->id . '-' . $cat;
            $completion = $this->workflowStore->getQueueCompletion($id);
            $updatedAt = ($completion['completed_at'] ?? null) ?: (($property->updated_at ?: now())->toIso8601String());
            $dueAt = $cat === 'maintenance_follow_up'
                ? CarbonImmutable::parse($updatedAt)->addDay()->toIso8601String()
                : ($cat === 'provider_assignment' ? CarbonImmutable::parse($updatedAt)->addDays(2)->toIso8601String() : ($cat === 'portfolio_review' ? CarbonImmutable::parse($updatedAt)->addDays(3)->toIso8601String() : null));
            $city = $this->strOrNull($addresses->get($property->id)?->city) ?? $this->strOrNull($property->locality) ?? 'Unknown City';

            $items[] = [
                'id' => $id,
                'property_id' => (int) $property->id,
                'property_title' => $property->title ?: ('Property #' . $property->id),
                'city' => $city,
                'status' => $status,
                'category' => $cat,
                'severity' => $sev,
                'sla_due_at' => $dueAt,
                'sla_state' => $dueAt ? (CarbonImmutable::parse($dueAt)->lessThan(CarbonImmutable::now()) ? 'overdue' : 'on_track') : 'no_deadline',
                'updated_at' => $updatedAt,
                'action' => $cat === 'provider_assignment' ? 'open_handoff' : ($cat === 'maintenance_follow_up' ? 'review_status' : 'open_property'),
                'completed' => $completion !== null,
                'completed_at' => $completion['completed_at'] ?? null,
                'resolution_code' => $completion['resolution_code'] ?? null,
                'note' => $completion['note'] ?? null,
                'title' => $cat === 'provider_assignment' ? 'Provider assignment pending' : ($cat === 'maintenance_follow_up' ? 'Maintenance follow-up required' : ($cat === 'portfolio_review' ? 'Reserved pipeline review' : 'Quality signal check')),
                'description' => ($property->title ?: ('Property #' . $property->id)) . ' queued for manager action.',
            ];
        }

        usort($items, static fn (array $a, array $b): int => strcmp((string) $b['updated_at'], (string) $a['updated_at']));
        return array_slice($items, 0, $limit);
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        $status = $this->strOrNull($request->query('status'));
        $city = $this->strOrNull($request->query('city'));
        $managerId = $this->strOrNull($request->query('manager_id'));
        $search = $this->strOrNull($request->query('search'));

        if ($status === 'available') {
            $query->where('state_id', 4);
        } elseif ($status === 'reserved') {
            $query->where('state_id', 2);
        } elseif ($status === 'maintenance') {
            $query->whereIn('state_id', [1, 3, 5]);
        }
        if ($managerId) {
            $query->where('user_id', (int) $managerId);
        }
        if ($search) {
            $query->where(fn (Builder $b) => $b->where('title', 'like', "%$search%")->orWhere('reference', 'like', "%$search%"));
        }
        if ($city) {
            $ids = PropertyAddress::where('city', 'like', "%$city%")->pluck('property_id')->all();
            $query->whereIn('id', empty($ids) ? [0] : $ids);
        }

        return $query;
    }

    private function scope(User $user): Builder
    {
        $query = Property::query();
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }
        return $query;
    }

    private function kpis(Builder $query): array
    {
        $rows = $query->get(['id', 'state_id', 'created_at']);
        $active = 0;
        $reserved = 0;
        $pending = 0;
        $ageDays = [];
        foreach ($rows as $row) {
            $status = $this->stateToStatus((int) ($row->state_id ?? 0));
            if ($status === 'available') {
                $active++;
            }
            if ($status === 'reserved') {
                $reserved++;
            }
            if ($status === 'available' && ! $this->workflowStore->getAssignment((int) $row->id)) {
                $pending++;
            }
            if ($row->created_at) {
                $ageDays[] = CarbonImmutable::parse($row->created_at)->diffInDays(now());
            }
        }
        $avg = empty($ageDays) ? 0 : round(array_sum($ageDays) / count($ageDays), 1);
        return ['active_properties' => $active, 'reserved_properties' => $reserved, 'avg_time_to_close_days' => $avg, 'provider_matches_pending' => $pending];
    }

    private function propertyRecord(Property $property, ?PropertyAddress $address, ?array $assignment): array
    {
        $price = $property->sale_price ?? $property->rental_price ?? 0;
        return [
            'id' => (int) $property->id,
            'title' => $property->title ?: ('Property #' . $property->id),
            'city' => $this->strOrNull($address?->city) ?? $this->strOrNull($property->locality) ?? 'Unknown City',
            'status' => $this->stateToStatus((int) ($property->state_id ?? 0)),
            'manager_id' => (string) ($property->user_id ?? ''),
            'provider_id' => $assignment['provider_id'] ?? null,
            'price' => (int) $price,
        ];
    }

    private function updateState(Request $request, int $id, string $status): JsonResponse
    {
        $user = $request->user();
        $property = $user instanceof User ? $this->scope($user)->where('id', $id)->first() : null;
        if (! $property) {
            return $this->errorResponse(404, 'PROPERTY_NOT_FOUND', 'Property not found.');
        }
        $property->state_id = $this->statusToState($status);
        $property->save();
        return response()->json(['data' => $this->propertyRecord($property, PropertyAddress::where('property_id', $property->id)->first(), $this->workflowStore->getAssignment((int) $property->id)), 'meta' => ['contract' => 'mobile-property-mutation.v1', 'flow' => 'property_status_update', 'reason' => $status]]);
    }

    private function canManage(User $user): bool
    {
        return $user->isAdmin() || $user->canManageProperties();
    }

    private function statusToState(string $status): int
    {
        return $status === 'reserved' ? 2 : ($status === 'maintenance' ? 5 : 4);
    }

    private function stateToStatus(int $stateId): string
    {
        if ($stateId === 2) {
            return 'reserved';
        }
        if (in_array($stateId, [1, 3, 5], true)) {
            return 'maintenance';
        }
        return 'available';
    }

    private function validationError(array $fields): JsonResponse
    {
        return response()->json(['message' => 'Validation failed.', 'error' => ['code' => 'VALIDATION_ERROR', 'message' => 'Validation failed.', 'fields' => $fields], 'meta' => ['retryable' => false]], 422);
    }

    private function errorResponse(int $status, string $code, string $message): JsonResponse
    {
        return response()->json(['message' => $message, 'error' => ['code' => $code, 'message' => $message]], $status);
    }

    private function strOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);
        return $value !== '' ? $value : null;
    }
}
