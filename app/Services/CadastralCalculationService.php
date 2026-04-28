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
}
