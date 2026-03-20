<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $properties = Property::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'data' => $properties->items(),
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

        $property = Property::query()->find($id);
        if (! $property) {
            return response()->json(['message' => 'Propiedad no encontrada'], 404);
        }

        if ((int) $property->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($property, 200);
    }

    // Kept for route compatibility with apiResource.
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos invalidos', 'errors' => $validator->errors()], 422);
        }

        return response()->json(['message' => 'No implementado en esta fase'], 501);
    }

    public function update(Request $request, int $id)
    {
        return response()->json(['message' => 'No implementado en esta fase'], 501);
    }

    public function destroy(Request $request, int $id)
    {
        return response()->json(['message' => 'No implementado en esta fase'], 501);
    }
}
