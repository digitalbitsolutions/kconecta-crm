<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CadastralCalculationService;

class CadastralController extends Controller
{
    protected $cadastralService;

    public function __construct(CadastralCalculationService $cadastralService)
    {
        $this->cadastralService = $cadastralService;
    }

    public function estimate(Request $request)
    {
        $validated = $request->validate([
            'postal_code' => 'required|string|max:10',
            'm2' => 'required|numeric|min:1',
            'municipality' => 'nullable|string|max:255',
        ]);

        $result = $this->cadastralService->estimatePropertyValue(
            $validated['m2'],
            $validated['postal_code'],
            $validated['municipality'] ?? null
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos catastrales suficientes para este código postal.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function advancedEstimate(Request $request)
    {
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

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos catastrales suficientes para este código postal/municipio.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}
