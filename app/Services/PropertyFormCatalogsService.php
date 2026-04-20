<?php

namespace App\Services;

use App\Models\EmissionsRating;
use App\Models\Equipment;
use App\Models\Facade;
use App\Models\Feature;
use App\Models\HeatingFuel;
use App\Models\LocationPremises;
use App\Models\NearestMunicipalityDistance;
use App\Models\Orientation;
use App\Models\Plant;
use App\Models\PlazaCapacity;
use App\Models\PowerConsumptionRating;
use App\Models\ReasonForSale;
use App\Models\RentalType;
use App\Models\StateConservation;
use App\Models\TerrainUse;
use App\Models\TypeFloor;
use App\Models\TypeHeating;
use App\Models\TypeOfTerrain;
use App\Models\Typology;
use App\Models\VisibilityInPortals;
use App\Models\WheeledAccess;
use Illuminate\Support\Collection;

class PropertyFormCatalogsService
{
    public const TYPE_HOUSE_CHALET = 1;
    public const TYPE_LOCAL_PREMISES = 4;
    public const TYPE_LAND = 9;
    public const TYPE_APARTMENT = 13;
    public const TYPE_GARAGE = 14;
    public const TYPE_RUSTIC_HOUSE = 15;

    private const SUPPORTED_TYPE_IDS = [
        self::TYPE_HOUSE_CHALET,
        self::TYPE_LOCAL_PREMISES,
        self::TYPE_LAND,
        self::TYPE_APARTMENT,
        self::TYPE_GARAGE,
        self::TYPE_RUSTIC_HOUSE,
    ];

    public function supportsType(int $typeId): bool
    {
        return in_array($typeId, self::SUPPORTED_TYPE_IDS, true);
    }

    public function buildForType(int $typeId): array
    {
        return [
            'plant' => $this->mapOptions(Plant::query()->orderBy('id')->get()),
            'type_floor' => $this->usesFloorTypes($typeId)
                ? $this->mapOptions(TypeFloor::query()->orderBy('id')->get())
                : [],
            'visibility_in_portals' => $this->mapOptions(VisibilityInPortals::query()->orderBy('id')->get()),
            'rental_type' => $this->usesRentalType($typeId)
                ? $this->mapOptions(RentalType::query()->orderBy('id')->get())
                : [],
            'reason_for_sale' => $this->usesReasonForSale($typeId)
                ? $this->mapOptions(ReasonForSale::query()->orderBy('id')->get())
                : [],
            'typology' => $this->resolveTypologyOptions($typeId),
            'orientation' => $this->usesOrientation($typeId)
                ? $this->mapOptions(Orientation::query()->orderBy('id')->get())
                : [],
            'type_heating' => $this->usesHeating($typeId)
                ? $this->mapOptions(TypeHeating::query()->orderBy('id')->get())
                : [],
            'heating_fuel' => $this->usesHeating($typeId)
                ? $this->mapOptions(HeatingFuel::query()->orderBy('id')->get())
                : [],
            'power_consumption_rating' => $this->usesEnergyRatings($typeId)
                ? $this->mapOptions(PowerConsumptionRating::query()->orderBy('id')->get())
                : [],
            'emissions_rating' => $this->usesEnergyRatings($typeId)
                ? $this->mapOptions(EmissionsRating::query()->orderBy('id')->get())
                : [],
            'state_conservation' => $this->mapOptions(StateConservation::query()->orderBy('id')->get()),
            'facade' => $this->usesFacade($typeId)
                ? $this->mapOptions(Facade::query()->orderBy('id')->get())
                : [],
            'feature' => $this->resolveFeatureOptions($typeId),
            'equipment' => $this->resolveEquipmentOptions($typeId),
            'plaza_capacity' => $typeId === self::TYPE_GARAGE
                ? $this->mapOptions(PlazaCapacity::query()->orderBy('id')->get())
                : [],
            'type_of_terrain' => $typeId === self::TYPE_LAND
                ? $this->resolveTerrainTypeOptions()
                : [],
            'terrain_use' => $typeId === self::TYPE_LAND
                ? $this->mapOptions(TerrainUse::query()->orderBy('id')->get())
                : [],
            'wheeled_access' => $typeId === self::TYPE_LAND
                ? $this->mapOptions(WheeledAccess::query()->orderBy('id')->get())
                : [],
            'nearest_municipality_distance' => $typeId === self::TYPE_LAND
                ? $this->mapOptions(NearestMunicipalityDistance::query()->orderBy('id')->get())
                : [],
            'location_premises' => $typeId === self::TYPE_LOCAL_PREMISES
                ? $this->mapOptions(LocationPremises::query()->orderBy('id')->get())
                : [],
        ];
    }

    private function mapOptions(Collection $items): array
    {
        return $items->map(function ($item) {
            $id = (string) ($item->id ?? '');
            $name = trim((string) ($item->name ?? ''));

            return [
                'value' => $id,
                'label' => $name,
            ];
        })->filter(fn (array $item) => $item['value'] !== '' && $item['label'] !== '')
            ->values()
            ->all();
    }

    private function resolveTypologyOptions(int $typeId): array
    {
        if ($typeId === self::TYPE_HOUSE_CHALET || $typeId === self::TYPE_APARTMENT) {
            return $this->mapOptions(Typology::query()->where('type_id', self::TYPE_HOUSE_CHALET)->orderBy('id')->get());
        }

        if ($typeId === self::TYPE_RUSTIC_HOUSE) {
            return $this->mapOptions(Typology::query()->where('type_id', self::TYPE_RUSTIC_HOUSE)->orderBy('id')->get());
        }

        return [];
    }

    private function resolveFeatureOptions(int $typeId): array
    {
        if ($typeId === self::TYPE_GARAGE) {
            return $this->mapOptions(Feature::query()->where('id_type', self::TYPE_GARAGE)->orderBy('id')->get());
        }

        if ($typeId === self::TYPE_HOUSE_CHALET || $typeId === self::TYPE_APARTMENT) {
            return $this->mapOptions(Feature::query()->where('id_type', self::TYPE_HOUSE_CHALET)->orderBy('id')->get());
        }

        if ($typeId === self::TYPE_RUSTIC_HOUSE) {
            return $this->mapOptions(Feature::query()->orderBy('id')->get());
        }

        return [];
    }

    private function resolveEquipmentOptions(int $typeId): array
    {
        if ($typeId === self::TYPE_HOUSE_CHALET || $typeId === self::TYPE_APARTMENT) {
            return $this->mapOptions(Equipment::query()->where('type_id', self::TYPE_HOUSE_CHALET)->orderBy('id')->get());
        }

        if ($typeId === self::TYPE_LOCAL_PREMISES || $typeId === self::TYPE_LAND) {
            return $this->mapOptions(Equipment::query()->where('type_id', self::TYPE_LOCAL_PREMISES)->orderBy('id')->get());
        }

        if ($typeId === self::TYPE_GARAGE) {
            return $this->mapOptions(Equipment::query()->where('type_id', self::TYPE_GARAGE)->orderBy('id')->get());
        }

        if ($typeId === self::TYPE_RUSTIC_HOUSE) {
            return $this->mapOptions(Equipment::query()->orderBy('id')->get());
        }

        return [];
    }

    private function resolveTerrainTypeOptions(): array
    {
        return $this->mapOptions(
            TypeOfTerrain::query()
                ->whereIn('name', ['Urbano', 'Urbanizable', 'Rústico'])
                ->orderBy('id')
                ->get()
        );
    }

    private function usesFloorTypes(int $typeId): bool
    {
        return in_array($typeId, [self::TYPE_HOUSE_CHALET, self::TYPE_APARTMENT, self::TYPE_RUSTIC_HOUSE], true);
    }

    private function usesRentalType(int $typeId): bool
    {
        return in_array($typeId, [self::TYPE_HOUSE_CHALET, self::TYPE_APARTMENT, self::TYPE_RUSTIC_HOUSE], true);
    }

    private function usesReasonForSale(int $typeId): bool
    {
        return in_array($typeId, [self::TYPE_HOUSE_CHALET, self::TYPE_APARTMENT, self::TYPE_RUSTIC_HOUSE], true);
    }

    private function usesOrientation(int $typeId): bool
    {
        return in_array($typeId, [self::TYPE_HOUSE_CHALET, self::TYPE_APARTMENT, self::TYPE_RUSTIC_HOUSE], true);
    }

    private function usesHeating(int $typeId): bool
    {
        return in_array(
            $typeId,
            [
                self::TYPE_HOUSE_CHALET,
                self::TYPE_APARTMENT,
                self::TYPE_LOCAL_PREMISES,
                self::TYPE_RUSTIC_HOUSE,
            ],
            true
        );
    }

    private function usesEnergyRatings(int $typeId): bool
    {
        return in_array(
            $typeId,
            [
                self::TYPE_HOUSE_CHALET,
                self::TYPE_APARTMENT,
                self::TYPE_LOCAL_PREMISES,
                self::TYPE_GARAGE,
                self::TYPE_LAND,
                self::TYPE_RUSTIC_HOUSE,
            ],
            true
        );
    }

    private function usesFacade(int $typeId): bool
    {
        return in_array($typeId, [self::TYPE_HOUSE_CHALET, self::TYPE_APARTMENT, self::TYPE_RUSTIC_HOUSE], true);
    }
}
