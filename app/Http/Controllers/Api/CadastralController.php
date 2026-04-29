<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CadastralCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CadastralController extends Controller
{
    protected $cadastralService;

    public function __construct(CadastralCalculationService $cadastralService)
    {
        $this->cadastralService = $cadastralService;
    }

    public function estimate(Request $request)
    {
        try {
            $validated = $request->validate([
                'postal_code' => 'required|string|max:10',
                'm2' => 'required|numeric|min:1',
                'municipality' => 'nullable|string|max:255',
            ]);

            $result = $this->cadastralService->estimatePropertyValue(
                (float) $validated['m2'],
                (string) $validated['postal_code'],
                $validated['municipality'] ?? null
            );

            if (! $result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron datos catastrales suficientes para este codigo postal.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Throwable $exception) {
            Log::error('Cadastral estimate failed', [
                'error' => $exception->getMessage(),
                'postal_code' => $request->input('postal_code'),
                'municipality' => $request->input('municipality'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Servicio temporalmente no disponible. Intentalo de nuevo en unos minutos.',
            ], 500);
        }
    }

    public function advancedEstimate(Request $request)
    {
        try {
            $validated = $request->validate([
                'postal_code' => 'required|string|max:10',
                'm2' => 'required|numeric|min:1',
                'municipality' => 'nullable|string|max:255',
                'property_type' => 'nullable|integer',
                'state_conservation' => 'nullable|integer',
                'bedrooms' => 'nullable|integer',
                'bathrooms' => 'nullable|integer',
                'has_elevator' => 'nullable|boolean',
                'has_parking' => 'nullable|boolean',
                'has_pool' => 'nullable|boolean',
            ]);

            $result = $this->cadastralService->advancedEstimate($validated);

            if (! $result) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron datos catastrales suficientes para este codigo postal o municipio.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Throwable $exception) {
            Log::error('Cadastral advanced estimate failed', [
                'error' => $exception->getMessage(),
                'postal_code' => $request->input('postal_code'),
                'municipality' => $request->input('municipality'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Servicio temporalmente no disponible. Intentalo de nuevo en unos minutos.',
            ], 500);
        }
    }
}
