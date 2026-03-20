<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Property;
use App\Models\PropertyAddress;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PropertyApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $isAdmin = (int) $user->user_level_id === 1;

        $query = Property::query()->orderByDesc('id');
        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        $properties = $query->paginate($perPage);
        $propertyItems = collect($properties->items());
        $propertyIds = $propertyItems->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        $typeIds = $propertyItems->pluck('type_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $categoryIds = $propertyItems->pluck('category_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $ownerIds = $propertyItems->pluck('user_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

        $typeMap = empty($typeIds) ? [] : Type::query()->whereIn('id', $typeIds)->pluck('name', 'id')->all();
        $categoryMap = empty($categoryIds) ? [] : Category::query()->whereIn('id', $categoryIds)->pluck('name', 'id')->all();
        $addressMap = empty($propertyIds)
            ? []
            : PropertyAddress::query()->whereIn('property_id', $propertyIds)->get()->keyBy('property_id')->all();
        $ownerMap = empty($ownerIds)
            ? []
            : User::query()->whereIn('id', $ownerIds)->get()->keyBy('id')->all();

        $data = $propertyItems->map(
            fn (Property $property) => $this->formatProperty(
                $property,
                $typeMap,
                $categoryMap,
                $addressMap[(int) $property->id] ?? null,
                $ownerMap[(int) $property->user_id] ?? null
            )
        )->values()->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $properties->currentPage(),
                'total' => $properties->total(),
                'per_page' => $properties->perPage(),
                'next_page' => $properties->nextPageUrl(),
                'prev_page' => $properties->previousPageUrl(),
            ],
        ], 200);
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $isAdmin = (int) $user->user_level_id === 1;

        $property = Property::query()->find($id);
        if (! $property) {
            return response()->json(['message' => 'Propiedad no encontrada'], 404);
        }

        if (! $isAdmin && (int) $property->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $typeMap = $property->type_id
            ? Type::query()->where('id', (int) $property->type_id)->pluck('name', 'id')->all()
            : [];
        $categoryMap = $property->category_id
            ? Category::query()->where('id', (int) $property->category_id)->pluck('name', 'id')->all()
            : [];
        $address = PropertyAddress::query()->where('property_id', (int) $property->id)->first();
        $owner = User::query()->find((int) $property->user_id);

        return response()->json($this->formatProperty($property, $typeMap, $categoryMap, $address, $owner), 200);
    }

    public function propertyTypes(Request $request)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $featured = [
            1 => 'Viviendas familiares con espacios amplios.',
            15 => 'Entorno natural y estilo rural.',
            13 => 'Opciones urbanas listas para habitar.',
            4 => 'Ideal para actividad comercial o almacen.',
            14 => 'Seguridad para tu vehiculo o plaza.',
            9 => 'Suelo para proyectos o inversion.',
        ];

        $types = Type::query()
            ->whereIn('id', array_keys($featured))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'data' => $types->map(function (Type $type) use ($featured) {
                return [
                    'id' => (int) $type->id,
                    'name' => $type->name,
                    'description' => $featured[(int) $type->id] ?? null,
                ];
            })->values(),
        ], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'type_id' => 'required|integer|exists:type,id',
            'category_id' => 'nullable|integer|exists:category,id',
            'operation_type' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'rental_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:120',
            'province' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:30',
            'country' => 'nullable|string|max:120',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'state_id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos invalidos', 'errors' => $validator->errors()], 422);
        }

        $isAdmin = (int) $user->user_level_id === 1;
        $targetUserId = $isAdmin && $request->filled('user_id')
            ? (int) $request->input('user_id')
            : (int) $user->id;

        $title = trim((string) $request->input('title'));
        $typeId = (int) $request->input('type_id');
        $categoryId = $this->resolveCategoryId($request);
        $stateId = $request->filled('state_id') ? (int) $request->input('state_id') : 4;
        $reference = $this->generateReference();
        $pageUrl = Str::slug(trim((string) $request->input('page_url', $title . ' ' . $reference)));
        [$salePrice, $rentalPrice] = $this->resolvePricing($request);

        $propertyData = [
            'reference' => $reference,
            'title' => $title,
            'description' => $request->input('description'),
            'type_id' => $typeId,
            'category_id' => $categoryId,
            'sale_price' => $salePrice,
            'rental_price' => $rentalPrice,
            'state_id' => $stateId,
            'user_id' => $targetUserId,
            'address' => $request->input('address'),
            'page_url' => $pageUrl,
        ];

        $property = Property::create($this->cleanData($propertyData));
        $this->upsertAddress($request, (int) $property->id);

        return response()->json([
            'message' => 'Propiedad creada',
            'data' => $property->fresh(),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $property = Property::query()->find($id);
        if (! $property) {
            return response()->json(['message' => 'Propiedad no encontrada'], 404);
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin && (int) $property->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:150',
            'type_id' => 'sometimes|integer|exists:type,id',
            'category_id' => 'nullable|integer|exists:category,id',
            'operation_type' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'rental_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:120',
            'province' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:30',
            'country' => 'nullable|string|max:120',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'state_id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos invalidos', 'errors' => $validator->errors()], 422);
        }

        $payload = [];
        if ($request->filled('title')) {
            $payload['title'] = trim((string) $request->input('title'));
        }
        if ($request->filled('description')) {
            $payload['description'] = $request->input('description');
        }
        if ($request->filled('type_id')) {
            $payload['type_id'] = (int) $request->input('type_id');
        }

        $resolvedCategory = $this->resolveCategoryId($request);
        if ($resolvedCategory !== null) {
            $payload['category_id'] = $resolvedCategory;
        }

        if ($request->hasAny(['price', 'sale_price', 'rental_price', 'operation_type'])) {
            [$salePrice, $rentalPrice] = $this->resolvePricing($request, $property);
            $payload['sale_price'] = $salePrice;
            $payload['rental_price'] = $rentalPrice;
        }

        if ($request->filled('state_id')) {
            $payload['state_id'] = (int) $request->input('state_id');
        }
        if ($request->filled('address')) {
            $payload['address'] = $request->input('address');
        }

        if ($isAdmin && $request->filled('user_id')) {
            $payload['user_id'] = (int) $request->input('user_id');
        }

        if (array_key_exists('title', $payload)) {
            $payload['page_url'] = Str::slug($payload['title'] . ' ' . ($property->reference ?: $this->generateReference()));
        }

        if (! empty($payload)) {
            $property->update($this->cleanData($payload));
        }

        $this->upsertAddress($request, (int) $property->id);

        return response()->json([
            'message' => 'Propiedad actualizada',
            'data' => $property->fresh(),
        ], 200);
    }

    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $property = Property::query()->find($id);
        if (! $property) {
            return response()->json(['message' => 'Propiedad no encontrada'], 404);
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin && (int) $property->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        DB::transaction(function () use ($property) {
            $propertyId = (int) $property->id;
            DB::table('cover_image')->where('property_id', $propertyId)->delete();
            DB::table('more_image')->where('property_id', $propertyId)->delete();
            DB::table('video')->where('property_id', $propertyId)->delete();
            DB::table('property_address')->where('property_id', $propertyId)->delete();
            DB::table('features')->where('property_id', $propertyId)->delete();
            DB::table('equipments')->where('property_id', $propertyId)->delete();
            DB::table('orientations')->where('property_id', $propertyId)->delete();
            DB::table('types_floors')->where('property_id', $propertyId)->delete();
            $property->delete();
        });

        return response()->json(['message' => 'Propiedad eliminada'], 200);
    }

    private function generateReference(): string
    {
        do {
            $reference = 'MOB-' . strtoupper(Str::random(8));
        } while (Property::query()->where('reference', $reference)->exists());

        return $reference;
    }

    private function resolveCategoryId(Request $request): ?int
    {
        if ($request->filled('category_id')) {
            return (int) $request->input('category_id');
        }

        $raw = trim((string) $request->input('operation_type', ''));
        if ($raw === '' && $request->filled('category')) {
            $raw = trim((string) $request->input('category'));
        }
        if ($raw === '') {
            return null;
        }

        $category = Category::query()->where('name', 'like', $raw . '%')->first();
        if ($category) {
            return (int) $category->id;
        }

        $normalized = Str::lower($raw);
        if (str_contains($normalized, 'venta') || str_contains($normalized, 'buy')) {
            $category = Category::query()
                ->where('name', 'like', '%venta%')
                ->orWhere('name', 'like', '%compra%')
                ->first();
            if ($category) {
                return (int) $category->id;
            }
        }

        if (str_contains($normalized, 'alquiler') || str_contains($normalized, 'rent')) {
            $category = Category::query()
                ->where('name', 'like', '%alquiler%')
                ->first();
            if ($category) {
                return (int) $category->id;
            }
        }

        return $category ? (int) $category->id : null;
    }

    private function resolvePricing(Request $request, ?Property $existing = null): array
    {
        $salePrice = $request->filled('sale_price') ? $this->numeric($request->input('sale_price')) : $existing?->sale_price;
        $rentalPrice = $request->filled('rental_price') ? $this->numeric($request->input('rental_price')) : $existing?->rental_price;

        if ($request->filled('price')) {
            $price = $this->numeric($request->input('price'));
            $operation = Str::lower(trim((string) $request->input('operation_type', '')));
            $isRental = str_contains($operation, 'alquiler') || str_contains($operation, 'rent');

            if ($isRental) {
                $rentalPrice = $price;
            } else {
                $salePrice = $price;
            }
        }

        return [$salePrice, $rentalPrice];
    }

    private function numeric(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $stringValue = str_replace(' ', '', (string) $value);
        if (str_contains($stringValue, ',') && str_contains($stringValue, '.')) {
            $stringValue = str_replace('.', '', $stringValue);
            $stringValue = str_replace(',', '.', $stringValue);
        } elseif (str_contains($stringValue, ',')) {
            $stringValue = str_replace(',', '.', $stringValue);
        } elseif (str_contains($stringValue, '.')) {
            $stringValue = str_replace('.', '', $stringValue);
        }

        return is_numeric($stringValue) ? (float) $stringValue : null;
    }

    private function cleanData(array $data): array
    {
        return array_filter($data, static fn ($value) => $value !== null && $value !== '');
    }

    private function upsertAddress(Request $request, int $propertyId): void
    {
        if (! $request->hasAny(['address', 'city', 'province', 'postal_code', 'country', 'latitude', 'longitude'])) {
            return;
        }

        PropertyAddress::updateOrCreate(
            ['property_id' => $propertyId],
            [
                'address' => (string) $request->input('address', ''),
                'city' => (string) $request->input('city', ''),
                'province' => (string) $request->input('province', ''),
                'postal_code' => (string) $request->input('postal_code', ''),
                'country' => (string) $request->input('country', ''),
                'latitude' => (string) $request->input('latitude', ''),
                'longitude' => (string) $request->input('longitude', ''),
            ]
        );
    }

    private function formatProperty(
        Property $property,
        array $typeMap = [],
        array $categoryMap = [],
        ?PropertyAddress $address = null,
        ?User $owner = null
    ): array {
        $item = $property->toArray();
        $item['type_name'] = $typeMap[(int) ($property->type_id ?? 0)] ?? null;
        $item['category_name'] = $categoryMap[(int) ($property->category_id ?? 0)] ?? null;

        $item['address'] = $address?->address ?: ($item['address'] ?? null);
        $item['city'] = $address?->city ?: ($item['city'] ?? null);
        $item['province'] = $address?->province ?: ($item['province'] ?? null);
        $item['country'] = $address?->country ?: ($item['country'] ?? null);
        $item['postal_code'] = $address?->postal_code ?: ($item['postal_code'] ?? null);
        $item['latitude'] = $address?->latitude ?: ($item['latitude'] ?? null);
        $item['longitude'] = $address?->longitude ?: ($item['longitude'] ?? null);

        if ($owner) {
            $ownerName = trim((string) (($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')));
            if ($ownerName === '') {
                $ownerName = (string) ($owner->user_name ?? '');
            }

            $item['owner_name'] = $ownerName ?: null;
            $item['user_name'] = $ownerName ?: null;
            $item['user_first_name'] = $owner->first_name ?? null;
            $item['user_last_name'] = $owner->last_name ?? null;
        }

        return $item;
    }
}
