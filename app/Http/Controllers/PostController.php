<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\ContactOption;
use App\Models\Country;
use App\Models\CoverImage;
use App\Models\Door;
use App\Models\EmissionsRating;
use App\Models\EnergyClass;
use App\Models\Equipment;
use App\Models\Equipments;
use App\Models\Facade;
use App\Models\Feature;
use App\Models\Features;
use App\Models\GaragePriceCategory;
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
use App\Models\Province;
use App\Models\ReasonForSale;
use App\Models\RentalType;
use App\Models\Service;
use App\Models\ServiceAddress;
use App\Models\ServiceType;
use App\Models\ServiceTypeLink;
use App\Models\State;
use App\Models\StateConservation;
use App\Models\TerrainQualification;
use App\Models\TerrainQualifications;
use App\Models\TerrainUse;
use App\Models\Type;
use App\Models\TypeFloor;
use App\Models\TypeHeating;
use App\Models\TypeOfTerrain;
use App\Models\TypesFloors;
use App\Models\Typology;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserLevel;
use App\Models\Video;
use App\Models\VisibilityInPortals;
use App\Models\WheeledAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{
    private const ALLOWED_VIDEO_MIME_TYPES = [
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/mpeg',
    ];

    private function maxVideoUploadBytes(): int
    {
        $maxMb = max(1, (int) config('uploads.video_max_upload_mb', 150));

        return $maxMb * 1024 * 1024;
    }

    private function maxVideoUploadLabel(): string
    {
        return max(1, (int) config('uploads.video_max_upload_mb', 150)) . 'MB';
    }

    private function normalizeLegacyLabel(?string $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $normalized = strtr($value, [
            'ÃƒÂ¡' => 'á',
            'ÃƒÂ©' => 'é',
            'ÃƒÂ­' => 'í',
            'ÃƒÂ³' => 'ó',
            'ÃƒÂº' => 'ú',
            'ÃƒÂ±' => 'ñ',
            'Ãƒâ€˜' => 'Ñ',
            'Ãƒâ€°' => 'É',
            'ÃƒÂ' => 'Á',
            'ÃƒÂ“' => 'Ó',
            'ÃƒÅ¡' => 'Ú',
            'Ã‚Â¿' => '¿',
            'Ã‚Â¡' => '¡',
            'Ã‚Â' => '',
            'Ã¢â€šÂ¬' => '€',
        ]);

        return match ($normalized) {
            'S?tano' => 'Sótano',
            'Semi-s?tano' => 'Semi-sótano',
            '?tico' => 'Ático',
            'Direcci?n exacta' => 'Dirección exacta',
            'Ocultar direcci?n' => 'Ocultar dirección',
            default => $normalized,
        };
    }

    private function normalizeLegacyCatalog(iterable $records): array
    {
        $normalized = [];

        foreach ($records as $record) {
            $row = is_array($record) ? $record : $record->toArray();

            array_walk_recursive($row, function (&$value): void {
                if (is_string($value)) {
                    $value = $this->normalizeLegacyLabel($value);
                }
            });

            $normalized[] = $row;
        }

        return $normalized;
    }

    private function normalizeTerrainTypeCatalog(?int $selectedId = null): array
    {
        $query = TypeOfTerrain::query()->whereIn('name', ['Urbano', 'Urbanizable', 'Rústico']);

        if ($selectedId) {
            $query->orWhere('id', $selectedId);
        }

        return collect($this->normalizeLegacyCatalog($query->orderBy('id')->get()))
            ->unique('id')
            ->values()
            ->all();
    }

    private function normalizeTerrainUseCatalog(): array
    {
        return $this->normalizeLegacyCatalog(TerrainUse::query()->orderBy('id')->get());
    }

    private function normalizeTerrainQualificationCatalog(): array
    {
        return $this->normalizeLegacyCatalog(TerrainQualification::query()->orderBy('id')->get());
    }

    private function setPositiveIntegerField(array &$dataForDb, string $column, mixed $value): void
    {
        if (is_numeric($value) && (int) $value > 0) {
            $dataForDb[$column] = (int) $value;
        }
    }

    private function normalizeDecimalValue(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9,.\-]/', '', str_replace(' ', '', (string) $value));
        if (! is_string($normalized) || $normalized === '' || $normalized === '-' || $normalized === ',' || $normalized === '.') {
            return null;
        }

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');

            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        } elseif (substr_count($normalized, '.') > 1) {
            $lastDot = strrpos($normalized, '.');
            $integerPart = str_replace('.', '', substr($normalized, 0, $lastDot));
            $decimalPart = substr($normalized, $lastDot + 1);
            $normalized = $integerPart . '.' . $decimalPart;
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function deleteStoredFile(string $directory, ?string $fileName): void
    {
        if (! $fileName) {
            return;
        }

        $filePath = public_path(trim($directory, '/') . '/' . ltrim($fileName, '/'));
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    private function normalizePositiveIds(mixed $values): array
    {
        if (! is_array($values)) {
            $values = [$values];
        }

        $normalized = [];
        foreach ($values as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                $normalized[] = (int) $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function deleteOwnedMoreImages(string $ownerColumn, int $ownerId, mixed $imageIds): void
    {
        $normalizedIds = $this->normalizePositiveIds($imageIds);
        if (empty($normalizedIds)) {
            return;
        }

        $images = MoreImage::where($ownerColumn, $ownerId)
            ->whereIn('id', $normalizedIds)
            ->get();

        foreach ($images as $image) {
            $this->deleteStoredFile('img/uploads', $image->url);
            $image->delete();
        }
    }

    private function storeUploadedImage($file, string $imagePath, array $options = []): array
    {
        if (! $file || ! $file->isValid()) {
            return ['success' => false, 'error' => 'La imagen no es valida.'];
        }

        $targetWidth = (int) ($options['target_width'] ?? 0);
        $targetHeight = (int) ($options['target_height'] ?? 0);
        $shouldCropToFit = (bool) ($options['crop_to_fit'] ?? false);

        $mimeType = $file->getMimeType();
        if ($mimeType === 'image/webp' && $targetWidth <= 0 && $targetHeight <= 0) {
            $fileName = bin2hex(random_bytes(16)) . '.webp';
            if (! $file->move($imagePath, $fileName)) {
                return ['success' => false, 'error' => 'Error al mover la imagen WebP.'];
            }

            return ['success' => true, 'file_name' => $fileName];
        }

        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return ['success' => false, 'error' => 'Formato de imagen no soportado.'];
        }

        $canConvertToWebp = extension_loaded('gd')
            && function_exists('imagewebp')
            && (($mimeType !== 'image/jpeg') || function_exists('imagecreatefromjpeg'))
            && (($mimeType !== 'image/png') || function_exists('imagecreatefrompng'))
            && (($mimeType !== 'image/webp') || function_exists('imagecreatefromwebp'));

        if (! $canConvertToWebp) {
            return ['success' => false, 'error' => 'El servidor no puede convertir imagenes a WebP en este momento.'];
        }

        $fileName = bin2hex(random_bytes(16)) . '.webp';
        $tempPath = $file->getRealPath();
        $image = $this->createGdImageFromPath($tempPath, $mimeType);

        if (! $image) {
            return ['success' => false, 'error' => 'Error al procesar la imagen.'];
        }

        if ($targetWidth > 0 && $targetHeight > 0) {
            $sourceWidth = imagesx($image);
            $sourceHeight = imagesy($image);
            $sourceX = 0;
            $sourceY = 0;
            $sourceCropWidth = $sourceWidth;
            $sourceCropHeight = $sourceHeight;

            if ($shouldCropToFit) {
                $sourceRatio = $sourceWidth / max(1, $sourceHeight);
                $targetRatio = $targetWidth / max(1, $targetHeight);

                if ($sourceRatio > $targetRatio) {
                    $sourceCropWidth = (int) round($sourceHeight * $targetRatio);
                    $sourceX = (int) floor(($sourceWidth - $sourceCropWidth) / 2);
                } elseif ($sourceRatio < $targetRatio) {
                    $sourceCropHeight = (int) round($sourceWidth / $targetRatio);
                    $sourceY = (int) floor(($sourceHeight - $sourceCropHeight) / 2);
                }
            }

            $resized = imagecreatetruecolor($targetWidth, $targetHeight);
            imagealphablending($resized, true);
            imagesavealpha($resized, true);

            imagecopyresampled(
                $resized,
                $image,
                0,
                0,
                $sourceX,
                $sourceY,
                $targetWidth,
                $targetHeight,
                $sourceCropWidth,
                $sourceCropHeight
            );

            imagedestroy($image);
            $image = $resized;
        }

        $webpPath = $imagePath . DIRECTORY_SEPARATOR . $fileName;
        $converted = imagewebp($image, $webpPath, 80);
        imagedestroy($image);

        if (! $converted) {
            return ['success' => false, 'error' => 'Error al convertir la imagen a WebP.'];
        }

        return ['success' => true, 'file_name' => $fileName];
    }

    private function createGdImageFromPath(string $path, string $mimeType): \GdImage|false
    {
        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($image && ($mimeType === 'image/png' || $mimeType === 'image/webp')) {
            if (function_exists('imagepalettetotruecolor') && ! imageistruecolor($image)) {
                imagepalettetotruecolor($image);
            }
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        return $image;
    }

    private function validateUploadedVideo($video): ?string
    {
        if (! $video) {
            return null;
        }

        if (! $video->isValid()) {
            return 'El video no se pudo subir correctamente o supera el limite permitido de '
                . $this->maxVideoUploadLabel()
                . ' por archivo.';
        }

        if (! in_array($video->getMimeType(), self::ALLOWED_VIDEO_MIME_TYPES, true)) {
            return 'El video no es valido. Solo se permiten MP4, MOV, AVI o MPEG.';
        }

        if ($video->getSize() > $this->maxVideoUploadBytes()) {
            return 'El video excede el limite permitido de '
                . $this->maxVideoUploadLabel()
                . ' por archivo.';
        }

        return null;
    }

    private function storeUploadedVideo($video, string $videoPath): array
    {
        if ($validationError = $this->validateUploadedVideo($video)) {
            return ['success' => false, 'error' => $validationError];
        }

        if (! is_dir($videoPath)) {
            @mkdir($videoPath, 0755, true);
        }

        $extension = strtolower((string) $video->getClientOriginalExtension());
        if ($extension === '') {
            $extension = match ($video->getMimeType()) {
                'video/mp4' => 'mp4',
                'video/quicktime' => 'mov',
                'video/x-msvideo' => 'avi',
                'video/mpeg' => 'mpeg',
                default => 'mp4',
            };
        }

        $randomName = bin2hex(random_bytes(16)) . '.' . $extension;
        if (! $video->move($videoPath, $randomName)) {
            return ['success' => false, 'error' => 'Error al guardar el video.'];
        }

        return ['success' => true, 'file_name' => $randomName];
    }

    public function index()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->canManageProperties()) {
            return redirect()->to('/post/services');
        }

        $userLevelName = UserLevel::find($user->user_level_id)?->name ?? 'Usuario';
        $featuredTypeConfig = [
            1 => [
                'label' => 'Casa o chalet',
                'image' => 'img/casa-1.webp',
                'summary' => 'Viviendas familiares con espacios amplios.',
            ],
            15 => [
                'label' => 'Casa rústica',
                'image' => 'img/casa-rustica.png',
                'summary' => 'Entorno natural y estilo rural.',
            ],
            13 => [
                'label' => 'Piso',
                'image' => 'img/piso-icon.png',
                'summary' => 'Opciones urbanas listas para habitar.',
            ],
            4 => [
                'label' => 'Local o nave',
                'image' => 'img/nave-local-icon.avif',
                'summary' => 'Ideal para actividad comercial o almacén.',
            ],
            14 => [
                'label' => 'Garaje',
                'image' => 'img/garaje-icon.png',
                'summary' => 'Seguridad para tu vehículo o plaza.',
            ],
            9 => [
                'label' => 'Terreno',
                'image' => 'img/pueblo-terreno_1.avif',
                'summary' => 'Suelo para proyectos o inversión.',
            ],
        ];

        $typeRows = Type::whereIn('id', array_keys($featuredTypeConfig))
            ->get(['id', 'name'])
            ->keyBy('id');

        $propertyTypes = [];
        foreach ($featuredTypeConfig as $typeId => $config) {
            $propertyTypes[] = [
                'id' => $typeId,
                'label' => $typeRows[$typeId]->name ?? $config['label'],
                'image' => $config['image'],
                'summary' => $config['summary'],
            ];
        }

        return view('post.index', [
            'user' => $user,
            'userLevelName' => $userLevelName,
            'isAdmin' => (int) $user->user_level_id === 1,
            'activeNav' => 'properties',
            'propertyTypes' => $propertyTypes,
        ]);
    }

    public function postDetails(string $reference)
    {
        return view('placeholder', ['title' => 'Post Details']);
    }

    public function createForm(string $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $userLevelName = UserLevel::find($user->user_level_id)?->name ?? 'Usuario';
        $isAdmin = (int) $user->user_level_id === 1;

        if ($id === 'service') {
            $serviceType = ServiceType::orderBy('name')->get();

            return view('post.forms.form_service', [
                'user' => $user,
                'userLevelName' => $userLevelName,
                'isAdmin' => $isAdmin,
                'activeNav' => 'services',
                'mapsKey' => config('services.google.maps_key'),
                'serviceType' => $serviceType,
            ]);
        }

        if (! $user->canManageProperties()) {
            return redirect()
                ->to('/post/services')
                ->with('status', 'Tu cuenta no puede publicar propiedades.');
        }

        $category = $this->normalizeLegacyCatalog(Category::all());
        $contactOption = $this->normalizeLegacyCatalog(ContactOption::all());
        $visibilityInPortals = $this->normalizeLegacyCatalog(VisibilityInPortals::all());
        $rentalType = $this->normalizeLegacyCatalog(RentalType::all());
        $reasonForSale = $this->normalizeLegacyCatalog(ReasonForSale::all());
        $typology = $this->normalizeLegacyCatalog(Typology::where('type_id', 1)->get());
        $orientation = $this->normalizeLegacyCatalog(Orientation::all());
        $typeHeating = $this->normalizeLegacyCatalog(TypeHeating::all());
        $heatingFuel = $this->normalizeLegacyCatalog(HeatingFuel::all());
        $energyClass = $this->normalizeLegacyCatalog(EnergyClass::all());
        $powerConsumptionRating = $this->normalizeLegacyCatalog(PowerConsumptionRating::all());
        $emissionsRating = $this->normalizeLegacyCatalog(EmissionsRating::all());
        $stateConservation = $this->normalizeLegacyCatalog(StateConservation::all());
        $plant = $this->normalizeLegacyCatalog(Plant::all());
        $typeFloor = $this->normalizeLegacyCatalog(TypeFloor::all());
        $facade = $this->normalizeLegacyCatalog(Facade::all());
        $feature = $this->normalizeLegacyCatalog(Feature::all());
        $equipment = $this->normalizeLegacyCatalog(Equipment::all());
        $plazaCapacity = $this->normalizeLegacyCatalog(PlazaCapacity::all());
        $typeOfTerrain = $this->normalizeTerrainTypeCatalog();
        $terrainUse = $this->normalizeTerrainUseCatalog();
        $terrainQualification = $this->normalizeTerrainQualificationCatalog();
        $wheeledAccess = $this->normalizeLegacyCatalog(WheeledAccess::all());
        $nearestMunicipalityDistance = $this->normalizeLegacyCatalog(NearestMunicipalityDistance::all());
        $locationPremises = $this->normalizeLegacyCatalog(LocationPremises::all());
        $garagePriceCategory = $this->normalizeLegacyCatalog(GaragePriceCategory::all());

        $formView = null;
        switch ((string) $id) {
            case '1':
                $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 1)->get());
                $formView = 'post.forms.form_1';
                break;
            case '13':
                $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 1)->get());
                $formView = 'post.forms.form_2';
                break;
            case '4':
                $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 4)->get());
                $formView = 'post.forms.form_3';
                break;
            case '14':
                $feature = $this->normalizeLegacyCatalog(Feature::where('id_type', 14)->get());
                $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 14)->get());
                $formView = 'post.forms.form_4';
                break;
            case '9':
                $feature = $this->normalizeLegacyCatalog(Feature::where('id_type', 9)->get());
                $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 4)->get());
                $formView = 'post.forms.form_5';
                break;
            case '15':
                $typology = $this->normalizeLegacyCatalog(Typology::where('type_id', 15)->get());
                $formView = 'post.forms.form_casa_rustica';
                break;
        }

        if (! $formView) {
            return redirect()->to('/post/index');
        }

        return view($formView, [
            'user' => $user,
            'userLevelName' => $userLevelName,
            'isAdmin' => $isAdmin,
            'activeNav' => 'properties',
            'mapsKey' => config('services.google.maps_key'),
            'category' => $category,
            'contactOption' => $contactOption,
            'visibilityInPortals' => $visibilityInPortals,
            'rentalType' => $rentalType,
            'reasonForSale' => $reasonForSale,
            'typology' => $typology,
            'orientation' => $orientation,
            'typeHeating' => $typeHeating,
            'heatingFuel' => $heatingFuel,
            'energyClass' => $energyClass,
            'powerConsumptionRating' => $powerConsumptionRating,
            'emissionsRating' => $emissionsRating,
            'stateConservation' => $stateConservation,
            'plant' => $plant,
            'typeFloor' => $typeFloor,
            'facade' => $facade,
            'feature' => $feature,
            'equipment' => $equipment,
            'plazaCapacity' => $plazaCapacity,
            'typeOfTerrain' => $typeOfTerrain,
            'terrainUse' => $terrainUse,
            'terrainQualification' => $terrainQualification,
            'wheeledAccess' => $wheeledAccess,
            'nearestMunicipalityDistance' => $nearestMunicipalityDistance,
            'locationPremises' => $locationPremises,
            'garagePriceCategory' => $garagePriceCategory,
        ]);
    }

    public function myPosts()
    {
        $user = Auth::user();
        if ($user && ! $user->canManageProperties()) {
            return redirect()->to('/post/services');
        }

        $isAdmin = $user && (int) $user->user_level_id === 1;
        $userLevelName = $user ? (UserLevel::find($user->user_level_id)?->name ?? 'Usuario') : 'Usuario';
        $isCompanyUser = $user && (int) $user->user_level_id === User::LEVEL_AGENT;

        $request = request();
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => (string) $request->query('status', ''),
            'category' => (string) $request->query('category', ''),
            'type' => (string) $request->query('type', ''),
            'partner_type' => (string) $request->query('partner_type', ''),
            'partner_id' => (string) $request->query('partner_id', ''),
            'ds' => (string) $request->query('ds', ''),
            'de' => (string) $request->query('de', ''),
        ];

        $query = Property::query();
        $partnerLevelIds = [User::LEVEL_AGENT, User::LEVEL_SERVICE_PROVIDER];
        if (! $isAdmin && $user) {
            $query->where('user_id', $user->id);
        }

        if ($filters['q'] !== '') {
            $search = $filters['q'];
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('reference', 'like', '%' . $search . '%');
            });
        }

        if ($filters['status'] !== '' && $filters['status'] !== 'all') {
            $query->where('state_id', (int) $filters['status']);
        }

        if ($filters['category'] !== '' && $filters['category'] !== 'all') {
            $query->where('category_id', (int) $filters['category']);
        }

        if ($filters['type'] !== '' && $filters['type'] !== 'all') {
            $query->where('type_id', (int) $filters['type']);
        }

        if ($isAdmin) {
            if (
                $filters['partner_type'] !== ''
                && $filters['partner_type'] !== 'all'
                && in_array((int) $filters['partner_type'], $partnerLevelIds, true)
            ) {
                $query->whereIn('user_id', User::query()
                    ->where('user_level_id', (int) $filters['partner_type'])
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all());
            }

            if ($filters['partner_id'] !== '' && $filters['partner_id'] !== 'all') {
                $partnerExists = User::query()
                    ->where('id', (int) $filters['partner_id'])
                    ->whereIn('user_level_id', $partnerLevelIds)
                    ->exists();

                if ($partnerExists) {
                    $query->where('user_id', (int) $filters['partner_id']);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        if ($filters['ds'] !== '') {
            $query->whereDate('created_at', '>=', $filters['ds']);
        }

        if ($filters['de'] !== '') {
            $query->whereDate('created_at', '<=', $filters['de']);
        }

        $properties = $query->orderByDesc('id')->paginate(9)->withQueryString();

        $propertyIds = $properties->pluck('id')->map(fn ($id) => (int) $id)->all();
        $coverImages = empty($propertyIds)
            ? collect()
            : CoverImage::whereIn('property_id', $propertyIds)->get()->keyBy('property_id');
        $addressRows = empty($propertyIds)
            ? collect()
            : PropertyAddress::whereIn('property_id', $propertyIds)->get()->groupBy('property_id');
        $categoryMap = Category::pluck('name', 'id')->all();
        $typeMap = Type::pluck('name', 'id')->all();
        $stateLabels = State::pluck('name', 'id')->all();
        $stateLabels[4] = $stateLabels[4] ?? 'Publicado';
        $stateLabels[5] = $stateLabels[5] ?? 'Inactivo';

        $ownerIds = $isAdmin ? $properties->pluck('user_id')->filter()->unique()->values()->all() : [];
        $owners = empty($ownerIds) ? collect() : User::whereIn('id', $ownerIds)->get()->keyBy('id');

        $properties->getCollection()->transform(function (Property $property) use ($coverImages, $addressRows, $categoryMap, $typeMap, $stateLabels, $owners, $isAdmin) {
            $address = $addressRows->get($property->id)?->first();
            $price = $property->sale_price ?: $property->rental_price;
            $owner = $isAdmin ? $owners->get($property->user_id) : null;
            $ownerName = '';
            if ($owner) {
                $ownerName = trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? ''));
                if ($ownerName === '') {
                    $ownerName = $owner->user_name ?? '';
                }
            }

            return [
                'id' => $property->id,
                'reference' => $property->reference,
                'title' => $property->title ?: 'Sin titulo',
                'category' => $categoryMap[$property->category_id] ?? 'Sin categoria',
                'type' => $typeMap[$property->type_id] ?? 'Sin tipo',
                'price' => $price,
                'meters' => $property->meters_built,
                'address' => $address?->address ?? '',
                'city' => $address?->city ?? '',
                'image' => $coverImages->get($property->id)?->url ?? null,
                'state_id' => (int) $property->state_id,
                'state_label' => $stateLabels[$property->state_id] ?? 'Sin estado',
                'updated_at' => $property->updated_at ? $property->updated_at->format('d/m/Y') : '',
                'owner' => $ownerName,
            ];
        });

        $categoryOptions = Category::orderBy('name')->get(['id', 'name']);
        $typeOptions = Type::orderBy('name')->get(['id', 'name']);
        $statusOptions = [
            '4' => $stateLabels[4] ?? 'Publicado',
            '5' => $stateLabels[5] ?? 'Inactivo',
        ];
        foreach ($stateLabels as $key => $label) {
            if (! isset($statusOptions[(string) $key])) {
                $statusOptions[(string) $key] = $label;
            }
        }

        $partnerTypeOptions = collect();
        $partnerUsersByType = [];
        if ($isAdmin) {
            $partnerTypeOptions = UserLevel::query()
                ->whereIn('id', $partnerLevelIds)
                ->orderBy('id')
                ->get(['id', 'name']);

            $partnerUsers = User::query()
                ->whereIn('user_level_id', $partnerLevelIds)
                ->orderBy('user_name')
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(['id', 'user_level_id', 'first_name', 'last_name', 'user_name', 'email']);

            foreach ($partnerUsers as $partnerUser) {
                $partnerName = trim((string) ($partnerUser->user_name ?? ''));
                if ($partnerName === '') {
                    $partnerName = trim(($partnerUser->first_name ?? '') . ' ' . ($partnerUser->last_name ?? ''));
                }
                if ($partnerName === '') {
                    $partnerName = trim((string) ($partnerUser->email ?? ''));
                }

                $levelKey = (string) ((int) $partnerUser->user_level_id);
                if (! isset($partnerUsersByType[$levelKey])) {
                    $partnerUsersByType[$levelKey] = [];
                }

                $partnerUsersByType[$levelKey][] = [
                    'id' => (int) $partnerUser->id,
                    'name' => $partnerName,
                ];
            }
        }

        return view('post.my_posts', [
            'user' => $user,
            'userLevelName' => $userLevelName,
            'isAdmin' => $isAdmin,
            'isCompanyUser' => $isCompanyUser,
            'activeNav' => 'properties',
            'properties' => $properties,
            'filters' => $filters,
            'categoryOptions' => $categoryOptions,
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
            'partnerTypeOptions' => $partnerTypeOptions,
            'partnerUsersByType' => $partnerUsersByType,
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->canManageProperties()) {
            return redirect()
                ->to('/post/services')
                ->with('error', 'Tu cuenta no puede crear propiedades.');
        }

        if ($addressValidation = $this->validateResolvedPropertyAddress($request)) {
            return $addressValidation;
        }

        $title = trim((string) $request->input('title', ''));
        $typeId = (int) ($request->input('type_id') ?: $request->input('type'));
        $categoryId = $request->filled('category') ? (int) $request->input('category') : null;
        $pageUrl = trim((string) $request->input('page_url', ''));

        if ($title === '' || $typeId <= 0) {
            return redirect()
                ->back()
                ->with('error', 'Completa los campos obligatorios de la propiedad.')
                ->withInput();
        }

        $property = Property::create([
            'reference' => $this->generatePropertyReference(),
            'title' => $title,
            'type_id' => $typeId,
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'state_id' => 4,
            'user_id' => (int) $user->id,
            'address' => trim((string) $request->input('address', '')) ?: null,
            'page_url' => $pageUrl !== '' ? $pageUrl : null,
        ]);

        $request->merge([
            'property_id' => (int) $property->id,
            'type' => $typeId,
        ]);
        $request->attributes->set('property_id', (int) $property->id);
        $request->attributes->set('property_model', $property);
        $request->attributes->set('success_status', 'Creado correctamente');

        return $this->update($request);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->canManageProperties()) {
            return redirect()
                ->to('/post/services')
                ->with('error', 'Tu cuenta no puede editar propiedades.');
        }

        $propertyFromAttributes = $request->attributes->get('property_model');
        $propertyId = (int) ($request->input('property_id') ?: $request->attributes->get('property_id'));
        if (! $propertyId) {
            return redirect()->back();
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if ($propertyFromAttributes instanceof Property && (int) $propertyFromAttributes->id === $propertyId) {
            $property = $propertyFromAttributes;
            if (! $isAdmin && (int) $property->user_id !== (int) $user->id) {
                $property = null;
            }
        } else {
            $propertyQuery = Property::where('id', $propertyId);
            if (! $isAdmin) {
                $propertyQuery->where('user_id', $user->id);
            }
            $property = $propertyQuery->first();
        }
        if (! $property) {
            return redirect()
                ->to('/post/my_posts')
                ->with('status', 'Ocurrio un error interno');
        }

        if ($addressValidation = $this->validateResolvedPropertyAddress($request)) {
            return $addressValidation;
        }

        $dataForDb = [];
        $typeId = $request->input('type');
        $locality = $request->input('locality');
        $number = $request->input('number');
        $escBlock = $request->input('esc_block');
        $door = $request->input('door');
        $nameUrbanization = $request->input('name_urbanization');
        $visibilityInPortalsId = $request->input('visibility_in_portals');
        $typologyId = $request->input('typology');
        $plotMeters = $request->input('plot_meters');
        $numberOfPlants = $request->input('number_of_plants');
        $energyClassId = $request->input('energy_class');
        $energyConsumption = $request->input('energy_consumption');
        $emissionsRatingId = $request->input('emissions_rating');
        $emissionsConsumption = $request->input('emissions_consumption');
        $stateConservationId = $request->input('state_conservation');
        $orientation = $request->input('orientation');
        $outdoorWheelchair = $request->input('outdoor_wheelchair');
        $interiorWheelchair = $request->input('interior_wheelchair');
        $typeHeatingId = $request->input('type_heating');
        $pageUrl = $request->input('page_url');
        $title = $request->input('title');
        $description = $request->input('description');
        $categoryId = $request->input('category');
        $metersBuilt = $request->input('meters_built');
        $usefulMeters = $request->input('useful_meters');
        $salePrice = $request->input('sale_price');
        $rentalPrice = $request->input('rental_price');
        $communityExpenses = $request->input('community_expenses');
        $yearOfConstruction = $request->input('year_of_construction');
        $bedrooms = $request->input('bedrooms');
        $bathrooms = $request->input('bathrooms');
        $parking = $request->input('parking');
        $feature = $request->input('feature');
        $countryId = $request->input('country_id', $request->input('country'));
        $cityId = $request->input('city_id', $request->input('city'));
        $provinceId = $request->input('province_id', $request->input('province'));
        $addressValue = $request->input('address');
        $closeTo = $request->input('close_to');
        $zipCode = $request->input('zip_code', $request->input('postal_code'));
        $rentalTypeId = $request->input('rental_type');
        $contactOptionId = $request->input('contact_option');
        $powerConsumptionRatingId = $request->input('power_consumption_rating');
        $reasonForSaleId = $request->input('reason_for_sale');
        $rooms = $request->input('rooms');
        $elevator = $request->input('elevator');
        $plantId = $request->input('plant');
        $doorId = $request->input('door_id');
        $typeFloor = $request->input('type_floor');
        $appropriateForChildren = $request->input('appropriate_for_children');
        $petFriendly = $request->input('pet_friendly');
        $maxNumTenants = $request->input('max_num_tenants');
        $bankOwnedProperty = $request->input('bank_owned_property');
        $guarantee = $request->input('guarantee');
        $ibi = $request->input('ibi');
        $mortgageRate = $request->input('mortgage_rate');
        $wheelchairAccessibleElevator = $request->input('wheelchair_accessible_elevator');
        $facadeId = $request->input('facade');
        $equipment = $request->input('equipment');
        $noNumber = $request->input('no-number');
        $plazaCapacityId = $request->input('plaza_capacity');
        $linearMetersOfFacade = $request->input('linear_meters_of_facade');
        $stays = $request->input('stays');
        $numberOfShopWindows = $request->input('number_of_shop_windows');
        $hasTenants = $request->input('has_tenants');
        $landSize = $request->input('land_size');
        $nearestMunicipalityDistanceId = $request->input('nearest_municipality_distance');
        $wheeledAccessId = $request->input('wheeled_access');
        $typeOfTerrainId = $request->input('type_of_terrain');
        $terrainUseId = $request->input('terrain_use');
        $terrainQualification = $request->input('terrain_qualification');
        $heatingFuelId = $request->input('heating_fuel');
        $mLong = $request->input('m_long');
        $mWide = $request->input('m_wide');
        $locationPremisesId = $request->input('location_premises');
        $garagePriceCategoryId = $request->input('garage_price_category');
        $garagePrice = $request->input('garage_price');

        $address = $request->input('address');
        $city = $request->input('city');
        $postalCode = $request->input('postal_code');
        $province = $request->input('province');
        $country = $request->input('country');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (! empty($garagePrice)) {
            $dataForDb['garage_price'] = $garagePrice;
        }
        $this->setPositiveIntegerField($dataForDb, 'garage_price_category_id', $garagePriceCategoryId);
        $this->setPositiveIntegerField($dataForDb, 'location_premises_id', $locationPremisesId);
        if ($request->exists('m_long')) {
            $dataForDb['m_long'] = $this->normalizeDecimalValue($mLong);
        }
        if ($request->exists('m_wide')) {
            $dataForDb['m_wide'] = $this->normalizeDecimalValue($mWide);
        }
        $this->setPositiveIntegerField($dataForDb, 'heating_fuel_id', $heatingFuelId);
        if (! empty($landSize)) {
            $dataForDb['land_size'] = str_replace('.', '', $landSize);
        }
        $this->setPositiveIntegerField($dataForDb, 'nearest_municipality_distance_id', $nearestMunicipalityDistanceId);
        $this->setPositiveIntegerField($dataForDb, 'wheeled_access_id', $wheeledAccessId);
        $this->setPositiveIntegerField($dataForDb, 'type_of_terrain_id', $typeOfTerrainId);
        $this->setPositiveIntegerField($dataForDb, 'terrain_use_id', $terrainUseId);
        if ($request->has('linear_meters_of_facade')) {
            $linearMetersOfFacadeValue = trim((string) $linearMetersOfFacade);
            $dataForDb['linear_meters_of_facade'] = $linearMetersOfFacadeValue !== '' ? $linearMetersOfFacadeValue : null;
        }
        if (! empty($stays)) {
            $dataForDb['stays'] = $stays;
        }
        if (! empty($numberOfShopWindows)) {
            $dataForDb['number_of_shop_windows'] = $numberOfShopWindows;
        }
        if (! empty($hasTenants)) {
            $dataForDb['has_tenants'] = $hasTenants;
        }
        $this->setPositiveIntegerField($dataForDb, 'plaza_capacity_id', $plazaCapacityId);
        if (! empty($appropriateForChildren)) {
            $dataForDb['appropriate_for_children'] = $appropriateForChildren;
        }
        if (! empty($petFriendly)) {
            $dataForDb['pet_friendly'] = $petFriendly;
        }
        if (! empty($maxNumTenants)) {
            $dataForDb['max_num_tenants'] = str_replace('.', '', $maxNumTenants);
        }
        if (! empty($bankOwnedProperty)) {
            $dataForDb['bank_owned_property'] = $bankOwnedProperty;
        }
        if (! empty($guarantee)) {
            $dataForDb['guarantee'] = $guarantee;
        }
        if (! empty($ibi)) {
            $dataForDb['ibi'] = $ibi;
        }
        if (! empty($mortgageRate)) {
            $dataForDb['mortgage_rate'] = $mortgageRate;
        }
        if (! empty($wheelchairAccessibleElevator)) {
            $dataForDb['wheelchair_accessible_elevator'] = $wheelchairAccessibleElevator;
        }
        $this->setPositiveIntegerField($dataForDb, 'facade_id', $facadeId);
        if (! empty($rooms)) {
            $dataForDb['rooms'] = str_replace('.', '', $rooms);
        }
        if (! empty($elevator)) {
            $dataForDb['elevator'] = $elevator;
        }
        $this->setPositiveIntegerField($dataForDb, 'plant_id', $plantId);
        $this->setPositiveIntegerField($dataForDb, 'door_id', $doorId);
        $this->setPositiveIntegerField($dataForDb, 'type_id', $typeId);
        if (! empty($locality)) {
            $dataForDb['locality'] = $locality;
        }
        if (! empty($number)) {
            $dataForDb['number'] = $number;
        } elseif (! empty($noNumber)) {
            $dataForDb['number'] = $noNumber;
        }
        if (! empty($escBlock)) {
            $dataForDb['esc_block'] = $escBlock;
        }
        if (! empty($door)) {
            $dataForDb['door'] = $door;
        }
        if (! empty($nameUrbanization)) {
            $dataForDb['name_urbanization'] = $nameUrbanization;
        }
        $this->setPositiveIntegerField($dataForDb, 'visibility_in_portals_id', $visibilityInPortalsId);
        $this->setPositiveIntegerField($dataForDb, 'typology_id', $typologyId);
        if (! empty($plotMeters)) {
            $dataForDb['plot_meters'] = str_replace('.', '', $plotMeters);
        }
        if (! empty($numberOfPlants)) {
            $dataForDb['number_of_plants'] = str_replace('.', '', $numberOfPlants);
        }
        $this->setPositiveIntegerField($dataForDb, 'energy_class_id', $energyClassId);
        if (! empty($energyConsumption)) {
            $dataForDb['energy_consumption'] = $energyConsumption;
        }
        $this->setPositiveIntegerField($dataForDb, 'emissions_rating_id', $emissionsRatingId);
        if (! empty($emissionsConsumption)) {
            $dataForDb['emissions_consumption'] = $emissionsConsumption;
        }
        $this->setPositiveIntegerField($dataForDb, 'state_conservation_id', $stateConservationId);
        if (! empty($outdoorWheelchair)) {
            $dataForDb['outdoor_wheelchair'] = $outdoorWheelchair;
        }
        if (! empty($interiorWheelchair)) {
            $dataForDb['interior_wheelchair'] = $interiorWheelchair;
        }
        $this->setPositiveIntegerField($dataForDb, 'type_heating_id', $typeHeatingId);
        if (! empty($pageUrl)) {
            $dataForDb['page_url'] = $pageUrl;
        }
        if (! empty($title)) {
            $dataForDb['title'] = $title;
        }
        if (! empty($description)) {
            $dataForDb['description'] = $description;
        }
        $this->setPositiveIntegerField($dataForDb, 'category_id', $categoryId);
        if (! empty($metersBuilt)) {
            $dataForDb['meters_built'] = str_replace('.', '', $metersBuilt);
        }
        if (! empty($usefulMeters)) {
            $dataForDb['useful_meters'] = str_replace('.', '', $usefulMeters);
        }
        if (! empty($salePrice)) {
            $dataForDb['sale_price'] = str_replace('.', '', $salePrice);
        }
        if (! empty($rentalPrice)) {
            $dataForDb['rental_price'] = str_replace('.', '', $rentalPrice);
        }
        if (! empty($communityExpenses)) {
            $dataForDb['community_expenses'] = str_replace('.', '', $communityExpenses);
        }
        if (! empty($yearOfConstruction)) {
            $dataForDb['year_of_construction'] = $yearOfConstruction;
        }
        if (! empty($bedrooms)) {
            $dataForDb['bedrooms'] = str_replace('.', '', $bedrooms);
        }
        if (! empty($bathrooms)) {
            $dataForDb['bathrooms'] = str_replace('.', '', $bathrooms);
        }
        if (! empty($parking)) {
            $dataForDb['parking'] = $parking;
        }
        $this->setPositiveIntegerField($dataForDb, 'country_id', $countryId);
        $this->setPositiveIntegerField($dataForDb, 'city_id', $cityId);
        $this->setPositiveIntegerField($dataForDb, 'province_id', $provinceId);
        if (! empty($addressValue)) {
            $dataForDb['address'] = $addressValue;
        }
        if (! empty($closeTo)) {
            $dataForDb['close_to'] = $closeTo;
        }
        if (! empty($zipCode)) {
            $dataForDb['zip_code'] = $zipCode;
        }
        $this->setPositiveIntegerField($dataForDb, 'rental_type_id', $rentalTypeId);
        $this->setPositiveIntegerField($dataForDb, 'contact_option_id', $contactOptionId);
        $this->setPositiveIntegerField($dataForDb, 'power_consumption_rating_id', $powerConsumptionRatingId);
        $this->setPositiveIntegerField($dataForDb, 'reason_for_sale_id', $reasonForSaleId);

        Property::where('id', $propertyId)->update($dataForDb);

        PropertyAddress::updateOrCreate(
            ['property_id' => $propertyId],
            [
                'address' => $address ?? '',
                'city' => $city ?? '',
                'province' => $province ?? '',
                'postal_code' => $postalCode ?? '',
                'country' => $country ?? '',
                'latitude' => $latitude ?? '',
                'longitude' => $longitude ?? '',
            ]
        );

        if (! empty($equipment)) {
            Equipments::where('property_id', $propertyId)->delete();
            foreach ($equipment as $value) {
                Equipments::create([
                    'property_id' => $propertyId,
                    'equipment_id' => $value,
                ]);
            }
        }
        if (! empty($feature) || $request->exists('terrain_feature_present')) {
            Features::where('property_id', $propertyId)->delete();
            foreach ((array) $feature as $value) {
                Features::create([
                    'property_id' => $propertyId,
                    'feature_id' => $value,
                ]);
            }
        }
        if (! empty($terrainQualification) || $request->exists('terrain_qualification_present')) {
            TerrainQualifications::where('property_id', $propertyId)->delete();
            foreach ((array) $terrainQualification as $value) {
                TerrainQualifications::create([
                    'property_id' => $propertyId,
                    'terrain_qualification_id' => $value,
                ]);
            }
        }
        if (! empty($typeFloor)) {
            TypesFloors::where('property_id', $propertyId)->delete();
            foreach ($typeFloor as $value) {
                TypesFloors::create([
                    'property_id' => $propertyId,
                    'type_floor_id' => $value,
                ]);
            }
        }
        if (! empty($orientation)) {
            Orientations::where('property_id', $propertyId)->delete();
            foreach ($orientation as $value) {
                Orientations::create([
                    'property_id' => $propertyId,
                    'orientation_id' => $value,
                ]);
            }
        }

        $imagePath = public_path('img/uploads');
        $videoPath = public_path('video/uploads');
        if (! is_dir($imagePath)) {
            @mkdir($imagePath, 0755, true);
        }
        if (! is_dir($videoPath)) {
            @mkdir($videoPath, 0755, true);
        }

        $existingCoverImage = CoverImage::where('property_id', $propertyId)->first();
        $deleteCoverImage = (int) $request->input('delete_cover_image', 0) === 1;

        if ($deleteCoverImage && $existingCoverImage) {
            $this->deleteStoredFile('img/uploads', $existingCoverImage->url);
            $existingCoverImage->delete();
            $existingCoverImage = null;
        }

        $coverImage = $request->file('cover_image');
        if ($coverImage && $coverImage->isValid()) {
            $storedImage = $this->storeUploadedImage($coverImage, $imagePath);
            if (! $storedImage['success']) {
                return redirect()->back()->with('error', $storedImage['error']);
            }

            CoverImage::updateOrCreate(
                ['property_id' => $propertyId],
                ['url' => $storedImage['file_name']]
            );

            if ($existingCoverImage && $existingCoverImage->url !== $storedImage['file_name']) {
                $this->deleteStoredFile('img/uploads', $existingCoverImage->url);
            }
        }

        $moreImages = $request->file('more_images', []);
        if (! empty($moreImages)) {
            foreach ((array) $moreImages as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $storedImage = $this->storeUploadedImage($file, $imagePath);
                if (! $storedImage['success']) {
                    return redirect()->back()->with('error', $storedImage['error']);
                }

                MoreImage::create([
                    'url' => $storedImage['file_name'],
                    'property_id' => $propertyId,
                ]);
            }
        }

        $existingVideo = Video::where('property_id', $propertyId)->first();
        $video = $request->file('video');
        if ($video) {
            $storedVideo = $this->storeUploadedVideo($video, $videoPath);
            if (! $storedVideo['success']) {
                return redirect()->back()->with('error', $storedVideo['error']);
            }

            Video::updateOrCreate(
                ['property_id' => $propertyId],
                ['url' => $storedVideo['file_name']]
            );

            if ($existingVideo && $existingVideo->url !== $storedVideo['file_name']) {
                $this->deleteStoredFile('video/uploads', $existingVideo->url);
            }
        }

        $this->deleteOwnedMoreImages('property_id', $propertyId, $request->input('delete_more_images', []));

        return redirect()
            ->to('/post/my_posts')
            ->with('status', $request->attributes->get('success_status', 'Actualizado correctamente'));
    }

    public function updateForm(string $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->canManageProperties()) {
            return redirect()
                ->to('/post/services')
                ->with('status', 'Tu cuenta no puede editar propiedades.');
        }

        $isAdmin = (int) $user->user_level_id === 1;
        $propertyQuery = Property::where('id', $id);
        if (! $isAdmin) {
            $propertyQuery->where('user_id', $user->id);
        }
        $property = $propertyQuery->get()->toArray();
        if (empty($property)) {
            return redirect()
                ->to('/post/my_posts')
                ->with('status', 'Ocurrio un error interno');
        }

        $propertyAddress = PropertyAddress::where('property_id', $id)->get()->toArray();
        if (empty($propertyAddress)) {
            $propertyAddress = [[
                'address' => '',
                'city' => '',
                'province' => '',
                'postal_code' => '',
                'country' => '',
                'latitude' => '',
                'longitude' => '',
            ]];
        }

        $category = $this->normalizeLegacyCatalog(Category::all()->toArray());
        $city = City::all()->toArray();
        $country = Country::all()->toArray();
        $feature = $this->normalizeLegacyCatalog(Feature::all()->toArray());
        $province = Province::all()->toArray();
        $state = State::all()->toArray();
        $type = Type::all()->toArray();
        $userLevel = UserLevel::all()->toArray();
        $typeId = (int) $property[0]['type_id'];
        $typology = $typeId === 15
            ? $this->normalizeLegacyCatalog(Typology::where('type_id', 15)->get()->toArray())
            : $this->normalizeLegacyCatalog(Typology::where('type_id', 1)->get()->toArray());
        $orientation = $this->normalizeLegacyCatalog(Orientation::all()->toArray());
        $typeHeating = $this->normalizeLegacyCatalog(TypeHeating::all()->toArray());
        $emissionsRating = $this->normalizeLegacyCatalog(EmissionsRating::all()->toArray());
        $energyClass = $this->normalizeLegacyCatalog(EnergyClass::all()->toArray());
        $stateConservation = $this->normalizeLegacyCatalog(StateConservation::all()->toArray());
        $visibilityInPortals = $this->normalizeLegacyCatalog(VisibilityInPortals::all()->toArray());
        $rentalType = $this->normalizeLegacyCatalog(RentalType::all()->toArray());
        $contactOption = $this->normalizeLegacyCatalog(ContactOption::all()->toArray());
        $powerConsumptionRating = $this->normalizeLegacyCatalog(PowerConsumptionRating::all()->toArray());
        $reasonForSale = $this->normalizeLegacyCatalog(ReasonForSale::all()->toArray());
        $plant = $this->normalizeLegacyCatalog(Plant::all()->toArray());
        $doorOptions = Door::all()->toArray();
        $typeFloor = $this->normalizeLegacyCatalog(TypeFloor::all()->toArray());
        $facade = $this->normalizeLegacyCatalog(Facade::all()->toArray());
        $plazaCapacity = $this->normalizeLegacyCatalog(PlazaCapacity::all()->toArray());
        $selectedTerrainTypeId = isset($property[0]['type_of_terrain_id']) ? (int) $property[0]['type_of_terrain_id'] : null;
        $typeOfTerrain = $this->normalizeTerrainTypeCatalog($selectedTerrainTypeId);
        $terrainUse = $this->normalizeTerrainUseCatalog();
        $terrainQualification = $this->normalizeTerrainQualificationCatalog();
        $wheeledAccess = $this->normalizeLegacyCatalog(WheeledAccess::all()->toArray());
        $nearestMunicipalityDistance = $this->normalizeLegacyCatalog(NearestMunicipalityDistance::all()->toArray());
        $heatingFuel = $this->normalizeLegacyCatalog(HeatingFuel::all()->toArray());
        $locationPremises = $this->normalizeLegacyCatalog(LocationPremises::all()->toArray());
        $garagePriceCategory = $this->normalizeLegacyCatalog(GaragePriceCategory::all()->toArray());

        $typesFloors = TypesFloors::where('property_id', $id)->get()->toArray();
        $equipments = Equipments::where('property_id', $id)->get()->toArray();
        $coverImage = CoverImage::where('property_id', $id)->get()->toArray();
        $moreImages = MoreImage::where('property_id', $id)->get()->toArray();
        $video = Video::where('property_id', $id)->get()->toArray();
        $orientations = Orientations::where('property_id', $id)->get()->toArray();
        $features = Features::where('property_id', $id)->get()->toArray();
        $terrainQualifications = TerrainQualifications::where('property_id', $id)->get()->toArray();

        $equipment = $this->normalizeLegacyCatalog(Equipment::all()->toArray());
        $formView = 'post.forms.form_1_update';

        if ($typeId === 1) {
            $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 1)->get()->toArray());
            $formView = 'post.forms.form_1_update';
        } elseif ($typeId === 13) {
            $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 1)->get()->toArray());
            $formView = 'post.forms.form_2_update';
        } elseif ($typeId === 4) {
            $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 4)->get()->toArray());
            $formView = 'post.forms.form_3_update';
        } elseif ($typeId === 14) {
            $feature = $this->normalizeLegacyCatalog(Feature::where('id_type', 14)->get()->toArray());
            $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 14)->get()->toArray());
            $formView = 'post.forms.form_4_update';
        } elseif ($typeId === 9) {
            $feature = $this->normalizeLegacyCatalog(Feature::where('id_type', 9)->get()->toArray());
            $equipment = $this->normalizeLegacyCatalog(Equipment::where('type_id', 4)->get()->toArray());
            $formView = 'post.forms.form_5_update';
        } elseif ($typeId === 15) {
            $formView = 'post.forms.form_casa_rustica_update';
        }

        return view($formView, [
            'user' => $user,
            'userLevelName' => UserLevel::find($user->user_level_id)?->name ?? 'Usuario',
            'isAdmin' => $isAdmin,
            'activeNav' => 'properties',
            'mapsKey' => config('services.google.maps_key'),
            'propertyAddress' => $propertyAddress,
            'category' => $category,
            'city' => $city,
            'country' => $country,
            'coverImage' => $coverImage,
            'feature' => $feature,
            'features' => $features,
            'terrainQualification' => $terrainQualification,
            'terrainQualifications' => $terrainQualifications,
            'moreImages' => $moreImages,
            'property' => $property,
            'province' => $province,
            'state' => $state,
            'type' => $type,
            'userLevel' => $userLevel,
            'typology' => $typology,
            'orientation' => $orientation,
            'orientations' => $orientations,
            'typeHeating' => $typeHeating,
            'emissionsRating' => $emissionsRating,
            'energyClass' => $energyClass,
            'stateConservation' => $stateConservation,
            'visibilityInPortals' => $visibilityInPortals,
            'rentalType' => $rentalType,
            'contactOption' => $contactOption,
            'powerConsumptionRating' => $powerConsumptionRating,
            'reasonForSale' => $reasonForSale,
            'plant' => $plant,
            'door' => $doorOptions,
            'typeFloor' => $typeFloor,
            'typesFloors' => $typesFloors,
            'facade' => $facade,
            'equipment' => $equipment,
            'equipments' => $equipments,
            'plazaCapacity' => $plazaCapacity,
            'typeOfTerrain' => $typeOfTerrain,
            'terrainUse' => $terrainUse,
            'wheeledAccess' => $wheeledAccess,
            'nearestMunicipalityDistance' => $nearestMunicipalityDistance,
            'video' => $video,
            'heatingFuel' => $heatingFuel,
            'locationPremises' => $locationPremises,
            'garagePriceCategory' => $garagePriceCategory,
        ]);
    }

    public function delete(Request $request)
    {
        $user = Auth::user();
        $propertyId = (int) $request->query('id');

        if (! $user) {
            return response()->json(['status' => 401]);
        }

        if (! $user->canManageProperties()) {
            return response()->json(['status' => 403, 'message' => 'Tu cuenta no puede eliminar propiedades.'], 403);
        }

        $property = Property::find($propertyId);
        if (! $property) {
            return response()->json(['status' => 404]);
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin && (int) $property->user_id !== (int) $user->id) {
            return response()->json(['status' => 403]);
        }

        CoverImage::where('property_id', $propertyId)->delete();
        MoreImage::where('property_id', $propertyId)->delete();
        Video::where('property_id', $propertyId)->delete();
        PropertyAddress::where('property_id', $propertyId)->delete();
        Features::where('property_id', $propertyId)->delete();
        Equipments::where('property_id', $propertyId)->delete();
        Orientations::where('property_id', $propertyId)->delete();
        TypesFloors::where('property_id', $propertyId)->delete();

        $property->delete();

        return response()->json(['status' => 200]);
    }

    public function disabledEnabled(Request $request)
    {
        $user = Auth::user();
        $propertyId = (int) $request->query('id');

        if (! $user) {
            return response()->json(['status' => 401]);
        }

        if (! $user->canManageProperties()) {
            return response()->json(['status' => 403, 'message' => 'Tu cuenta no puede cambiar estado de propiedades.'], 403);
        }

        $property = Property::find($propertyId);
        if (! $property) {
            return response()->json(['status' => 404]);
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin && (int) $property->user_id !== (int) $user->id) {
            return response()->json(['status' => 403]);
        }

        $property->state_id = (int) $property->state_id === 5 ? 4 : 5;
        $property->save();

        return response()->json([
            'status' => 200,
            'state_id' => $property->state_id,
        ]);
    }

    public function deleteMoreImage(Request $request)
    {
        $user = Auth::user();
        $imageId = (int) $request->query('id');

        if (! $user) {
            return response()->json(['status' => 401]);
        }

        if (! $imageId) {
            return response()->json(['status' => 400]);
        }

        $image = MoreImage::find($imageId);
        if (! $image) {
            return response()->json(['status' => 404]);
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! empty($image->property_id)) {
            $property = Property::find($image->property_id);
            if (! $property) {
                return response()->json(['status' => 404]);
            }
            if (! $isAdmin && (int) $property->user_id !== (int) $user->id) {
                return response()->json(['status' => 403]);
            }
        } elseif (! empty($image->service_id)) {
            $service = Service::find($image->service_id);
            if (! $service) {
                return response()->json(['status' => 404]);
            }
            if (! $isAdmin && (int) $service->user_id !== (int) $user->id) {
                return response()->json(['status' => 403]);
            }
        } elseif (! $isAdmin) {
            return response()->json(['status' => 403]);
        }

        $filePath = public_path('img/uploads/' . $image->url);
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        $image->delete();

        return response()->json(['status' => 200]);
    }

    public function services()
    {
        $user = Auth::user();
        $isAdmin = $user && (int) $user->user_level_id === 1;
        $userLevelName = $user ? (UserLevel::find($user->user_level_id)?->name ?? 'Usuario') : 'Usuario';

        if ($isAdmin) {
            $request = request();
            $filters = [
                'q' => trim((string) $request->query('q', '')),
            ];

            $query = User::where('user_level_id', 4);
            if ($filters['q'] !== '') {
                $search = $filters['q'];
                $query->where(function ($builder) use ($search) {
                    $builder->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('user_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('landline_phone', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%');
                });
            }

            $providers = $query->orderByDesc('id')->paginate(15)->withQueryString();
            $providerIds = $providers->pluck('id')->map(fn ($id) => (int) $id)->all();
            $addressRows = empty($providerIds)
                ? collect()
                : UserAddress::whereIn('user_id', $providerIds)->get()->groupBy('user_id');
            $levelMap = UserLevel::pluck('name', 'id')->all();

            $providers->getCollection()->transform(function (User $provider) use ($addressRows, $levelMap) {
                $address = $addressRows->get($provider->id)?->first();
                $name = trim(($provider->first_name ?? '') . ' ' . ($provider->last_name ?? ''));
                if ($name === '') {
                    $name = $provider->user_name ?: ($provider->email ?: 'Proveedor');
                }

                $addressParts = [];
                $baseAddress = $address?->address ?: ($provider->address ?? '');
                if ($baseAddress) {
                    $addressParts[] = $baseAddress;
                }
                if ($address?->city) {
                    $addressParts[] = $address->city;
                }
                if ($address?->province) {
                    $addressParts[] = $address->province;
                }
                if ($address?->country) {
                    $addressParts[] = $address->country;
                }

                return [
                    'id' => $provider->id,
                    'name' => $name,
                    'level' => $levelMap[$provider->user_level_id] ?? 'Proveedor de servicio',
                    'email' => $provider->email ?? '',
                    'phone' => $provider->phone ?? '',
                    'landline_phone' => $provider->landline_phone ?? '',
                    'address' => trim(implode(', ', $addressParts)),
                    'is_active' => (int) ($provider->is_active ?? 1),
                ];
            });

            return view('post.providers', [
                'user' => $user,
                'userLevelName' => $userLevelName,
                'isAdmin' => $isAdmin,
                'activeNav' => 'services',
                'providers' => $providers,
                'filters' => $filters,
            ]);
        }

        $request = request();
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'type' => (string) $request->query('type', ''),
            'ds' => (string) $request->query('ds', ''),
            'de' => (string) $request->query('de', ''),
        ];

        $query = Service::query();
        if (! $isAdmin && $user) {
            $query->where('user_id', $user->id);
        }

        if ($filters['q'] !== '') {
            $search = $filters['q'];
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($filters['type'] !== '' && $filters['type'] !== 'all') {
            $serviceIds = ServiceTypeLink::where('service_type_id', (int) $filters['type'])
                ->pluck('service_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (! empty($serviceIds)) {
                $query->whereIn('id', $serviceIds);
            } else {
                $query->where('id', 0);
            }
        }

        if ($filters['ds'] !== '') {
            $query->whereDate('created_at', '>=', $filters['ds']);
        }

        if ($filters['de'] !== '') {
            $query->whereDate('created_at', '<=', $filters['de']);
        }

        $services = $query->orderByDesc('id')->paginate(9)->withQueryString();

        $serviceIds = $services->pluck('id')->map(fn ($id) => (int) $id)->all();
        $coverImages = empty($serviceIds)
            ? collect()
            : CoverImage::whereIn('service_id', $serviceIds)->get()->keyBy('service_id');
        $serviceAddresses = empty($serviceIds)
            ? collect()
            : ServiceAddress::whereIn('service_id', $serviceIds)->get()->keyBy('service_id');
        $serviceVideos = empty($serviceIds)
            ? collect()
            : Video::whereIn('service_id', $serviceIds)->get()->keyBy('service_id');
        $serviceTypeLinks = empty($serviceIds)
            ? collect()
            : ServiceTypeLink::whereIn('service_id', $serviceIds)->get()->groupBy('service_id');
        $serviceTypeMap = ServiceType::pluck('name', 'id')->all();

        $ownerIds = $services->pluck('user_id')->filter()->unique()->values()->all();
        $owners = empty($ownerIds) ? collect() : User::whereIn('id', $ownerIds)->get()->keyBy('id');
        $ownerAddresses = empty($ownerIds)
            ? collect()
            : UserAddress::whereIn('user_id', $ownerIds)->get()->groupBy('user_id');

        $services->getCollection()->transform(function (Service $service) use ($coverImages, $serviceAddresses, $serviceVideos, $serviceTypeLinks, $serviceTypeMap, $owners, $ownerAddresses, $isAdmin) {
            $links = $serviceTypeLinks->get($service->id) ?? collect();
            $typeNames = [];
            foreach ($links as $link) {
                $typeId = (int) $link->service_type_id;
                if (isset($serviceTypeMap[$typeId])) {
                    $typeNames[] = $serviceTypeMap[$typeId];
                }
            }

            $owner = $owners->get($service->user_id);
            $ownerName = '';
            if ($owner) {
                $ownerName = trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? ''));
                if ($ownerName === '') {
                    $ownerName = $owner->user_name ?? '';
                }
            }

            $serviceAddress = $serviceAddresses->get($service->id);
            $serviceAddressParts = [];
            if ($serviceAddress?->address) {
                $serviceAddressParts[] = $serviceAddress->address;
            }
            if ($serviceAddress?->city) {
                $serviceAddressParts[] = $serviceAddress->city;
            }
            if ($serviceAddress?->province) {
                $serviceAddressParts[] = $serviceAddress->province;
            }
            if ($serviceAddress?->country) {
                $serviceAddressParts[] = $serviceAddress->country;
            }
            $serviceFullAddress = trim(implode(', ', $serviceAddressParts));

            $serviceVideo = $serviceVideos->get($service->id);
            $address = $ownerAddresses->get($service->user_id)?->first();

            return [
                'id' => $service->id,
                'title' => $service->title ?: 'Servicio',
                'description' => $service->description,
                'availability' => $service->availability,
                'image' => $coverImages->get($service->id)?->url ?? null,
                'types' => $typeNames,
                'owner' => $isAdmin ? $ownerName : '',
                'address' => $address?->address ?? '',
                'city' => $address?->city ?? '',
                'phone' => $owner?->phone ?? '',
                'page_url' => $service->page_url ?? '',
                'service_address' => $serviceAddress?->address ?? '',
                'service_city' => $serviceAddress?->city ?? '',
                'service_province' => $serviceAddress?->province ?? '',
                'service_country' => $serviceAddress?->country ?? '',
                'service_full_address' => $serviceFullAddress,
                'video' => $serviceVideo?->url ?? null,
                'updated_at' => $service->updated_at ? $service->updated_at->format('d/m/Y') : '',
            ];
        });

        $serviceTypeOptions = ServiceType::orderBy('name')->get(['id', 'name']);

        $isProviderView = $user ? $user->isServiceProvider() : false;
        $providerProfile = null;
        $providerServiceTypes = [];
        $providerLanding = null;
        if ($isProviderView && $user) {
            $profileAddress = UserAddress::where('user_id', $user->id)->first();
            $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            if ($name === '') {
                $name = $user->user_name ?: ($user->email ?: 'Proveedor');
            }

            $addressParts = [];
            $baseAddress = $profileAddress?->address ?: ($user->address ?? '');
            if ($baseAddress) {
                $addressParts[] = $baseAddress;
            }
            if ($profileAddress?->city) {
                $addressParts[] = $profileAddress->city;
            }
            if ($profileAddress?->province) {
                $addressParts[] = $profileAddress->province;
            }
            if ($profileAddress?->country) {
                $addressParts[] = $profileAddress->country;
            }

            $providerProfile = [
                'name' => $name,
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'landline_phone' => $user->landline_phone ?? '',
                'address' => trim(implode(', ', $addressParts)),
                'photo' => $user->photo ? asset('img/photo_profile/' . $user->photo) : asset('img/default-avatar-profile-icon.webp'),
            ];

            $providerServiceIds = Service::where('user_id', $user->id)->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (! empty($providerServiceIds)) {
                $typeIds = ServiceTypeLink::whereIn('service_id', $providerServiceIds)
                    ->pluck('service_type_id')
                    ->unique()
                    ->map(fn ($id) => (int) $id)
                    ->all();
                if (! empty($typeIds)) {
                    $providerServiceTypes = ServiceType::whereIn('id', $typeIds)->orderBy('name')->pluck('name')->all();
                }
            }

            $primaryService = $services->first();
            $heroImage = asset('img/image-icon-1280x960.png');
            $landingImages = [$heroImage];
            $serviceDescription = '';
            $serviceAvailability = '';
            $servicePageUrl = '';
            $serviceUpdatedAt = '';
            $serviceVideoUrl = '';
            $serviceAddressLabel = $providerProfile['address'] ?? '';

            if ($primaryService) {
                $landingImages = [];
                if (! empty($primaryService['image'])) {
                    $heroImage = asset('img/uploads/' . $primaryService['image']);
                    $landingImages[] = $heroImage;
                }
                $serviceDescription = $primaryService['description'] ?? '';
                $serviceAvailability = $primaryService['availability'] ?? '';
                $servicePageUrl = $primaryService['page_url'] ?? '';
                $serviceUpdatedAt = $primaryService['updated_at'] ?? '';
                if (! empty($primaryService['service_full_address'])) {
                    $serviceAddressLabel = $primaryService['service_full_address'];
                }
                if (! empty($primaryService['video'])) {
                    $serviceVideoUrl = asset('video/uploads/' . $primaryService['video']);
                }

                $galleryImages = MoreImage::where('service_id', (int) ($primaryService['id'] ?? 0))
                    ->orderBy('id')
                    ->pluck('url')
                    ->filter()
                    ->map(fn ($url) => asset('img/uploads/' . ltrim((string) $url, '/')))
                    ->values()
                    ->all();

                foreach ($galleryImages as $galleryImage) {
                    if (! in_array($galleryImage, $landingImages, true)) {
                        $landingImages[] = $galleryImage;
                    }
                }
            }

            if (empty($landingImages)) {
                $landingImages = [$heroImage];
            }

            $mapQuery = trim((string) $serviceAddressLabel);
            $mapLink = $mapQuery !== ''
                ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapQuery)
                : '';
            $mapEmbed = $mapQuery !== ''
                ? 'https://www.google.com/maps?q=' . urlencode($mapQuery) . '&output=embed'
                : '';

            $whatsappPhone = preg_replace('/\D+/', '', (string) ($providerProfile['phone'] ?? ''));
            $whatsappLink = $whatsappPhone !== '' ? 'https://wa.me/' . $whatsappPhone : '';

            $providerLanding = [
                'hero_image' => $heroImage,
                'images' => $landingImages,
                'address' => $serviceAddressLabel,
                'description' => $serviceDescription,
                'availability' => $serviceAvailability,
                'page_url' => $servicePageUrl,
                'updated_at' => $serviceUpdatedAt,
                'video_url' => $serviceVideoUrl,
                'map_link' => $mapLink,
                'map_embed' => $mapEmbed,
                'whatsapp_link' => $whatsappLink,
            ];
        }

        return view('post.services', [
            'user' => $user,
            'userLevelName' => $userLevelName,
            'isAdmin' => $isAdmin,
            'activeNav' => 'services',
            'services' => $services,
            'filters' => $filters,
            'serviceTypeOptions' => $serviceTypeOptions,
            'isProviderView' => $isProviderView,
            'providerProfile' => $providerProfile,
            'providerServiceTypes' => $providerServiceTypes,
            'providerLanding' => $providerLanding,
        ]);
    }

    public function servicesDelete(Request $request)
    {
        $user = Auth::user();
        $serviceId = (int) $request->query('id');

        if (! $user) {
            return response()->json(['status' => 401]);
        }

        $service = Service::find($serviceId);
        if (! $service) {
            return response()->json(['status' => 404]);
        }

        $isAdmin = (int) $user->user_level_id === 1;
        if (! $isAdmin && (int) $service->user_id !== (int) $user->id) {
            return response()->json(['status' => 403]);
        }

        CoverImage::where('service_id', $serviceId)->delete();
        MoreImage::where('service_id', $serviceId)->delete();
        Video::where('service_id', $serviceId)->delete();
        ServiceAddress::where('service_id', $serviceId)->delete();
        ServiceTypeLink::where('service_id', $serviceId)->delete();

        $service->delete();

        return response()->json(['status' => 200]);
    }

    public function servicesUpdate(string $id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $isAdmin = (int) $user->user_level_id === 1;
        $serviceQuery = Service::where('id', $id);
        if (! $isAdmin) {
            $serviceQuery->where('user_id', $user->id);
        }
        $service = $serviceQuery->get()->toArray();
        if (empty($service)) {
            return redirect()
                ->to('/post/services')
                ->with('status', 'Ocurrio un error interno');
        }

        $serviceType = ServiceType::all()->toArray();
        $serviceTypes = ServiceTypeLink::where('service_id', $id)->get()->toArray();
        $coverImage = CoverImage::where('service_id', $id)->get()->toArray();
        $moreImages = MoreImage::where('service_id', $id)->get()->toArray();
        $video = Video::where('service_id', $id)->get()->toArray();

        return view('post.forms.form_service_update', [
            'user' => $user,
            'userLevelName' => UserLevel::find($user->user_level_id)?->name ?? 'Usuario',
            'isAdmin' => $isAdmin,
            'activeNav' => 'services',
            'mapsKey' => config('services.google.maps_key'),
            'serviceType' => $serviceType,
            'serviceTypes' => $serviceTypes,
            'moreImages' => $moreImages,
            'coverImage' => $coverImage,
            'service' => $service,
            'video' => $video,
        ]);
    }

    public function servicesUpdateSave(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $serviceId = (int) $request->input('service_id');
        if (! $serviceId) {
            return redirect()->back();
        }

        $isAdmin = (int) $user->user_level_id === 1;
        $serviceQuery = Service::where('id', $serviceId);
        if (! $isAdmin) {
            $serviceQuery->where('user_id', $user->id);
        }
        $service = $serviceQuery->first();
        if (! $service) {
            return redirect()
                ->to('/post/services')
                ->with('status', 'Ocurrio un error interno');
        }

        $dataForDb = [];
        $title = $request->input('title');
        $description = $request->input('description');
        $availability = $request->input('availability');
        $documentNumber = $request->input('document_number');
        $pageUrl = $request->input('page_url');
        $serviceTypes = $request->input('service_type');

        $address = $request->input('address');
        $city = $request->input('city');
        $postalCode = $request->input('postal_code');
        $province = $request->input('province');
        $country = $request->input('country');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if (! empty($title)) {
            $dataForDb['title'] = $title;
        }
        if (! empty($description)) {
            $dataForDb['description'] = $description;
        }
        if (! empty($availability)) {
            $dataForDb['availability'] = $availability;
        }
        if (! empty($documentNumber)) {
            $dataForDb['document_number'] = $documentNumber;
        }
        if (! empty($pageUrl)) {
            $dataForDb['page_url'] = $pageUrl;
        }

        if (! empty($dataForDb)) {
            Service::where('id', $serviceId)->update($dataForDb);
        }

        if (! empty($serviceTypes)) {
            ServiceTypeLink::where('service_id', $serviceId)->delete();
            foreach ($serviceTypes as $value) {
                ServiceTypeLink::create([
                    'service_id' => $serviceId,
                    'service_type_id' => $value,
                ]);
            }
        }

        if ($address || $city || $postalCode || $province || $country || $latitude || $longitude) {
            ServiceAddress::updateOrCreate(
                ['service_id' => $serviceId],
                [
                    'address' => $address ?? '',
                    'city' => $city ?? '',
                    'province' => $province ?? '',
                    'postal_code' => $postalCode ?? '',
                    'country' => $country ?? '',
                    'latitude' => $latitude ?? '',
                    'longitude' => $longitude ?? '',
                ]
            );
        }

        $imagePath = public_path('img/uploads');
        $videoPath = public_path('video/uploads');
        if (! is_dir($imagePath)) {
            @mkdir($imagePath, 0755, true);
        }
        if (! is_dir($videoPath)) {
            @mkdir($videoPath, 0755, true);
        }

        $existingCoverImage = CoverImage::where('service_id', $serviceId)->first();
        $coverImage = $request->file('cover_image');
        if ($coverImage && $coverImage->isValid()) {
            $storedImage = $this->storeUploadedImage($coverImage, $imagePath);
            if (! $storedImage['success']) {
                return redirect()->back()->with('error', $storedImage['error']);
            }

            CoverImage::updateOrCreate(
                ['service_id' => $serviceId],
                ['url' => $storedImage['file_name']]
            );

            if ($existingCoverImage && $existingCoverImage->url !== $storedImage['file_name']) {
                $this->deleteStoredFile('img/uploads', $existingCoverImage->url);
            }
        }

        $moreImages = $request->file('more_images', []);
        if (! empty($moreImages)) {
            foreach ((array) $moreImages as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $storedImage = $this->storeUploadedImage($file, $imagePath);
                if (! $storedImage['success']) {
                    return redirect()->back()->with('error', $storedImage['error']);
                }

                MoreImage::create([
                    'url' => $storedImage['file_name'],
                    'service_id' => $serviceId,
                ]);
            }
        }

        $existingVideo = Video::where('service_id', $serviceId)->first();
        $video = $request->file('video');
        if ($video) {
            $storedVideo = $this->storeUploadedVideo($video, $videoPath);
            if (! $storedVideo['success']) {
                return redirect()->back()->with('error', $storedVideo['error']);
            }

            Video::updateOrCreate(
                ['service_id' => $serviceId],
                ['url' => $storedVideo['file_name']]
            );

            if ($existingVideo && $existingVideo->url !== $storedVideo['file_name']) {
                $this->deleteStoredFile('video/uploads', $existingVideo->url);
            }
        }

        $this->deleteOwnedMoreImages('service_id', $serviceId, $request->input('delete_more_images', []));

        return redirect()
            ->to('/post/services')
            ->with('status', 'Actualizado correctamente');
    }

    public function createService(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->canManageServices()) {
            return redirect()
                ->to('/home')
                ->with('error', 'Tu cuenta no puede crear servicios.');
        }

        $validated = $request->validate([
            'availability' => 'required|string|max:100',
            'description' => 'required|string',
            'page_url' => 'nullable|string|max:255',
            'service_type' => 'required|array|min:1',
            'service_type.*' => 'integer',
            'cover_image' => 'required|file|mimes:jpg,jpeg,png,webp',
            'more_images' => 'nullable|array',
            'more_images.*' => 'file|mimes:jpg,jpeg,png,webp',
            'video' => 'nullable|file|mimes:mp4,mov,avi,mpeg,mpg',
        ]);

        $imagePath = public_path('img/uploads');
        $videoPath = public_path('video/uploads');
        if (! is_dir($imagePath)) {
            @mkdir($imagePath, 0755, true);
        }
        if (! is_dir($videoPath)) {
            @mkdir($videoPath, 0755, true);
        }

        $coverImage = $request->file('cover_image');
        $storedCover = $this->storeUploadedImage($coverImage, $imagePath);
        if (! $storedCover['success']) {
            return redirect()->back()->with('error', $storedCover['error'])->withInput();
        }

        $service = Service::create([
            'title' => trim((string) $request->input('title', '')) ?: null,
            'description' => (string) $validated['description'],
            'availability' => (string) $validated['availability'],
            'document_number' => trim((string) ($user->document_number ?? '')),
            'page_url' => trim((string) $request->input('page_url', '')) ?: null,
            'user_id' => (int) $user->id,
        ]);

        $providerAddress = UserAddress::where('user_id', (int) $user->id)->first();
        $resolvedAddress = trim((string) ($providerAddress?->address ?? $user->address ?? ''));
        if ($resolvedAddress === '') {
            return redirect()
                ->back()
                ->with('error', 'Debes completar la direccion en Mi perfil antes de publicar un servicio.')
                ->withInput();
        }

        $resolvedLatitude = $providerAddress?->latitude;
        $resolvedLongitude = $providerAddress?->longitude;
        if ($resolvedLatitude === null || $resolvedLongitude === null || $resolvedLatitude === '' || $resolvedLongitude === '') {
            return redirect()
                ->back()
                ->with('error', 'Debes validar tu direccion en Mi perfil antes de publicar un servicio.')
                ->withInput();
        }

        CoverImage::create([
            'url' => $storedCover['file_name'],
            'service_id' => (int) $service->id,
        ]);

        foreach ((array) $validated['service_type'] as $serviceTypeId) {
            ServiceTypeLink::create([
                'service_id' => (int) $service->id,
                'service_type_id' => (int) $serviceTypeId,
            ]);
        }

        ServiceAddress::create([
            'service_id' => (int) $service->id,
            'address' => $resolvedAddress,
            'city' => (string) ($providerAddress?->city ?? ''),
            'province' => (string) ($providerAddress?->province ?? ''),
            'postal_code' => (string) ($providerAddress?->postal_code ?? ''),
            'country' => (string) ($providerAddress?->country ?? ''),
            'latitude' => (string) $resolvedLatitude,
            'longitude' => (string) $resolvedLongitude,
        ]);

        $moreImages = $request->file('more_images', []);
        if (! empty($moreImages)) {
            foreach ((array) $moreImages as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $storedImage = $this->storeUploadedImage($file, $imagePath);
                if (! $storedImage['success']) {
                    return redirect()->back()->with('error', $storedImage['error'])->withInput();
                }

                MoreImage::create([
                    'url' => $storedImage['file_name'],
                    'service_id' => (int) $service->id,
                ]);
            }
        }

        $video = $request->file('video');
        if ($video) {
            $storedVideo = $this->storeUploadedVideo($video, $videoPath);
            if (! $storedVideo['success']) {
                return redirect()->back()->with('error', $storedVideo['error'])->withInput();
            }

            Video::create([
                'url' => $storedVideo['file_name'],
                'service_id' => (int) $service->id,
            ]);
        }

        return redirect()
            ->to('/post/services')
            ->with('status', 'Servicio creado correctamente.');
    }

    private function validateResolvedPropertyAddress(Request $request): ?RedirectResponse
    {
        $address = trim((string) $request->input('address', ''));
        $latitude = trim((string) $request->input('latitude', ''));
        $longitude = trim((string) $request->input('longitude', ''));

        if ($address === '') {
            return redirect()
                ->back()
                ->with('error', 'La direccion es obligatoria.')
                ->withInput();
        }

        if ($latitude === '' || $longitude === '') {
            return redirect()
                ->back()
                ->with('error', 'Selecciona una direccion valida desde las sugerencias de Google Maps antes de guardar.')
                ->withInput();
        }

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return redirect()
                ->back()
                ->with('error', 'La direccion seleccionada no devolvio coordenadas validas.')
                ->withInput();
        }

        return null;
    }

    private function generatePropertyReference(): string
    {
        do {
            $reference = Str::lower(Str::random(8));
        } while (Property::where('reference', $reference)->exists());

        return $reference;
    }
}
