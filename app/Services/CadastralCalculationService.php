<?php

namespace App\Services;

use App\Models\CadastralPrice;
use Illuminate\Support\Facades\DB;

class CadastralCalculationService
{
    /**
     * Calculate cadastral statistics for a given postal code or municipality.
     */
    public function calculateStats(string $postalCode, ?string $municipality = null)
    {
        $query = CadastralPrice::where('postal_code', $postalCode);

        if ($municipality) {
            $query->where('municipality', 'like', "%{$municipality}%");
        }

        $stats = $query->select(
            DB::raw('AVG(price_m2_eur) as avg_price'),
            DB::raw('MIN(price_m2_eur) as min_price'),
            DB::raw('MAX(price_m2_eur) as max_price'),
            DB::raw('COUNT(*) as total_records')
        )->first();

        // Fallback: If no records for this postal code, but we have a municipality, search by municipality average
        if ($stats->total_records == 0 && $municipality) {
            $fallbackQuery = CadastralPrice::where('municipality', 'like', "%{$municipality}%");
            $stats = $fallbackQuery->select(
                DB::raw('AVG(price_m2_eur) as avg_price'),
                DB::raw('MIN(price_m2_eur) as min_price'),
                DB::raw('MAX(price_m2_eur) as max_price'),
                DB::raw('COUNT(*) as total_records')
            )->first();

            if ($stats->total_records > 0) {
                $postalCode = "{$postalCode} (Media de {$municipality})";
            }
        }

        return [
            'postal_code' => $postalCode,
            'municipality' => $municipality,
            'avg_price_m2' => $stats->avg_price ? round((float)$stats->avg_price, 2) : 0,
            'min_price_m2' => $stats->min_price ? round((float)$stats->min_price, 2) : 0,
            'max_price_m2' => $stats->max_price ? round((float)$stats->max_price, 2) : 0,
            'total_areas' => $stats->total_records,
        ];
    }
    
    /**
     * Calculate the estimated property value based on m2 and location.
     */
    public function estimatePropertyValue(float $squareMeters, string $postalCode, ?string $municipality = null)
    {
        $stats = $this->calculateStats($postalCode, $municipality);
        
        if ($stats['total_areas'] === 0) {
            return null; // No data available to estimate
        }
        
        return [
            'estimated_value' => round($stats['avg_price_m2'] * $squareMeters, 2),
            'min_value' => round($stats['min_price_m2'] * $squareMeters, 2),
            'max_value' => round($stats['max_price_m2'] * $squareMeters, 2),
            'base_stats' => $stats,
            'square_meters' => $squareMeters
        ];
    }

    /**
     * Calculate an advanced estimated property value applying multipliers.
     */
    public function advancedEstimate(array $data)
    {
        $baseEstimation = $this->estimatePropertyValue(
            (float) ($data['m2'] ?? 0),
            $data['postal_code'] ?? '',
            $data['municipality'] ?? null
        );

        if (!$baseEstimation) {
            return null; // No data available
        }

        $multiplier = 1.0;
        $fixedAdditions = 0;

        // 1. Property Type
        $type = (int) ($data['property_type'] ?? 13);
        if ($type === 1) { // Casa/Chalet
            $multiplier += 0.15;
        } elseif ($type === 15) { // Casa rústica
            $multiplier -= 0.05;
        } elseif ($type === 4) { // Local o nave
            $multiplier -= 0.20; // Commercial base lower than residential
        } elseif ($type === 14) { // Garaje
            $multiplier -= 0.60; // Garages are much cheaper per m2
        } elseif ($type === 9) { // Terreno
            $multiplier -= 0.70; // Land is significantly cheaper than built space
        }

        // 2. Condition
        $condition = (int) ($data['state_conservation'] ?? 1);
        if ($condition === 2) { // A reformar
            $multiplier -= 0.15;
        } elseif ($condition === 3) { // Obra nueva
            $multiplier += 0.15;
        }

        // 3. Distribution
        $m2 = (float) ($data['m2'] ?? 0);
        $bedrooms = (int) ($data['bedrooms'] ?? 1);
        $bathrooms = (int) ($data['bathrooms'] ?? 1);
        
        if ($bedrooms > 0) {
            $m2PerBedroom = $m2 / $bedrooms;
            if ($m2PerBedroom >= 40) {
                $multiplier += 0.05; // Premium space
            } elseif ($m2PerBedroom < 15) {
                $multiplier -= 0.05; // Very cramped
            }
        }

        if ($bathrooms >= 2) {
            $multiplier += 0.03;
        }

        // 4. Extras
        $hasElevator = filter_var($data['has_elevator'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $hasParking = filter_var($data['has_parking'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $hasPool = filter_var($data['has_pool'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($type === 13) { // Piso
            if ($hasElevator) {
                $multiplier += 0.05;
            } else {
                $multiplier -= 0.15; // Penalty for no elevator in a flat
            }
        }

        if ($hasPool) {
            $multiplier += 0.08;
        }

        if ($hasParking) {
            $fixedAdditions += 15000;
        }

        $finalEstimatedValue = ($baseEstimation['estimated_value'] * $multiplier) + $fixedAdditions;
        $finalMinValue = ($baseEstimation['min_value'] * $multiplier) + $fixedAdditions;
        $finalMaxValue = ($baseEstimation['max_value'] * $multiplier) + $fixedAdditions;

        return [
            'estimated_value' => round($finalEstimatedValue, 2),
            'min_value' => round($finalMinValue, 2),
            'max_value' => round($finalMaxValue, 2),
            'multiplier_applied' => round($multiplier, 2),
            'base_estimation' => $baseEstimation
        ];
    }
}
