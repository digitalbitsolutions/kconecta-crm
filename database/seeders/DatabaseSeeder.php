<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\CoverImage;
use App\Models\MoreImage;
use App\Models\Property;
use App\Models\PropertyAddress;
use App\Models\Service;
use App\Models\ServiceAddress;
use App\Models\ServiceTypeLink;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserFree;
use App\Models\Video;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const DEFAULT_LEGACY_MEDIA_PATH = 'C:\\xampp\\htdocs\\kconecta\\img';
    private const PROPERTY_TARGET = 24;
    private const SERVICE_TARGET = 6;
    private const BLOG_TARGET = 8;

    private ?\Faker\Generator $faker = null;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedLookups();
        $this->syncLegacyMedia();

        $images = $this->loadImagePool();
        $users = $this->seedUsers($images['profiles']);

        $this->seedProperties($users['propertyOwners'], $users['userFrees'], $images['uploads']);
        $this->seedServices($users['serviceProviders'], $images['uploads']);
        $this->seedBlogs($images['articles']);
    }

    private function faker(): \Faker\Generator
    {
        if (! $this->faker) {
            $this->faker = \Faker\Factory::create('es_ES');
        }

        return $this->faker;
    }

    private function seedLookups(): void
    {
        $this->upsertRows('user_level', [
            ['id' => User::LEVEL_ADMIN, 'name' => 'Admin'],
            ['id' => User::LEVEL_FREE, 'name' => 'Free'],
            ['id' => User::LEVEL_PREMIUM, 'name' => 'Premium'],
            ['id' => User::LEVEL_SERVICE_PROVIDER, 'name' => 'Service Provider'],
            ['id' => User::LEVEL_AGENT, 'name' => 'Agent'],
        ], ['name']);

        $this->upsertRows('state', [
            ['id' => 1, 'name' => 'Borrador'],
            ['id' => 2, 'name' => 'Pendiente'],
            ['id' => 3, 'name' => 'Rechazado'],
            ['id' => 4, 'name' => 'Publicado'],
            ['id' => 5, 'name' => 'Oculto'],
        ], ['name']);

        $this->upsertRows('category', $this->rowsFromNames([
            'Venta',
            'Alquiler',
            'Alquiler vacacional',
        ]), ['name']);

        $this->upsertRows('type', [
            ['id' => 1, 'name' => 'Casa o chalet'],
            ['id' => 4, 'name' => 'Local o nave'],
            ['id' => 9, 'name' => 'Terreno'],
            ['id' => 13, 'name' => 'Piso'],
            ['id' => 14, 'name' => 'Garaje'],
            ['id' => 15, 'name' => 'Casa rustica'],
        ], ['name']);

        $this->upsertRows('typology', [
            ['id' => 1, 'name' => 'Chalet', 'type_id' => 1],
            ['id' => 2, 'name' => 'Adosado', 'type_id' => 1],
            ['id' => 3, 'name' => 'Duplex', 'type_id' => 1],
            ['id' => 4, 'name' => 'Villa', 'type_id' => 1],
            ['id' => 5, 'name' => 'Finca rustica', 'type_id' => 15],
            ['id' => 6, 'name' => 'Cortijo', 'type_id' => 15],
            ['id' => 7, 'name' => 'Casa de campo', 'type_id' => 15],
        ], ['name', 'type_id']);

        $this->upsertRows('city', $this->rowsFromNames([
            'Madrid',
            'Barcelona',
            'Valencia',
            'Sevilla',
            'Malaga',
            'Zaragoza',
            'Bilbao',
            'Alicante',
        ]), ['name']);

        $this->upsertRows('province', $this->rowsFromNames([
            'Madrid',
            'Barcelona',
            'Valencia',
            'Sevilla',
            'Malaga',
            'Zaragoza',
            'Vizcaya',
            'Alicante',
        ]), ['name']);

        $this->upsertRows('country', $this->rowsFromNames([
            'Espana',
            'Portugal',
        ]), ['name']);

        $this->upsertRows('orientation', $this->rowsFromNames([
            'Norte',
            'Sur',
            'Este',
            'Oeste',
            'Noreste',
            'Noroeste',
            'Sureste',
            'Suroeste',
        ]), ['name']);

        $this->upsertRows('type_heating', $this->rowsFromNames([
            'Central',
            'Individual',
            'Sin calefaccion',
        ]), ['name']);

        $this->upsertRows('heating_fuel', $this->rowsFromNames([
            'Gas',
            'Electricidad',
            'Gasoil',
            'Biomasa',
            'Aerotermia',
        ]), ['name']);

        $this->upsertRows('energy_class', $this->rowsFromNames([
            'A', 'B', 'C', 'D', 'E', 'F', 'G',
        ]), ['name']);

        $this->upsertRows('emissions_rating', $this->rowsFromNames([
            'A', 'B', 'C', 'D', 'E', 'F', 'G',
        ]), ['name']);

        $this->upsertRows('power_consumption_rating', $this->rowsFromNames([
            'Bajo',
            'Medio',
            'Alto',
            'Muy alto',
        ]), ['name']);

        $this->upsertRows('state_conservation', $this->rowsFromNames([
            'Nuevo',
            'Buen estado',
            'Reformado',
            'A reformar',
        ]), ['name']);

        $this->upsertRows('contact_option', $this->rowsFromNames([
            'Telefono',
            'WhatsApp',
            'Email',
            'Todos',
        ]), ['name']);

        $this->upsertRows('visibility_in_portals', $this->rowsFromNames([
            'Publico',
            'Privado',
            'Solo agentes',
        ]), ['name']);

        $this->upsertRows('rental_type', $this->rowsFromNames([
            'Larga estancia',
            'Temporal',
            'Vacacional',
        ]), ['name']);

        $this->upsertRows('reason_for_sale', $this->rowsFromNames([
            'Mudanza',
            'Inversion',
            'Herencia',
            'Otro',
        ]), ['name']);

        $this->upsertRows('plant', $this->rowsFromNames([
            'Bajo',
            '1',
            '2',
            '3',
            '4',
            'Atico',
        ]), ['name']);

        $this->upsertRows('door', $this->rowsFromNames([
            'A', 'B', 'C', 'D',
        ]), ['name']);

        $this->upsertRows('facade', $this->rowsFromNames([
            'Ladrillo',
            'Piedra',
            'Monocapa',
            'SATE',
        ]), ['name']);

        $this->upsertRows('plaza_capacity', $this->rowsFromNames([
            '1 plaza',
            '2 plazas',
            '3 plazas',
            '4 plazas',
        ]), ['name']);

        $this->upsertRows('type_of_terrain', $this->rowsFromNames([
            'Urbano',
            'Urbanizable',
            'Rústico',
        ]), ['name']);

        $this->upsertRows('terrain_use', $this->rowsFromNames([
            'Servicios',
            'Residencial',
            'Industrial',
            'Agrícola',
        ]), ['name']);

        $this->upsertRows('terrain_qualification', [
            ['id' => 1, 'name' => 'Residencial en altura (bloques)'],
            ['id' => 2, 'name' => 'Residencial unifamiliar (chalets)'],
            ['id' => 3, 'name' => 'Terciario oficinas'],
            ['id' => 4, 'name' => 'Terciario comercial'],
            ['id' => 5, 'name' => 'Terciario hoteles'],
            ['id' => 6, 'name' => 'Industrial'],
            ['id' => 7, 'name' => 'Dotaciones (hospitales, escuelas, museos)'],
            ['id' => 8, 'name' => 'Otra'],
        ], ['name']);

        $this->upsertRows('wheeled_access', $this->rowsFromNames([
            'Accesible',
            'No accesible',
            'Adaptado',
        ]), ['name']);

        $this->upsertRows('nearest_municipality_distance', $this->rowsFromNames([
            '0-1 km',
            '1-5 km',
            '5-10 km',
            '10+ km',
        ]), ['name']);

        $this->upsertRows('location_premises', $this->rowsFromNames([
            'Calle',
            'Centro comercial',
            'Poligono',
            'Zona residencial',
        ]), ['name']);

        $this->upsertRows('garage_price_category', $this->rowsFromNames([
            'Incluido',
            'Opcional',
            'No disponible',
        ]), ['name']);

        $this->upsertRows('type_floor', $this->rowsFromNames([
            'Parquet',
            'Gres',
            'Marmol',
            'Laminado',
            'Tarima',
        ]), ['name']);

        $this->upsertRows('location_action', $this->rowsFromNames([
            'Venta',
            'Alquiler',
            'Reforma',
            'Decoracion',
        ]), ['name']);

        $this->upsertRows('feature', [
            ['id' => 1, 'name' => 'Aire acondicionado', 'id_type' => null, 'category_id' => null],
            ['id' => 2, 'name' => 'Armarios empotrados', 'id_type' => null, 'category_id' => null],
            ['id' => 3, 'name' => 'Terraza', 'id_type' => null, 'category_id' => null],
            ['id' => 4, 'name' => 'Jardin', 'id_type' => null, 'category_id' => null],
            ['id' => 5, 'name' => 'Piscina', 'id_type' => null, 'category_id' => null],
            ['id' => 6, 'name' => 'Trastero', 'id_type' => null, 'category_id' => null],
            ['id' => 7, 'name' => 'Balcon', 'id_type' => null, 'category_id' => null],
            ['id' => 8, 'name' => 'Calefaccion', 'id_type' => null, 'category_id' => null],
            ['id' => 9, 'name' => 'Ascensor', 'id_type' => null, 'category_id' => null],
            ['id' => 10, 'name' => 'Vistas abiertas', 'id_type' => null, 'category_id' => null],
            ['id' => 11, 'name' => 'Acceso adaptado', 'id_type' => null, 'category_id' => null],
            ['id' => 12, 'name' => 'Plaza cubierta', 'id_type' => 14, 'category_id' => null],
            ['id' => 13, 'name' => 'Puerta automatica', 'id_type' => 14, 'category_id' => null],
            ['id' => 14, 'name' => 'Trastero garaje', 'id_type' => 14, 'category_id' => null],
            ['id' => 15, 'name' => 'Agua', 'id_type' => 9, 'category_id' => null],
            ['id' => 16, 'name' => 'Luz', 'id_type' => 9, 'category_id' => null],
            ['id' => 17, 'name' => 'Alcantarillado', 'id_type' => 9, 'category_id' => null],
            ['id' => 18, 'name' => 'Gas natural', 'id_type' => 9, 'category_id' => null],
            ['id' => 19, 'name' => 'Alumbrado publico', 'id_type' => 9, 'category_id' => null],
            ['id' => 20, 'name' => 'Aceras', 'id_type' => 9, 'category_id' => null],
        ], ['name', 'id_type', 'category_id']);

        $this->upsertRows('equipment', [
            ['id' => 1, 'name' => 'Horno', 'type_id' => 1],
            ['id' => 2, 'name' => 'Vitroceramica', 'type_id' => 1],
            ['id' => 3, 'name' => 'Lavavajillas', 'type_id' => 1],
            ['id' => 4, 'name' => 'Frigorifico', 'type_id' => 1],
            ['id' => 5, 'name' => 'Salida de humos', 'type_id' => 4],
            ['id' => 6, 'name' => 'Escaparate', 'type_id' => 4],
            ['id' => 7, 'name' => 'Alarma', 'type_id' => 4],
            ['id' => 8, 'name' => 'Puerta automatica', 'type_id' => 14],
            ['id' => 9, 'name' => 'Vigilancia', 'type_id' => 14],
            ['id' => 10, 'name' => 'Cargador electrico', 'type_id' => 14],
        ], ['name', 'type_id']);

        $this->upsertRows('service_type', [
            ['id' => 1, 'name' => 'Reformas'],
            ['id' => 2, 'name' => 'Mudanzas'],
            ['id' => 3, 'name' => 'Limpieza'],
            ['id' => 4, 'name' => 'Tasacion'],
            ['id' => 5, 'name' => 'Fotografia'],
            ['id' => 6, 'name' => 'Home staging'],
        ], ['name']);
    }

    private function seedUsers(array $profileImages): array
    {
        $admin = User::where('email', 'admin@kconecta.test')->first();
        if (! $admin) {
            $admin = User::create([
                'first_name' => 'Admin',
                'last_name' => 'User',
                'user_name' => 'admin',
                'email' => 'admin@kconecta.test',
                'phone' => '600000000',
                'document_type' => 'DNI',
                'document_number' => '00000000A',
                'address' => 'Calle Principal 1',
                'password' => Hash::make('password'),
                'photo' => $this->randomImage($profileImages),
                'user_level_id' => User::LEVEL_ADMIN,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
        }

        $test = User::where('email', 'test@example.com')->first();
        if (! $test) {
            $test = User::factory()->create([
                'email' => 'test@example.com',
                'user_level_id' => User::LEVEL_FREE,
                'photo' => $this->randomImage($profileImages),
                'email_verified_at' => now(),
            ]);
        }

        $propertyLevels = [User::LEVEL_FREE, User::LEVEL_PREMIUM, User::LEVEL_AGENT];
        $owners = User::whereIn('user_level_id', $propertyLevels)->get();
        $missingOwners = max(0, 8 - $owners->count());
        for ($i = 0; $i < $missingOwners; $i++) {
            User::factory()->create([
                'user_level_id' => $this->faker()->randomElement($propertyLevels),
                'photo' => $this->randomImage($profileImages),
                'email_verified_at' => now(),
            ]);
        }

        $providers = User::where('user_level_id', User::LEVEL_SERVICE_PROVIDER)->get();
        $missingProviders = max(0, 4 - $providers->count());
        for ($i = 0; $i < $missingProviders; $i++) {
            User::factory()->create([
                'user_level_id' => User::LEVEL_SERVICE_PROVIDER,
                'photo' => $this->randomImage($profileImages),
                'email_verified_at' => now(),
            ]);
        }

        if (UserFree::count() < 8) {
            $toCreate = 8 - UserFree::count();
            for ($i = 0; $i < $toCreate; $i++) {
                UserFree::create([
                    'name' => $this->faker()->name(),
                    'email' => $this->faker()->unique()->safeEmail(),
                    'photo' => $this->randomImage($profileImages),
                ]);
            }
        }

        $ownerIds = User::whereIn('user_level_id', $propertyLevels)->pluck('id')->all();
        $ownerIds[] = $admin->id;
        $ownerIds = array_values(array_unique($ownerIds));
        $providerIds = User::where('user_level_id', User::LEVEL_SERVICE_PROVIDER)->pluck('id')->all();
        $userFreeIds = UserFree::pluck('id')->all();

        $this->seedUserAddresses(array_values(array_unique(array_merge($ownerIds, $providerIds))));

        return [
            'propertyOwners' => $ownerIds,
            'serviceProviders' => $providerIds,
            'userFrees' => $userFreeIds,
        ];
    }

    private function seedUserAddresses(array $userIds): void
    {
        foreach ($userIds as $userId) {
            if (UserAddress::where('user_id', $userId)->exists()) {
                continue;
            }

            UserAddress::create([
                'user_id' => $userId,
                'address' => $this->faker()->streetAddress(),
                'street_name' => $this->faker()->streetName(),
                'street_number' => (string) $this->faker()->buildingNumber(),
                'neighborhood' => $this->faker()->word(),
                'city' => $this->faker()->city(),
                'province' => $this->faker()->state(),
                'postal_code' => $this->faker()->postcode(),
                'state' => $this->faker()->state(),
                'country' => $this->faker()->country(),
                'latitude' => (string) $this->faker()->latitude(),
                'longitude' => (string) $this->faker()->longitude(),
                'additional_info' => $this->faker()->optional()->sentence(),
            ]);
        }
    }

    private function seedProperties(array $ownerIds, array $userFreeIds, array $uploadImages): void
    {
        if (empty($ownerIds)) {
            return;
        }

        $current = Property::count();
        if ($current >= self::PROPERTY_TARGET) {
            return;
        }

        $typeMap = DB::table('type')->pluck('name', 'id')->all();
        $categoryMap = DB::table('category')->pluck('name', 'id')->all();
        $cityMap = DB::table('city')->pluck('name', 'id')->all();
        $provinceMap = DB::table('province')->pluck('name', 'id')->all();
        $countryMap = DB::table('country')->pluck('name', 'id')->all();

        $typeIds = array_keys($typeMap);
        $categoryIds = array_keys($categoryMap);
        $cityIds = array_keys($cityMap);
        $provinceIds = array_keys($provinceMap);
        $countryIds = array_keys($countryMap);

        $orientationIds = DB::table('orientation')->pluck('id')->all();
        $typeFloorIds = DB::table('type_floor')->pluck('id')->all();
        $contactOptionIds = DB::table('contact_option')->pluck('id')->all();
        $visibilityIds = DB::table('visibility_in_portals')->pluck('id')->all();
        $rentalTypeIds = DB::table('rental_type')->pluck('id')->all();
        $reasonIds = DB::table('reason_for_sale')->pluck('id')->all();
        $typeHeatingIds = DB::table('type_heating')->pluck('id')->all();
        $heatingFuelIds = DB::table('heating_fuel')->pluck('id')->all();
        $energyClassIds = DB::table('energy_class')->pluck('id')->all();
        $emissionsRatingIds = DB::table('emissions_rating')->pluck('id')->all();
        $powerConsumptionIds = DB::table('power_consumption_rating')->pluck('id')->all();
        $stateConservationIds = DB::table('state_conservation')->pluck('id')->all();
        $facadeIds = DB::table('facade')->pluck('id')->all();
        $plantIds = DB::table('plant')->pluck('id')->all();
        $doorIds = DB::table('door')->pluck('id')->all();
        $plazaCapacityIds = DB::table('plaza_capacity')->pluck('id')->all();
        $typeTerrainIds = DB::table('type_of_terrain')->pluck('id')->all();
        $wheeledAccessIds = DB::table('wheeled_access')->pluck('id')->all();
        $nearestIds = DB::table('nearest_municipality_distance')->pluck('id')->all();
        $locationPremisesIds = DB::table('location_premises')->pluck('id')->all();
        $garageCategoryIds = DB::table('garage_price_category')->pluck('id')->all();

        $typologyRows = DB::table('typology')->get(['id', 'type_id']);
        $typologyByType = [];
        foreach ($typologyRows as $row) {
            $typologyByType[$row->type_id][] = $row->id;
        }

        $featureRows = DB::table('feature')->get(['id', 'id_type']);
        $featuresGeneral = [];
        $featuresByType = [];
        foreach ($featureRows as $row) {
            if ($row->id_type === null) {
                $featuresGeneral[] = $row->id;
            } else {
                $featuresByType[$row->id_type][] = $row->id;
            }
        }

        $equipmentRows = DB::table('equipment')->get(['id', 'type_id']);
        $equipmentGeneral = [];
        $equipmentByType = [];
        foreach ($equipmentRows as $row) {
            if ($row->type_id === null) {
                $equipmentGeneral[] = $row->id;
            } else {
                $equipmentByType[$row->type_id][] = $row->id;
            }
        }

        $toCreate = self::PROPERTY_TARGET - $current;
        $referenceBase = (int) (Property::max('id') ?? 0);

        $stats = [
            'ps_views_detail' => [],
            'ps_views_search' => [],
            'ps_email_owner' => [],
            'ps_owner_calls' => [],
            'ps_whatsapp_clicks' => [],
            'ps_link_copied' => [],
            'ps_saved_favorite' => [],
            'ps_shared_facebook' => [],
            'ps_shared_friends' => [],
            'post_visits' => [],
            'favorite' => [],
            'ps_messages_received' => [],
        ];

        for ($i = 1; $i <= $toCreate; $i++) {
            $typeId = $typeIds ? (int) $this->faker()->randomElement($typeIds) : null;
            $categoryId = $categoryIds ? (int) $this->faker()->randomElement($categoryIds) : null;
            $cityId = $cityIds ? (int) $this->faker()->randomElement($cityIds) : null;
            $provinceId = $provinceIds ? (int) $this->faker()->randomElement($provinceIds) : null;
            $countryId = $countryIds ? (int) $this->faker()->randomElement($countryIds) : null;

            $typeName = $typeMap[$typeId] ?? 'Propiedad';
            $cityName = $cityMap[$cityId] ?? 'Ciudad';
            $reference = $this->makeReference($referenceBase + $i);
            $title = $typeName . ' en ' . $cityName;
            $isRental = $this->isRentalCategory($categoryMap[$categoryId] ?? null);

            $salePrice = $isRental ? null : $this->faker()->numberBetween(90000, 850000);
            $rentalPrice = $isRental ? $this->faker()->numberBetween(500, 3500) : null;

            $isGarage = $typeId === 14;
            $garagePrice = $isGarage
                ? ($isRental ? $this->faker()->numberBetween(60, 250) : $this->faker()->numberBetween(5000, 40000))
                : null;

            $bedrooms = $isGarage || $typeId === 9 ? 0 : $this->faker()->numberBetween(1, 5);
            $bathrooms = $isGarage || $typeId === 9 ? 0 : $this->faker()->numberBetween(1, 3);

            $property = Property::create([
                'reference' => $reference,
                'title' => Str::limit($title, 150, ''),
                'description' => $this->faker()->paragraphs(2, true),
                'meters_built' => $this->faker()->numberBetween(40, 350),
                'useful_meters' => $this->faker()->numberBetween(35, 300),
                'plot_meters' => $typeId === 9 ? $this->faker()->numberBetween(200, 2500) : null,
                'land_size' => $typeId === 9 ? (string) $this->faker()->numberBetween(200, 2500) : null,
                'plant' => $this->randomName($plantIds, 'plant'),
                'number_of_plants' => $this->faker()->numberBetween(1, 5),
                'emissions_consumption' => $this->faker()->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G']),
                'energy_consumption' => $this->faker()->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G']),
                'sale_price' => $salePrice,
                'rental_price' => $rentalPrice,
                'garage_price' => $garagePrice,
                'community_expenses' => $this->faker()->optional()->numberBetween(20, 180),
                'year_of_construction' => (string) $this->faker()->numberBetween(1980, 2024),
                'bedrooms' => $bedrooms,
                'bathrooms' => $bathrooms,
                'rooms' => $bedrooms > 0 ? $bedrooms + $this->faker()->numberBetween(1, 3) : 0,
                'elevator' => $bedrooms > 0 ? (int) $this->faker()->boolean() : 0,
                'appropriate_for_children' => (int) $this->faker()->boolean(),
                'pet_friendly' => (int) $this->faker()->boolean(),
                'stays' => 'Salon, cocina, comedor',
                'bank_owned_property' => (int) $this->faker()->boolean(15),
                'guarantee' => $isRental ? $this->faker()->numberBetween(1, 3) : null,
                'ibi' => $this->faker()->optional()->numberBetween(100, 900),
                'parking' => $isGarage ? 'Si' : ($this->faker()->boolean() ? 'Si' : 'No'),
                'locality' => $cityName,
                'address' => Str::limit($this->faker()->streetAddress(), 200, ''),
                'zip_code' => $this->faker()->postcode(),
                'page_url' => Str::slug($title . ' ' . $reference),
                'state_id' => 4,
                'user_id' => (int) $this->faker()->randomElement($ownerIds),
                'type_id' => $typeId,
                'category_id' => $categoryId,
                'city_id' => $cityId,
                'province_id' => $provinceId,
                'country_id' => $countryId,
                'typology_id' => $this->randomId($typologyByType[$typeId] ?? []),
                'orientation_id' => $this->randomId($orientationIds),
                'type_heating_id' => $this->randomId($typeHeatingIds),
                'emissions_rating_id' => $this->randomId($emissionsRatingIds),
                'energy_class_id' => $this->randomId($energyClassIds),
                'state_conservation_id' => $this->randomId($stateConservationIds),
                'visibility_in_portals_id' => $this->randomId($visibilityIds),
                'rental_type_id' => $isRental ? $this->randomId($rentalTypeIds) : null,
                'contact_option_id' => $this->randomId($contactOptionIds),
                'power_consumption_rating_id' => $this->randomId($powerConsumptionIds),
                'reason_for_sale_id' => $this->randomId($reasonIds),
                'plant_id' => $this->randomId($plantIds),
                'door_id' => $this->randomId($doorIds),
                'facade_id' => $this->randomId($facadeIds),
                'plaza_capacity_id' => $this->randomId($plazaCapacityIds),
                'type_of_terrain_id' => $typeId === 9 ? $this->randomId($typeTerrainIds) : null,
                'wheeled_access_id' => $this->randomId($wheeledAccessIds),
                'nearest_municipality_distance_id' => $this->randomId($nearestIds),
                'heating_fuel_id' => $this->randomId($heatingFuelIds),
                'location_premises_id' => $this->randomId($locationPremisesIds),
                'garage_price_category_id' => $isGarage ? $this->randomId($garageCategoryIds) : null,
            ]);

            PropertyAddress::create([
                'property_id' => $property->id,
                'address' => $this->faker()->streetAddress(),
                'street_name' => $this->faker()->streetName(),
                'street_number' => (string) $this->faker()->buildingNumber(),
                'neighborhood' => $this->faker()->word(),
                'city' => $cityName,
                'district' => $this->faker()->word(),
                'province' => $provinceMap[$provinceId] ?? 'Provincia',
                'postal_code' => $this->faker()->postcode(),
                'state' => 'Estado',
                'country' => $countryMap[$countryId] ?? 'Espana',
                'latitude' => (string) $this->faker()->latitude(),
                'longitude' => (string) $this->faker()->longitude(),
                'additional_info' => $this->faker()->optional()->sentence(),
            ]);

            $coverImage = $this->randomImage($uploadImages);
            if ($coverImage) {
                CoverImage::create([
                    'url' => $coverImage,
                    'property_id' => $property->id,
                ]);
            }

            foreach ($this->randomSubset($uploadImages, 3, 7) as $imageName) {
                MoreImage::create([
                    'url' => $imageName,
                    'property_id' => $property->id,
                ]);
            }

            if ($this->faker()->boolean(30)) {
                Video::create([
                    'property_id' => $property->id,
                    'url' => 'https://www.youtube.com/watch?v=Qm9zN8sVx2E',
                ]);
            }

            $featurePool = array_merge($featuresGeneral, $featuresByType[$typeId] ?? []);
            $this->insertPivotRows('features', $property->id, 'feature_id', $this->randomSubset($featurePool, 3, 6));

            $equipmentPool = array_merge($equipmentGeneral, $equipmentByType[$typeId] ?? []);
            $this->insertPivotRows('equipments', $property->id, 'equipment_id', $this->randomSubset($equipmentPool, 2, 4));

            $this->insertPivotRows('orientations', $property->id, 'orientation_id', $this->randomSubset($orientationIds, 1, 2));
            $this->insertPivotRows('types_floors', $property->id, 'type_floor_id', $this->randomSubset($typeFloorIds, 1, 2));

            $stats['ps_views_detail'][] = [
                'property_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'counter' => $this->faker()->numberBetween(25, 400),
            ];
            $stats['ps_views_search'][] = [
                'property_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'counter' => $this->faker()->numberBetween(10, 200),
            ];
            $stats['ps_email_owner'][] = [
                'property_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'counter' => $this->faker()->numberBetween(0, 25),
            ];
            $stats['ps_owner_calls'][] = [
                'property_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'counter' => $this->faker()->numberBetween(0, 20),
            ];
            $stats['ps_whatsapp_clicks'][] = [
                'property_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'counter' => $this->faker()->numberBetween(0, 35),
            ];
            $stats['ps_link_copied'][] = [
                'property_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'counter' => $this->faker()->numberBetween(0, 15),
            ];
            $stats['ps_saved_favorite'][] = [
                'property_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'counter' => $this->faker()->numberBetween(0, 18),
            ];
            $stats['ps_shared_facebook'][] = [
                'property_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'counter' => $this->faker()->numberBetween(0, 10),
            ];
            $stats['ps_shared_friends'][] = [
                'property_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'counter' => $this->faker()->numberBetween(0, 10),
            ];
            $stats['post_visits'][] = [
                'post_id' => $property->id,
                'ip_address' => $this->faker()->ipv4(),
                'referer' => 'https://google.com',
                'contacted' => (int) $this->faker()->boolean(),
            ];
            $stats['favorite'][] = [
                'property_id' => $property->id,
                'user_id' => (int) $this->faker()->randomElement($ownerIds),
            ];

            if (! empty($userFreeIds) && $this->faker()->boolean(30)) {
                $stats['ps_messages_received'][] = [
                    'property_id' => $property->id,
                    'user_free_id' => (int) $this->faker()->randomElement($userFreeIds),
                    'message' => $this->faker()->sentence(),
                ];
            }
        }

        foreach ($stats as $table => $rows) {
            if (! empty($rows)) {
                DB::table($table)->insert($rows);
            }
        }
    }

    private function seedServices(array $providerIds, array $uploadImages): void
    {
        if (empty($providerIds)) {
            return;
        }

        $current = Service::count();
        if ($current >= self::SERVICE_TARGET) {
            return;
        }

        $toCreate = self::SERVICE_TARGET - $current;
        $serviceTypeIds = DB::table('service_type')->pluck('id')->all();
        $cityNames = DB::table('city')->pluck('name')->all();
        $provinceNames = DB::table('province')->pluck('name')->all();
        $countryNames = DB::table('country')->pluck('name')->all();

        for ($i = 0; $i < $toCreate; $i++) {
            $title = Str::limit($this->faker()->company() . ' Servicios', 150, '');
            $service = Service::create([
                'title' => $title,
                'description' => $this->faker()->paragraphs(2, true),
                'availability' => 'Lunes a Viernes',
                'document_number' => $this->faker()->numerify('########'),
                'page_url' => Str::slug($title),
                'user_id' => (int) $this->faker()->randomElement($providerIds),
            ]);

            ServiceAddress::create([
                'service_id' => $service->id,
                'address' => $this->faker()->streetAddress(),
                'street_name' => $this->faker()->streetName(),
                'street_number' => (string) $this->faker()->buildingNumber(),
                'neighborhood' => $this->faker()->word(),
                'city' => $cityNames ? (string) $this->faker()->randomElement($cityNames) : $this->faker()->city(),
                'province' => $provinceNames ? (string) $this->faker()->randomElement($provinceNames) : $this->faker()->state(),
                'postal_code' => $this->faker()->postcode(),
                'state' => 'Estado',
                'country' => $countryNames ? (string) $this->faker()->randomElement($countryNames) : $this->faker()->country(),
                'latitude' => (string) $this->faker()->latitude(),
                'longitude' => (string) $this->faker()->longitude(),
                'additional_info' => $this->faker()->optional()->sentence(),
            ]);

            $coverImage = $this->randomImage($uploadImages);
            if ($coverImage) {
                CoverImage::create([
                    'url' => $coverImage,
                    'service_id' => $service->id,
                ]);
            }

            foreach ($this->randomSubset($uploadImages, 2, 5) as $imageName) {
                MoreImage::create([
                    'url' => $imageName,
                    'service_id' => $service->id,
                ]);
            }

            if ($this->faker()->boolean(40)) {
                Video::create([
                    'service_id' => $service->id,
                    'url' => 'https://www.youtube.com/watch?v=7Yf3YAdx2lQ',
                ]);
            }

            $typeIds = $this->randomSubset($serviceTypeIds, 1, 3);
            foreach ($typeIds as $typeId) {
                ServiceTypeLink::create([
                    'service_id' => $service->id,
                    'service_type_id' => $typeId,
                ]);
            }
        }
    }

    private function seedBlogs(array $articleImages): void
    {
        $current = BlogPost::count();
        if ($current >= self::BLOG_TARGET) {
            return;
        }

        $toCreate = self::BLOG_TARGET - $current;
        $baseId = (int) (BlogPost::max('id') ?? 0);

        for ($i = 1; $i <= $toCreate; $i++) {
            $title = Str::limit($this->faker()->sentence(6), 140, '');
            $slug = Str::slug($title . '-' . ($baseId + $i));
            $image = $this->randomImage($articleImages);

            BlogPost::create([
                'title' => $title,
                'slug' => $slug,
                'summary' => $this->faker()->sentence(18),
                'featured_image' => $image ? 'img/article/' . $image : null,
                'content' => implode("\n\n", $this->faker()->paragraphs(4)),
                'status' => 1,
                'blog_post_category_id' => null,
            ]);
        }
    }

    private function syncLegacyMedia(): void
    {
        $legacyMediaPath = (string) env('LEGACY_MEDIA_PATH', self::DEFAULT_LEGACY_MEDIA_PATH);

        if (! is_dir($legacyMediaPath)) {
            return;
        }

        $this->syncMediaDir($legacyMediaPath . '\\uploads', public_path('img/uploads'));
        $this->syncMediaDir($legacyMediaPath . '\\article', public_path('img/article'));
        $this->syncMediaDir($legacyMediaPath . '\\photo_profile', public_path('img/photo_profile'));
    }

    private function syncMediaDir(string $sourceDir, string $targetDir): void
    {
        if (! is_dir($sourceDir)) {
            return;
        }

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        foreach (File::files($sourceDir) as $file) {
            $target = $targetDir . DIRECTORY_SEPARATOR . $file->getFilename();
            if (! File::exists($target)) {
                File::copy($file->getPathname(), $target);
            }
        }
    }

    private function loadImagePool(): array
    {
        return [
            'uploads' => $this->listImageFiles(public_path('img/uploads')),
            'articles' => $this->listImageFiles(public_path('img/article')),
            'profiles' => $this->listImageFiles(public_path('img/photo_profile')),
        ];
    }

    private function listImageFiles(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $allowed = ['webp', 'jpg', 'jpeg', 'png', 'avif', 'gif'];

        return collect(File::files($path))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), $allowed, true))
            ->map(fn ($file) => $file->getFilename())
            ->values()
            ->all();
    }

    private function upsertRows(string $table, array $rows, array $updateColumns): void
    {
        if (empty($rows)) {
            return;
        }

        DB::table($table)->upsert($rows, ['id'], $updateColumns);
    }

    private function rowsFromNames(array $names, int $startId = 1): array
    {
        $rows = [];
        foreach ($names as $offset => $name) {
            $rows[] = [
                'id' => $startId + $offset,
                'name' => $name,
            ];
        }

        return $rows;
    }

    private function randomImage(array $images): ?string
    {
        if (empty($images)) {
            return null;
        }

        return (string) $images[array_rand($images)];
    }

    private function randomSubset(array $items, int $min, int $max): array
    {
        if (empty($items)) {
            return [];
        }

        $count = count($items);
        $take = min($count, $this->faker()->numberBetween($min, $max));

        return collect($items)->shuffle()->take($take)->values()->all();
    }

    private function insertPivotRows(string $table, int $propertyId, string $foreignKey, array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $rows = [];
        foreach ($ids as $id) {
            $rows[] = [
                'property_id' => $propertyId,
                $foreignKey => $id,
            ];
        }

        DB::table($table)->insert($rows);
    }

    private function isRentalCategory(?string $name): bool
    {
        if (! $name) {
            return false;
        }

        $label = Str::lower($name);

        return str_contains($label, 'alquiler');
    }

    private function makeReference(int $sequence): string
    {
        return 'DM-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    private function randomId(array $ids): ?int
    {
        if (empty($ids)) {
            return null;
        }

        return (int) $ids[array_rand($ids)];
    }

    private function randomName(array $ids, string $table): ?string
    {
        if (empty($ids)) {
            return null;
        }

        $id = $this->randomId($ids);
        $row = $id ? DB::table($table)->find($id) : null;

        return $row?->name;
    }
}
