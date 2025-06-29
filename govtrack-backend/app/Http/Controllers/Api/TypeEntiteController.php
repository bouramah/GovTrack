<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeEntite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TypeEntiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $typeEntites = TypeEntite::with('entites')->get();

        return response()->json([
            'success' => true,
            'data' => $typeEntites,
            'message' => 'Types d\'entités récupérés avec succès'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:type_entites',
            'description' => 'nullable|string',
        ]);

        $now = Carbon::now();
        $typeEntite = TypeEntite::create([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? null,
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $request->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'data' => $typeEntite,
            'message' => 'Type d\'entité créé avec succès'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $typeEntite = TypeEntite::with('entites')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $typeEntite,
            'message' => 'Type d\'entité récupéré avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $typeEntite = TypeEntite::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:type_entites,nom,' . $id,
            'description' => 'nullable|string',
        ]);

        $typeEntite->update([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? $typeEntite->description,
            'date_modification' => Carbon::now(),
            'modifier_par' => $request->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'data' => $typeEntite->fresh(),
            'message' => 'Type d\'entité mis à jour avec succès'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $typeEntite = TypeEntite::findOrFail($id);

        // Vérifier s'il y a des entités liées
        if ($typeEntite->entites()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce type d\'entité car il est utilisé par des entités existantes'
            ], 422);
        }

        $typeEntite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Type d\'entité supprimé avec succès'
        ]);
    }
}
