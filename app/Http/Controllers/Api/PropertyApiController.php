<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyFormCatalogsService;
use App\Models\Category;
use App\Models\CoverImage;
use App\Models\EmissionsRating;
use App\Models\Equipment;
use App\Models\Equipments;
use App\Models\Facade;
use App\Models\Feature;
use App\Models\Features;
use App\Models\HeatingFuel;
use App\Models\LocationPremises;
use App\Models\MoreImage;
use App\Models\NearestMunicipalityDistance;
use App\Models\Orientation;
use App\Models\Orientations;
use App\Models\Plant;
use App\Models\PlazaCapacity;
use App\Models\PowerConsumptionRating;
use App\Models\Property;
use App\Models\PropertyAddress;
use App\Models\ReasonForSale;
use App\Models\RentalType;
use App\Models\StateConservation;
use App\Models\Type;
use App\Models\TypeFloor;
use App\Models\TypeHeating;
use App\Models\TypeOfTerrain;
use App\Models\TypesFloors;
use App\Models\Typology;
use App\Models\User;
use App\Models\Video;
use App\Models\VisibilityInPortals;
use App\Models\WheeledAccess;
use Carbon\Carbon;
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
        $coverImageMap = empty($propertyIds)
            ? []
            : CoverImage::query()->whereIn('property_id', $propertyIds)->get()->keyBy('property_id')->all();

        $data = $propertyItems->map(
            fn (Property $property) => $this->formatProperty(
                $property,
                $typeMap,
                $categoryMap,
                $addressMap[(int) $property->id] ?? null,
                $ownerMap[(int) $property->user_id] ?? null,
                $coverImageMap[(int) $property->id] ?? null
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
        $coverImage = CoverImage::query()->where('property_id', (int) $property->id)->first();
        $moreImages = MoreImage::query()->where('property_id', (int) $property->id)->get();
        $videos = Video::query()->where('property_id', (int) $property->id)->get();
        $detailContext = $this->buildDetailContext($property, $address, $owner);

        return response()->json(
            $this->formatProperty(
                $property,
                $typeMap,
                $categoryMap,
                $address,
                $owner,
                $coverImage,
                $moreImages->all(),
                $videos->all(),
                $detailContext
            ),
            200
        );
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

    public function propertyFormCatalogs(Request $request, PropertyFormCatalogsService $catalogsService)
    {
        if (! $request->user()) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $typeId = (int) $request->query('type_id');
        if (! $catalogsService->supportsType($typeId)) {
            return response()->json(['message' => 'type_id invalido'], 422);
        }

        return response()->json([
            'data' => [
                'type_id' => $typeId,
                'catalogs' => $catalogsService->buildForType($typeId),
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $mediaValidationError = $this->validateMediaUploads($request);
        if ($mediaValidationError) {
            return response()->json(['message' => $mediaValidationError], 422);
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
            'page_url' => 'nullable|string|max:255',
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
        $this->syncAdvancedPropertyData($request, $property);
        $this->upsertAddress($request, (int) $property->id);
        try {
            $this->persistMediaUploads($request, $property);
        } catch (\RuntimeException $error) {
            $this->deletePropertyGraph($property);

            return response()->json(['message' => $error->getMessage()], 500);
        }

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

        $typeChanged = $request->filled('type_id') && (int) $request->input('type_id') !== (int) $property->type_id;

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin && (int) $property->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $mediaValidationError = $this->validateMediaUploads($request);
        if ($mediaValidationError) {
            return response()->json(['message' => $mediaValidationError], 422);
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
            'page_url' => 'nullable|string|max:255',
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
        if ($request->exists('page_url')) {
            $rawPageUrl = trim((string) $request->input('page_url', ''));
            $payload['page_url'] = $rawPageUrl !== ''
                ? Str::slug($rawPageUrl)
                : (array_key_exists('title', $payload)
                    ? Str::slug($payload['title'] . ' ' . ($property->reference ?: $this->generateReference()))
                    : null);
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

        if (! array_key_exists('page_url', $payload) && array_key_exists('title', $payload)) {
            $payload['page_url'] = Str::slug($payload['title'] . ' ' . ($property->reference ?: $this->generateReference()));
        }

        if (! empty($payload)) {
            $property->update($this->cleanData($payload));
        }

        $this->syncAdvancedPropertyData($request, $property, $typeChanged);
        $this->upsertAddress($request, (int) $property->id);
        try {
            $this->persistMediaUploads($request, $property);
        } catch (\RuntimeException $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }

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

        $this->deletePropertyGraph($property);

        return response()->json(['message' => 'Propiedad eliminada'], 200);
    }

    public function destroyMoreImage(Request $request, int $imageId)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $image = MoreImage::query()->find($imageId);
        if (! $image) {
            return response()->json(['message' => 'Imagen no encontrada'], 404);
        }

        $property = Property::query()->find((int) $image->property_id);
        if (! $property) {
            return response()->json(['message' => 'Propiedad no encontrada'], 404);
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin && (int) $property->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $image->delete();

        return response()->json(['message' => 'Imagen eliminada'], 200);
    }

    private function validateMediaUploads(Request $request): ?string
    {
        $coverImage = $request->file('cover_image');
        if ($coverImage) {
            if (! $coverImage->isValid()) {
                return 'La imagen de portada no es valida.';
            }

            if (! $this->isSupportedImageMime($coverImage->getMimeType())) {
                return 'Formato de imagen de portada no soportado.';
            }
        }

        $moreImages = $request->file('more_images', $request->file('more_images[]', []));
        foreach ((array) $moreImages as $file) {
            if (! $file) {
                continue;
            }

            if (! $file->isValid()) {
                return 'Una imagen de la galeria no es valida.';
            }

            if (! $this->isSupportedImageMime($file->getMimeType())) {
                return 'Formato de imagen de galeria no soportado.';
            }
        }

        $video = $request->file('video');
        if ($video) {
            if (! $video->isValid()) {
                return 'El video no es valido.';
            }

            if (! $this->isSupportedVideoMime($video->getMimeType())) {
                return 'El video no es valido.';
            }

            if ($video->getSize() > 51200 * 1024) {
                return 'El video excede el limite permitido.';
            }
        }

        return null;
    }

    private function persistMediaUploads(Request $request, Property $property): void
    {
        [$imagePath, $videoPath] = $this->ensureMediaDirectories();
        $propertyId = (int) $property->id;

        $coverImage = $request->file('cover_image');
        if ($coverImage && $coverImage->isValid()) {
            $filename = $this->persistImageAsWebp($coverImage, $imagePath);
            CoverImage::updateOrCreate(
                ['property_id' => $propertyId],
                ['url' => $filename]
            );
        }

        $moreImages = $request->file('more_images', $request->file('more_images[]', []));
        foreach ((array) $moreImages as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $filename = $this->persistImageAsWebp($file, $imagePath);
            MoreImage::create([
                'url' => $filename,
                'property_id' => $propertyId,
            ]);
        }

        $video = $request->file('video');
        if ($video && $video->isValid()) {
            $filename = $this->persistVideoFile($video, $videoPath);
            Video::updateOrCreate(
                ['property_id' => $propertyId],
                ['url' => $filename]
            );
        }
    }

    private function ensureMediaDirectories(): array
    {
        $imagePath = public_path('img/uploads');
        $videoPath = public_path('video/uploads');

        if (! is_dir($imagePath)) {
            @mkdir($imagePath, 0755, true);
        }

        if (! is_dir($videoPath)) {
            @mkdir($videoPath, 0755, true);
        }

        return [$imagePath, $videoPath];
    }

    private function persistImageAsWebp($file, string $imagePath): string
    {
        $randomName = bin2hex(random_bytes(16)) . '.webp';
        $mimeType = strtolower(trim((string) $file->getMimeType()));

        if ($mimeType === 'image/webp') {
            if (! $file->move($imagePath, $randomName)) {
                throw new \RuntimeException('Error al guardar la imagen WebP.');
            }

            return $randomName;
        }

        $tempPath = $file->getRealPath();
        $image = $this->createImageResource($mimeType, $tempPath);
        if (! $image) {
            throw new \RuntimeException('No se pudo procesar la imagen subida.');
        }

        $webpPath = $imagePath . DIRECTORY_SEPARATOR . $randomName;
        $converted = imagewebp($image, $webpPath, 80);
        imagedestroy($image);

        if (! $converted) {
            throw new \RuntimeException('Error al convertir la imagen a WebP.');
        }

        return $randomName;
    }

    private function persistVideoFile($file, string $videoPath): string
    {
        $extension = strtolower(trim((string) $file->getClientOriginalExtension()));
        if ($extension === '') {
            $extension = $this->resolveVideoExtension((string) $file->getMimeType());
        }

        $randomName = bin2hex(random_bytes(16)) . '.' . $extension;
        if (! $file->move($videoPath, $randomName)) {
            throw new \RuntimeException('Error al guardar el video.');
        }

        return $randomName;
    }

    private function resolveVideoExtension(string $mimeType): string
    {
        return match (strtolower(trim($mimeType))) {
            'video/webm' => 'webm',
            'video/quicktime' => 'mov',
            'video/x-msvideo', 'video/avi' => 'avi',
            'video/mpeg' => 'mpeg',
            'video/x-matroska' => 'mkv',
            default => 'mp4',
        };
    }

    private function createImageResource(string $mimeType, string $tempPath)
    {
        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($tempPath),
            'image/png' => @imagecreatefrompng($tempPath),
            'image/gif' => @imagecreatefromgif($tempPath),
            default => null,
        };
    }

    private function isSupportedImageMime(?string $mimeType): bool
    {
        return in_array(
            strtolower(trim((string) $mimeType)),
            ['image/webp', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
            true
        );
    }

    private function isSupportedVideoMime(?string $mimeType): bool
    {
        return in_array(
            strtolower(trim((string) $mimeType)),
            ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/avi', 'video/mpeg', 'video/x-matroska'],
            true
        );
    }

    private function deletePropertyGraph(Property $property): void
    {
        DB::transaction(function () use ($property) {
            $propertyId = (int) $property->id;
            DB::table('cover_image')->where('property_id', $propertyId)->delete();
            DB::table('more_images')->where('property_id', $propertyId)->delete();
            DB::table('video')->where('property_id', $propertyId)->delete();
            DB::table('property_address')->where('property_id', $propertyId)->delete();
            DB::table('features')->where('property_id', $propertyId)->delete();
            DB::table('equipments')->where('property_id', $propertyId)->delete();
            DB::table('orientations')->where('property_id', $propertyId)->delete();
            DB::table('types_floors')->where('property_id', $propertyId)->delete();
            $property->delete();
        });
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

    private function syncAdvancedPropertyData(Request $request, Property $property, bool $clearTypeSpecific = false): void
    {
        $data = $this->buildAdvancedFieldPayload($request, $clearTypeSpecific);

        if (! empty($data)) {
            Property::query()->where('id', (int) $property->id)->update($data);
        }

        $this->syncPivotSelection($request, $property, 'type_floor', TypesFloors::class, 'type_floor_id', $clearTypeSpecific);
        $this->syncPivotSelection($request, $property, 'orientation', Orientations::class, 'orientation_id', $clearTypeSpecific);
        $this->syncPivotSelection($request, $property, 'feature', Features::class, 'feature_id', $clearTypeSpecific);
        $this->syncPivotSelection($request, $property, 'equipment', Equipments::class, 'equipment_id', $clearTypeSpecific);
    }

    private function buildAdvancedFieldPayload(Request $request, bool $clearTypeSpecific = false): array
    {
        $data = $clearTypeSpecific ? $this->emptyTypeSpecificFieldPayload() : [];

        $this->setNullableStringField($request, $data, 'locality');
        $this->setNullableStringField($request, $data, 'esc_block');
        $this->setNullableStringField($request, $data, 'door');
        $this->setNullableStringField($request, $data, 'name_urbanization');

        $this->setNullableIntegerField($request, $data, 'plant', 'plant_id');
        $this->setNullableIntegerField($request, $data, 'visibility_in_portals', 'visibility_in_portals_id');
        $this->setNullableIntegerField($request, $data, 'location_premises', 'location_premises_id');
        $this->setNullableIntegerField($request, $data, 'rental_type', 'rental_type_id');
        $this->setNullableIntegerField($request, $data, 'reason_for_sale', 'reason_for_sale_id');
        $this->setNullableIntegerField($request, $data, 'typology', 'typology_id');
        $this->setNullableIntegerField($request, $data, 'state_conservation', 'state_conservation_id');
        $this->setNullableIntegerField($request, $data, 'facade', 'facade_id');
        $this->setNullableIntegerField($request, $data, 'type_heating', 'type_heating_id');
        $this->setNullableIntegerField($request, $data, 'heating_fuel', 'heating_fuel_id');
        $this->setNullableIntegerField($request, $data, 'energy_class', 'energy_class_id');
        $this->setNullableIntegerField($request, $data, 'power_consumption_rating', 'power_consumption_rating_id');
        $this->setNullableIntegerField($request, $data, 'emissions_rating', 'emissions_rating_id');
        $this->setNullableIntegerField($request, $data, 'plaza_capacity', 'plaza_capacity_id');
        $this->setNullableIntegerField($request, $data, 'type_of_terrain', 'type_of_terrain_id');
        $this->setNullableIntegerField($request, $data, 'wheeled_access', 'wheeled_access_id');
        $this->setNullableIntegerField(
            $request,
            $data,
            'nearest_municipality_distance',
            'nearest_municipality_distance_id'
        );

        $this->setNullableNumericField($request, $data, 'guarantee');
        $this->setNullableNumericField($request, $data, 'community_expenses');
        $this->setNullableNumericField($request, $data, 'ibi');
        $this->setNullableNumericField($request, $data, 'mortgage_rate');
        $this->setNullableNumericField($request, $data, 'meters_built');
        $this->setNullableNumericField($request, $data, 'useful_meters');
        $this->setNullableNumericField($request, $data, 'plot_meters');
        $this->setNullableNumericField($request, $data, 'linear_meters_of_facade');
        $this->setNullableNumericField($request, $data, 'energy_consumption');
        $this->setNullableNumericField($request, $data, 'emissions_consumption');
        $this->setNullableNumericField($request, $data, 'land_size');
        $this->setNullableNumericField($request, $data, 'm_long');
        $this->setNullableNumericField($request, $data, 'm_wide');

        $this->setNullableIntegerField($request, $data, 'has_tenants');
        $this->setNullableIntegerField($request, $data, 'bedrooms');
        $this->setNullableIntegerField($request, $data, 'bathrooms');
        $this->setNullableIntegerField($request, $data, 'number_of_shop_windows');
        $this->setNullableIntegerField($request, $data, 'number_of_plants');
        $this->setNullableIntegerField($request, $data, 'rooms');
        $this->setNullableIntegerField($request, $data, 'stays');
        $this->setNullableIntegerField($request, $data, 'year_of_construction');
        $this->setBooleanIntegerField($request, $data, 'bank_owned_property');
        $this->setBooleanIntegerField($request, $data, 'elevator');
        $this->setBooleanIntegerField($request, $data, 'wheelchair_accessible_elevator');
        $this->setBooleanIntegerField($request, $data, 'appropriate_for_children');
        $this->setBooleanIntegerField($request, $data, 'pet_friendly');
        $this->setBooleanIntegerField($request, $data, 'outdoor_wheelchair');
        $this->setBooleanIntegerField($request, $data, 'interior_wheelchair');
        $this->setNullableIntegerField($request, $data, 'max_num_tenants');

        return $data;
    }

    private function emptyTypeSpecificFieldPayload(): array
    {
        return [
            'visibility_in_portals_id' => null,
            'location_premises_id' => null,
            'rental_type_id' => null,
            'reason_for_sale_id' => null,
            'typology_id' => null,
            'facade_id' => null,
            'type_heating_id' => null,
            'heating_fuel_id' => null,
            'energy_class_id' => null,
            'power_consumption_rating_id' => null,
            'emissions_rating_id' => null,
            'plaza_capacity_id' => null,
            'type_of_terrain_id' => null,
            'wheeled_access_id' => null,
            'nearest_municipality_distance_id' => null,
            'guarantee' => null,
            'community_expenses' => null,
            'ibi' => null,
            'mortgage_rate' => null,
            'plot_meters' => null,
            'linear_meters_of_facade' => null,
            'number_of_shop_windows' => null,
            'number_of_plants' => null,
            'energy_consumption' => null,
            'emissions_consumption' => null,
            'land_size' => null,
            'm_long' => null,
            'm_wide' => null,
            'rooms' => null,
            'stays' => null,
            'elevator' => null,
            'wheelchair_accessible_elevator' => null,
            'appropriate_for_children' => null,
            'pet_friendly' => null,
            'max_num_tenants' => null,
            'outdoor_wheelchair' => null,
            'interior_wheelchair' => null,
        ];
    }

    private function setNullableStringField(Request $request, array &$data, string $input, ?string $column = null): void
    {
        if (! $request->exists($input)) {
            return;
        }

        $value = trim((string) $request->input($input, ''));
        $data[$column ?? $input] = $value !== '' ? $value : null;
    }

    private function setNullableIntegerField(Request $request, array &$data, string $input, ?string $column = null): void
    {
        if (! $request->exists($input)) {
            return;
        }

        $value = trim((string) $request->input($input, ''));
        $data[$column ?? $input] = $value !== '' ? (int) $value : null;
    }

    private function setBooleanIntegerField(Request $request, array &$data, string $input, ?string $column = null): void
    {
        if (! $request->exists($input)) {
            return;
        }

        $data[$column ?? $input] = $this->truthy($request->input($input)) ? 1 : 0;
    }

    private function setNullableNumericField(Request $request, array &$data, string $input, ?string $column = null): void
    {
        if (! $request->exists($input)) {
            return;
        }

        $value = $this->numeric($request->input($input));
        $data[$column ?? $input] = $value;
    }

    private function syncPivotSelection(
        Request $request,
        Property $property,
        string $input,
        string $pivotModelClass,
        string $relatedIdColumn,
        bool $forceClear = false
    ): void {
        if (! $forceClear && ! $request->exists($input)) {
            return;
        }

        $propertyId = (int) $property->id;
        $values = collect($request->input($input, []))
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->unique()
            ->values();

        $pivotModelClass::query()->where('property_id', $propertyId)->delete();

        foreach ($values as $value) {
            $pivotModelClass::query()->create([
                'property_id' => $propertyId,
                $relatedIdColumn => $value,
            ]);
        }
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'si', 'yes', 'on'], true);
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
        ?User $owner = null,
        ?CoverImage $coverImage = null,
        array $moreImages = [],
        array $videos = [],
        array $detailContext = []
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

        $coverImageFile = $this->extractCoverImageFile($coverImage);
        $coverImageUrl = $coverImageFile ? $this->buildCoverImageUrl($coverImageFile) : null;
        $item['cover_image'] = $coverImageFile;
        $item['cover_image_url'] = $coverImageUrl;
        $item['image'] = $coverImageUrl;
        $item['photo'] = $coverImageUrl;
        $item['more_images'] = array_map(function (MoreImage $image) {
            return [
                'id' => (int) $image->id,
                'url' => $image->url,
                'property_id' => (int) $image->property_id,
            ];
        }, $moreImages);
        $item['video'] = array_map(function (Video $video) {
            return [
                'id' => (int) $video->id,
                'url' => $video->url,
                'property_id' => (int) $video->property_id,
            ];
        }, $videos);
        $item['videos'] = $item['video'];

        if (! empty($detailContext)) {
            $item = array_merge($item, $detailContext);
        }

        return $item;
    }

    private function buildDetailContext(Property $property, ?PropertyAddress $address = null, ?User $owner = null): array
    {
        $propertyId = (int) $property->id;

        $stateConservation = $this->wrapSingle(StateConservation::query()->find($property->state_conservation_id));
        $visibilityInPortals = $this->wrapSingle(VisibilityInPortals::query()->find($property->visibility_in_portals_id));
        $locationPremises = $this->wrapSingle(LocationPremises::query()->find($property->location_premises_id));
        $typeHeating = $this->wrapSingle(TypeHeating::query()->find($property->type_heating_id));
        $heatingFuel = $this->wrapSingle(HeatingFuel::query()->find($property->heating_fuel_id));
        $facade = $this->wrapSingle(Facade::query()->find($property->facade_id));
        $typology = $this->wrapSingle(Typology::query()->find($property->typology_id));
        $rentalType = $this->wrapSingle(RentalType::query()->find($property->rental_type_id));
        $reasonForSale = $this->wrapSingle(ReasonForSale::query()->find($property->reason_for_sale_id));
        $plazaCapacity = $this->wrapSingle(PlazaCapacity::query()->find($property->plaza_capacity_id));
        $typeOfTerrain = $this->wrapSingle(TypeOfTerrain::query()->find($property->type_of_terrain_id));
        $wheeledAccess = $this->wrapSingle(WheeledAccess::query()->find($property->wheeled_access_id));
        $nearestMunicipalityDistance = $this->wrapSingle(
            NearestMunicipalityDistance::query()->find($property->nearest_municipality_distance_id)
        );
        $powerConsumptionRating = $this->wrapSingle(
            PowerConsumptionRating::query()->find($property->power_consumption_rating_id)
        );
        $emissionsRating = $this->wrapSingle(EmissionsRating::query()->find($property->emissions_rating_id));
        $plant = $this->wrapSingle(Plant::query()->find($property->plant_id));
        $typesFloors = $this->mapLinkedItems(
            $propertyId,
            TypesFloors::class,
            'type_floor_id',
            TypeFloor::class
        );
        $orientations = $this->mapLinkedItems(
            $propertyId,
            Orientations::class,
            'orientation_id',
            Orientation::class
        );

        return [
            'updated_at_text' => $this->formatUpdatedAt($property->updated_at),
            'property_address' => $address ? [$address->toArray()] : [],
            'user' => $this->mapOwner($owner),
            'state_conservation' => $stateConservation,
            'state_conservation_label' => $stateConservation[0]['name'] ?? null,
            'visibility_in_portals' => $visibilityInPortals,
            'visibility_in_portals_label' => $visibilityInPortals[0]['name'] ?? null,
            'location_premises' => $locationPremises,
            'location_premises_label' => $locationPremises[0]['name'] ?? null,
            'type_heating' => $typeHeating,
            'type_heating_label' => $typeHeating[0]['name'] ?? null,
            'heating_fuel' => $heatingFuel,
            'heating_fuel_label' => $heatingFuel[0]['name'] ?? null,
            'facade' => $facade,
            'facade_label' => $facade[0]['name'] ?? null,
            'typology' => $typology,
            'typology_label' => $typology[0]['name'] ?? null,
            'rental_type' => $rentalType,
            'rental_type_label' => $rentalType[0]['name'] ?? null,
            'reason_for_sale' => $reasonForSale,
            'reason_for_sale_label' => $reasonForSale[0]['name'] ?? null,
            'plaza_capacity' => $plazaCapacity,
            'plaza_capacity_label' => $plazaCapacity[0]['name'] ?? null,
            'type_of_terrain' => $typeOfTerrain,
            'type_of_terrain_label' => $typeOfTerrain[0]['name'] ?? null,
            'wheeled_access' => $wheeledAccess,
            'wheeled_access_label' => $wheeledAccess[0]['name'] ?? null,
            'nearest_municipality_distance' => $nearestMunicipalityDistance,
            'nearest_municipality_distance_label' => $nearestMunicipalityDistance[0]['name'] ?? null,
            'power_consumption_rating' => $powerConsumptionRating,
            'power_consumption_rating_label' => $powerConsumptionRating[0]['name'] ?? null,
            'emissions_rating' => $emissionsRating,
            'emissions_rating_label' => $emissionsRating[0]['name'] ?? null,
            'plant' => $plant,
            'plant_label' => $plant[0]['name'] ?? null,
            'types_floors' => $typesFloors,
            'orientations' => $orientations,
            'features' => $this->mapLinkedItems(
                $propertyId,
                Features::class,
                'feature_id',
                Feature::class
            ),
            'equipments' => $this->mapLinkedItems(
                $propertyId,
                Equipments::class,
                'equipment_id',
                Equipment::class
            ),
        ];
    }

    private function mapOwner(?User $owner): array
    {
        if (! $owner) {
            return [];
        }

        $fullName = trim((string) (($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')));
        $displayName = $fullName !== '' ? $fullName : (string) ($owner->user_name ?? '');
        $photoFile = trim((string) ($owner->photo ?? ''));
        $photoUrl = $photoFile !== ''
            ? $this->normalizeImageHost(asset('img/photo_profile/' . ltrim($photoFile, '/')))
            : $this->normalizeImageHost(asset('img/default-avatar-profile-icon.webp'));

        return [
            'id' => (int) $owner->id,
            'first_name' => $owner->first_name ?? null,
            'last_name' => $owner->last_name ?? null,
            'user_name' => $owner->user_name ?? null,
            'display_name' => $displayName !== '' ? $displayName : null,
            'email' => $owner->email ?? null,
            'landline_phone' => $owner->landline_phone ?? null,
            'photo' => $owner->photo ?? null,
            'photo_url' => $photoUrl,
        ];
    }

    private function mapLinkedItems(
        int $propertyId,
        string $pivotModelClass,
        string $relatedIdColumn,
        string $relatedModelClass
    ): array {
        $links = $pivotModelClass::query()->where('property_id', $propertyId)->get();

        $items = [];
        foreach ($links as $link) {
            $relatedId = (int) ($link->{$relatedIdColumn} ?? 0);
            if (! $relatedId) {
                continue;
            }

            $related = $relatedModelClass::query()->find($relatedId);
            if ($related) {
                $items[] = $related->toArray();
            }
        }

        return $items;
    }

    private function wrapSingle($model): array
    {
        return $model ? [$model->toArray()] : [];
    }

    private function formatUpdatedAt($value): string
    {
        if (! $value) {
            return '';
        }

        Carbon::setLocale('es');

        return Carbon::parse($value)->translatedFormat('d \\d\\e F \\d\\e Y');
    }

    private function extractCoverImageFile(?CoverImage $coverImage): ?string
    {
        if (! $coverImage) {
            return null;
        }

        $raw = trim((string) ($coverImage->url ?? ''));
        if ($raw === '') {
            return null;
        }

        return $raw;
    }

    private function buildCoverImageUrl(string $rawPath): string
    {
        $normalized = str_replace('\\', '/', trim($rawPath));

        if (Str::startsWith($normalized, ['http://', 'https://'])) {
            return $this->normalizeImageHost($normalized);
        }

        $normalized = ltrim($normalized, '/');
        $relativePath = Str::startsWith($normalized, ['img/', 'storage/'])
            ? $normalized
            : 'img/uploads/' . $normalized;

        return $this->normalizeImageHost(asset($relativePath));
    }

    private function normalizeImageHost(string $url): string
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! isset($parts['host'])) {
            return $url;
        }

        $host = strtolower((string) $parts['host']);
        if (! in_array($host, ['kconecta.com', 'www.kconecta.com'], true)) {
            return $url;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . '://kconecta.com' . $port . $path . $query . $fragment;
    }
}
