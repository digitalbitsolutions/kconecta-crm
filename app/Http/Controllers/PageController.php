<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\ContactOption;
use App\Models\Country;
use App\Models\CoverImage;
use App\Models\EmissionsRating;
use App\Models\EnergyClass;
use App\Models\Equipment;
use App\Models\Equipments;
use App\Models\Facade;
use App\Models\Feature;
use App\Models\Features;
use App\Models\MoreImage;
use App\Models\NearestMunicipalityDistance;
use App\Models\Orientation;
use App\Models\Orientations;
use App\Models\Plant;
use App\Models\PlazaCapacity;
use App\Models\PostVisit;
use App\Models\PowerConsumptionRating;
use App\Models\Property;
use App\Models\PropertyAddress;
use App\Models\Province;
use App\Models\PsViewsDetail;
use App\Models\PsViewsSearch;
use App\Models\ReasonForSale;
use App\Models\RentalType;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\ServiceTypeLink;
use App\Models\StateConservation;
use App\Models\TerrainUse;
use App\Models\TerrainQualification;
use App\Models\TerrainQualifications;
use App\Models\Type;
use App\Models\TypeFloor;
use App\Models\TypeHeating;
use App\Models\TypeOfTerrain;
use App\Models\TypesFloors;
use App\Models\Typology;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Video;
use App\Models\VisibilityInPortals;
use App\Models\WheeledAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function index()
    {
        Carbon::setLocale('es');

        $city = City::orderBy('name')->get()->toArray();
        $serviceType = ServiceType::orderBy('name')->get()->toArray();

        $properties = Property::query()
            ->where('state_id', 4)
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(function (Property $property) {
                $item = $property->toArray();

                $item['updated_at_text'] = $this->formatUpdatedAt($property->updated_at);
                $item['type_name'] = Type::find($property->type_id)?->name ?? '';
                $item['category_name'] = Category::find($property->category_id)?->name ?? '';

                $coverImage = CoverImage::where('property_id', $property->id)->first();
                $item['cover_image'] = $coverImage ? $coverImage->toArray() : ['url' => ''];

                $user = User::find($property->user_id);
                $item['user_name'] = $user?->user_name ?? '';

                $item['post_visits'] = PostVisit::where('post_id', $property->id)->count();

                $item['state_conservation'] = $this->wrapSingle(StateConservation::find($property->state_conservation_id));
                $item['facade'] = $this->wrapSingle(Facade::find($property->facade_id));
                $item['nearest_municipality_distance'] = $this->wrapSingle(
                    NearestMunicipalityDistance::find($property->nearest_municipality_distance_id)
                );
                $item['type_of_terrain'] = $this->wrapSingle(TypeOfTerrain::find($property->type_of_terrain_id));
                $item['wheeled_access'] = $this->wrapSingle(WheeledAccess::find($property->wheeled_access_id));

                return $item;
            })
            ->values()
            ->all();

        return view('page.index', [
            'property' => $properties,
            'serviceType' => $serviceType,
            'city' => $city,
        ]);
    }

    public function resultAll()
    {
        Carbon::setLocale('es');

        $request = request();

        $mode = $request->query('mode');
        $address = $request->query('address');
        $categoryId = $request->query('ca');
        $typeId = $request->query('ty');
        $pMin = $request->query('p_min');
        $pMax = $request->query('p_max');
        $builtMin = $request->query('built_min');
        $builtMax = $request->query('built_max');
        $numMinBathrooms = $request->query('n_bar');
        $numMinBedrooms = $request->query('n_ber');

        $city = $request->query('city');
        $province = $request->query('province');
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $zoom = $request->query('zoom');

        $propertiesQuery = Property::query()->where('state_id', 4);

        if (! empty($city) || ! empty($province)) {
            $addressQuery = PropertyAddress::query();
            if (! empty($province) && empty($city)) {
                $addressQuery->where('province', trim($province));
            } elseif (! empty($city)) {
                $addressQuery->where('city', trim($city));
            }

            $ids = $addressQuery->pluck('property_id')->map(fn ($id) => (int) $id)->all();
            if (! empty($ids)) {
                $propertiesQuery->whereIn('id', $ids);
            } else {
                $propertiesQuery->where('id', 0);
            }
        } elseif (! empty($address)) {
            $addressParts = explode(',', $address);
            $addressSeed = trim($addressParts[0]);

            $addressQuery = PropertyAddress::query()
                ->where('address', 'like', '%' . trim($address) . '%')
                ->orWhere('address', 'like', '%' . $addressSeed . '%')
                ->orWhere('province', 'like', '%' . $addressSeed . '%')
                ->orWhere('city', 'like', '%' . $addressSeed . '%');

            $ids = $addressQuery->pluck('property_id')->map(fn ($id) => (int) $id)->all();
            if (! empty($ids)) {
                $propertiesQuery->whereIn('id', $ids);
            } else {
                $propertiesQuery->where('id', 0);
            }
        } else {
            $addressQuery = PropertyAddress::query()
                ->where('address', 'like', '%barcelona%')
                ->orWhere('province', 'like', '%barcelona%')
                ->orWhere('city', 'like', '%barcelona%');

            $ids = $addressQuery->pluck('property_id')->map(fn ($id) => (int) $id)->all();
            if (! empty($ids)) {
                $propertiesQuery->whereIn('id', $ids);
            } else {
                $propertiesQuery->where('id', 0);
            }
        }

        if (! empty($categoryId)) {
            $propertiesQuery->where('category_id', $categoryId);
            $priceField = ((int) $categoryId === 1) ? 'rental_price' : 'sale_price';
            if (! empty($pMin)) {
                $propertiesQuery->where($priceField, '>=', $pMin);
            }
            if (! empty($pMax)) {
                $propertiesQuery->where($priceField, '<=', $pMax);
            }
        } else {
            if (! empty($pMin)) {
                $propertiesQuery->where(function ($query) use ($pMin) {
                    $query->where('sale_price', '>=', $pMin)
                        ->orWhere('rental_price', '>=', $pMin);
                });
            }
            if (! empty($pMax)) {
                $propertiesQuery->where(function ($query) use ($pMax) {
                    $query->where('sale_price', '<=', $pMax)
                        ->orWhere('rental_price', '<=', $pMax);
                });
            }
        }

        if (! empty($numMinBathrooms)) {
            $propertiesQuery->where('bathrooms', '>=', $numMinBathrooms);
        }
        if (! empty($numMinBedrooms)) {
            $propertiesQuery->where('bedrooms', '>=', $numMinBedrooms);
        }
        if (! empty($builtMin)) {
            $propertiesQuery->where('meters_built', '>=', $builtMin);
        }
        if (! empty($builtMax)) {
            $propertiesQuery->where('meters_built', '<=', $builtMax);
        }
        if (! empty($typeId)) {
            $propertiesQuery->where('type_id', $typeId);
        }

        $quantityDataView = 15;
        $numberPosition = (int) ($request->query('page') ?: 1);

        $propertiesAll = $propertiesQuery->orderByDesc('id')->get();
        $quantityBlockNav = (int) round($propertiesAll->count() / $quantityDataView);
        $propertiesPage = $propertiesAll
            ->slice(($quantityDataView * $numberPosition) - $quantityDataView, $quantityDataView)
            ->values();

        $quantity = $propertiesPage->count();

        $provinces = PropertyAddress::query()
            ->select('property_address.province', DB::raw('COUNT(*) as total'))
            ->join('property', 'property.id', '=', 'property_address.property_id')
            ->where('property.state_id', 4)
            ->groupBy('property_address.province')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        $cities = PropertyAddress::query()
            ->select('property_address.city', DB::raw('COUNT(*) as total'))
            ->join('property', 'property.id', '=', 'property_address.property_id')
            ->where('property.state_id', 4)
            ->groupBy('property_address.city')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        $provinceCitiesList = PropertyAddress::query()
            ->select('property_address.province', 'property_address.city', DB::raw('COUNT(*) as total'))
            ->join('property', 'property.id', '=', 'property_address.property_id')
            ->where('property.state_id', 4)
            ->groupBy('property_address.province', 'property_address.city')
            ->orderBy('property_address.province')
            ->orderByDesc('total')
            ->get();

        $provinceCities = [];
        foreach ($provinceCitiesList as $row) {
            $provinceKey = $row->province;
            $provinceCities[$provinceKey][] = [
                'city' => $row->city,
                'total' => $row->total,
            ];
        }

        $updatedProperties = [];
        foreach ($propertiesPage as $property) {
            $item = $property->toArray();
            $item['updated_at_text'] = $this->formatUpdatedAt($property->updated_at);
            $item['type_name'] = Type::find($property->type_id)?->name ?? '';
            $item['category_name'] = Category::find($property->category_id)?->name ?? '';
            $coverImage = CoverImage::where('property_id', $property->id)->first();
            $item['cover_image'] = $coverImage ? $coverImage->toArray() : ['url' => ''];

            $user = User::find($property->user_id);
            $item['user_name'] = $user?->user_name ?? '';

            $item['state_conservation'] = $this->wrapSingle(StateConservation::find($property->state_conservation_id));
            $item['facade'] = $this->wrapSingle(Facade::find($property->facade_id));
            $item['nearest_municipality_distance'] = $this->wrapSingle(
                NearestMunicipalityDistance::find($property->nearest_municipality_distance_id)
            );
            $item['type_of_terrain'] = $this->wrapSingle(TypeOfTerrain::find($property->type_of_terrain_id));
            $item['wheeled_access'] = $this->wrapSingle(WheeledAccess::find($property->wheeled_access_id));

            $updatedProperties[] = $item;

            $exists = PsViewsSearch::query()
                ->where('property_id', $property->id)
                ->where('ip_address', $request->ip())
                ->first();

            if (! $exists) {
                PsViewsSearch::create([
                    'property_id' => $property->id,
                    'ip_address' => $request->ip(),
                    'counter' => 1,
                ]);
            }
        }

        return view('page.result_all', [
            'number_position' => $numberPosition,
            'quantity_block_nav' => $quantityBlockNav,
            'properties' => $updatedProperties,
            'quantity' => $quantity,
            'address' => $address,
            'type_id' => $typeId,
            'category_id' => $categoryId,
            'p_max' => $pMax,
            'p_min' => $pMin,
            'built_min' => $builtMin,
            'built_max' => $builtMax,
            'n_ber' => $numMinBedrooms,
            'n_bar' => $numMinBathrooms,
            'mode' => $mode,
            'provinces' => $provinces,
            'cities' => $cities,
            'provinceCities' => $provinceCities,
            'city' => $city,
            'province' => $province,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => $zoom,
        ]);
    }

    public function resultAllServices()
    {
        Carbon::setLocale('es');

        $request = request();

        $sti = $request->query('sti');
        $mode = $request->query('mode');
        $address = $request->query('address');

        $city = $request->query('city');
        $province = $request->query('province');
        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $zoom = $request->query('zoom');

        $serviceTypeIds = [];

        if (! empty($sti)) {
            if (is_array($sti)) {
                $sti = array_map('intval', $sti);
            } else {
                $sti = [(int) $sti];
            }

            $serviceTypeIds = ServiceTypeLink::query()
                ->whereIn('service_type_id', $sti)
                ->pluck('service_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $servicesQuery = Service::query();

        if (! empty($serviceTypeIds)) {
            $servicesQuery->whereIn('id', $serviceTypeIds);
        }

        if (! empty($city) || ! empty($province)) {
            $addressQuery = UserAddress::query();
            if (! empty($province) && empty($city)) {
                $addressQuery->where('province', trim($province));
            } elseif (! empty($city)) {
                $addressQuery->where('city', trim($city));
            }

            $ids = $addressQuery->pluck('user_id')->map(fn ($id) => (int) $id)->all();
            if (! empty($ids)) {
                $servicesQuery->whereIn('user_id', $ids);
            } else {
                $servicesQuery->where('user_id', 0);
            }
        } elseif (! empty($address)) {
            $addressParts = explode(',', $address);
            $addressSeed = trim($addressParts[0]);

            $ids = UserAddress::query()
                ->where('address', 'like', '%' . trim($address) . '%')
                ->orWhere('address', 'like', '%' . $addressSeed . '%')
                ->orWhere('province', 'like', '%' . $addressSeed . '%')
                ->orWhere('city', 'like', '%' . $addressSeed . '%')
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (! empty($ids)) {
                $servicesQuery->whereIn('user_id', $ids);
            } else {
                $servicesQuery->where('user_id', 0);
            }
        } else {
            $ids = UserAddress::query()
                ->where('address', 'like', '%barcelona%')
                ->orWhere('province', 'like', '%barcelona%')
                ->orWhere('city', 'like', '%barcelona%')
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (! empty($ids)) {
                $servicesQuery->whereIn('user_id', $ids);
            } else {
                $servicesQuery->where('user_id', 0);
            }
        }

        $quantityDataView = 15;
        $numberPosition = (int) ($request->query('page') ?: 1);

        $servicesAll = $servicesQuery->orderByDesc('id')->get();
        $quantityBlockNav = (int) round($servicesAll->count() / $quantityDataView);
        $servicesPage = $servicesAll
            ->slice(($quantityDataView * $numberPosition) - $quantityDataView, $quantityDataView)
            ->values();

        $quantity = $servicesPage->count();

        $provinces = UserAddress::query()
            ->select('province', DB::raw('COUNT(*) as total'))
            ->groupBy('province')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        $cities = UserAddress::query()
            ->select('city', DB::raw('COUNT(*) as total'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        $provinceCitiesList = UserAddress::query()
            ->select('province', 'city', DB::raw('COUNT(*) as total'))
            ->groupBy('province', 'city')
            ->orderBy('province')
            ->orderByDesc('total')
            ->get();

        $provinceCities = [];
        foreach ($provinceCitiesList as $row) {
            $provinceKey = $row->province;
            $provinceCities[$provinceKey][] = [
                'city' => $row->city,
                'total' => $row->total,
            ];
        }

        $updatedServices = [];
        foreach ($servicesPage as $service) {
            $item = $service->toArray();
            $item['updated_at_text'] = $this->formatUpdatedAt($service->updated_at);

            $coverImage = CoverImage::where('service_id', $service->id)->first();
            $item['cover_image'] = $coverImage ? $coverImage->toArray() : ['url' => ''];

            $item['user'] = $this->wrapSingle(User::find($service->user_id));
            $item['user_address'] = UserAddress::where('user_id', $service->user_id)->get()->toArray();

            $serviceTypeLinks = ServiceTypeLink::where('service_id', $service->id)
                ->pluck('service_type_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (! empty($serviceTypeLinks)) {
                $item['service_types'] = ServiceType::whereIn('id', $serviceTypeLinks)->get()->toArray();
            } else {
                $item['service_types'] = [];
            }

            $updatedServices[] = $item;
        }

        $serviceTypes = ServiceType::orderBy('name')->get()->toArray();

        return view('page.result_all_service', [
            'service_type' => $serviceTypes,
            'number_position' => $numberPosition,
            'quantity_block_nav' => $quantityBlockNav,
            'properties' => $updatedServices,
            'quantity' => $quantity,
            'address' => $address,
            'mode' => $mode,
            'provinces' => $provinces,
            'cities' => $cities,
            'provinceCities' => $provinceCities,
            'city' => $city,
            'province' => $province,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => $zoom,
            'sti' => $sti,
        ]);
    }

    public function resultMaps()
    {
        return view('page.placeholder', ['title' => 'Results Map']);
    }

    public function result(string $reference)
    {
        Carbon::setLocale('es');

        $request = request();

        $property = Property::query()
            ->where('reference', $reference)
            ->where('state_id', 4)
            ->first();

        if (! $property) {
            return redirect('/');
        }

        $item = $property->toArray();
        $item['updated_at_text'] = $this->formatUpdatedAt($property->updated_at);
        $item['type_name'] = Type::find($property->type_id)?->name ?? '';
        $item['category_name'] = Category::find($property->category_id)?->name ?? '';
        $coverImage = CoverImage::where('property_id', $property->id)->first();
        $item['cover_image'] = $coverImage ? $coverImage->toArray() : [];
        $item['more_images'] = MoreImage::where('property_id', $property->id)->get()->toArray();

        $item['city'] = $this->wrapSingle(City::find($property->city_id));
        $item['province'] = $this->wrapSingle(Province::find($property->province_id));
        $item['country'] = $this->wrapSingle(Country::find($property->country_id));
        $item['typology'] = $this->wrapSingle(Typology::find($property->typology_id));

        $item['type_heating'] = $this->wrapSingle(TypeHeating::find($property->type_heating_id));
        $item['emissions_rating'] = $this->wrapSingle(EmissionsRating::find($property->emissions_rating_id));
        $item['energy_class'] = $this->wrapSingle(EnergyClass::find($property->energy_class_id));
        $item['state_conservation'] = $this->wrapSingle(StateConservation::find($property->state_conservation_id));
        $item['visibility_in_portals'] = $this->wrapSingle(
            VisibilityInPortals::find($property->visibility_in_portals_id)
        );
        $item['rental_type'] = $this->wrapSingle(RentalType::find($property->rental_type_id));
        $item['contact_option'] = $this->wrapSingle(ContactOption::find($property->contact_option_id));
        $item['power_consumption_rating'] = $this->wrapSingle(
            PowerConsumptionRating::find($property->power_consumption_rating_id)
        );
        $item['reason_for_sale'] = $this->wrapSingle(ReasonForSale::find($property->reason_for_sale_id));
        $item['facade'] = $this->wrapSingle(Facade::find($property->facade_id));
        $item['videos'] = Video::where('property_id', $property->id)->get()->toArray();
        $item['plant'] = $this->wrapSingle(Plant::find($property->plant_id));
        $item['plaza_capacity'] = $this->wrapSingle(PlazaCapacity::find($property->plaza_capacity_id));

        $item['nearest_municipality_distance'] = $this->wrapSingle(
            NearestMunicipalityDistance::find($property->nearest_municipality_distance_id)
        );
        $item['wheeled_access'] = $this->wrapSingle(WheeledAccess::find($property->wheeled_access_id));
        $item['type_of_terrain'] = $this->wrapSingle(TypeOfTerrain::find($property->type_of_terrain_id));
        $item['terrain_use'] = $this->wrapSingle(TerrainUse::find($property->terrain_use_id));
        $item['terrain_qualifications'] = [];
        $terrainQualificationLinks = TerrainQualifications::where('property_id', $property->id)->get();
        foreach ($terrainQualificationLinks as $terrainQualificationLink) {
            $terrainQualification = TerrainQualification::find($terrainQualificationLink->terrain_qualification_id);
            if ($terrainQualification) {
                $item['terrain_qualifications'][] = $terrainQualification->toArray();
            }
        }
        $item['property_address'] = PropertyAddress::where('property_id', $property->id)->get()->toArray();
        $item['user'] = User::find($property->user_id)?->toArray() ?? [];

        $item['features'] = [];
        $featureLinks = Features::where('property_id', $property->id)->get();
        foreach ($featureLinks as $featureLink) {
            $feature = Feature::find($featureLink->feature_id);
            if ($feature) {
                $item['features'][] = $feature->toArray();
            }
        }

        $item['equipments'] = [];
        $equipmentLinks = Equipments::where('property_id', $property->id)->get();
        foreach ($equipmentLinks as $equipmentLink) {
            $equipment = Equipment::find($equipmentLink->equipment_id);
            if ($equipment) {
                $item['equipments'][] = $equipment->toArray();
            }
        }

        $item['orientations'] = [];
        $orientationLinks = Orientations::where('property_id', $property->id)->get();
        foreach ($orientationLinks as $orientationLink) {
            $orientation = Orientation::find($orientationLink->orientation_id);
            if ($orientation) {
                $item['orientations'][] = $orientation->toArray();
            }
        }

        $item['types_floors'] = [];
        $typesFloorLinks = TypesFloors::where('property_id', $property->id)->get();
        foreach ($typesFloorLinks as $typesFloorLink) {
            $typeFloor = TypeFloor::find($typesFloorLink->type_floor_id);
            if ($typeFloor) {
                $item['types_floors'][] = $typeFloor->toArray();
            }
        }

        $exists = PsViewsDetail::query()
            ->where('property_id', $property->id)
            ->where('ip_address', $request->ip())
            ->first();

        if (! $exists) {
            PsViewsDetail::create([
                'property_id' => $property->id,
                'ip_address' => $request->ip(),
                'counter' => 1,
            ]);
        }

        return view('page.details', ['property' => $item]);
    }

    public function resultService(string $id)
    {
        Carbon::setLocale('es');

        $service = Service::query()->where('id', $id)->first();
        if (! $service) {
            return redirect('/');
        }

        $item = $service->toArray();
        $item['updated_at_text'] = $this->formatUpdatedAt($service->updated_at);

        $coverImage = CoverImage::where('service_id', $service->id)->first();
        $item['cover_image'] = $coverImage ? $coverImage->toArray() : [];
        $item['more_images'] = MoreImage::where('service_id', $service->id)->get()->toArray();

        $item['videos'] = Video::where('service_id', $service->id)->get()->toArray();
        $item['address'] = UserAddress::where('user_id', $service->user_id)->get()->toArray();
        $item['user'] = User::find($service->user_id)?->toArray() ?? [];

        $item['service_types'] = [];
        $serviceTypeLinks = ServiceTypeLink::where('service_id', $service->id)->get();
        foreach ($serviceTypeLinks as $serviceTypeLink) {
            $serviceType = ServiceType::find($serviceTypeLink->service_type_id);
            if ($serviceType) {
                $item['service_types'][] = $serviceType->toArray();
            }
        }

        return view('page.details_service', ['property' => $item]);
    }

    public function signup(Request $request)
    {
        return redirect()->route('login');
    }

    public function validateAccountPage()
    {
        return view('page.placeholder', ['title' => 'Validate Account']);
    }

    public function validateAccount(Request $request)
    {
        return redirect('/');
    }

    public function policyAndPrivacy()
    {
        return view('page.placeholder', ['title' => 'Policy and Privacy']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    private function wrapSingle($model): array
    {
        if (! $model) {
            return [];
        }

        return [$model->toArray()];
    }

    private function formatUpdatedAt($value): string
    {
        if (! $value) {
            return '';
        }

        return Carbon::parse($value)->translatedFormat('d \\d\\e F \\d\\e Y');
    }
}
