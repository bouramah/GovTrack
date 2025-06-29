<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poste;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class PosteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $postes = Poste::with(['affectations' => function ($query) {
                            $query->where('statut', true)->with(['user', 'entite']);
                        }])
                       ->orderBy('nom')
                       ->get()
                       ->map(function ($poste) {
                           return [
                               'id' => $poste->id,
                               'nom' => $poste->nom,
                               'description' => $poste->description,
                               'nombre_affectations_actives' => $poste->affectations->count(),
                               'employes_actuels' => $poste->affectations->map(function ($affectation) {
                                   return [
                                       'user' => [
                                           'id' => $affectation->user->id,
                                           'nom' => $affectation->user->nom,
                                           'prenom' => $affectation->user->prenom,
                                           'matricule' => $affectation->user->matricule,
                                       ],
                                       'entite' => $affectation->entite->nom,
                                       'date_debut' => $affectation->date_debut,
                                   ];
                               }),
                               'date_creation' => $poste->date_creation,
                               'creer_par' => $poste->creer_par,
                           ];
                       });

        return response()->json([
            'success' => true,
            'data' => $postes,
            'message' => 'Postes récupérés avec succès'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:postes',
            'description' => 'nullable|string|max:1000',
        ]);

        $now = Carbon::now();
        $poste = Poste::create([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? null,
            'date_creation' => $now,
            'date_modification' => $now,
            'creer_par' => $request->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'data' => $poste,
            'message' => 'Poste créé avec succès'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $poste = Poste::with([
                    'affectations' => function ($query) {
                        $query->with(['user', 'entite.typeEntite'])
                              ->orderBy('date_debut', 'desc');
                    }
                ])->findOrFail($id);

        $response = [
            'id' => $poste->id,
            'nom' => $poste->nom,
            'description' => $poste->description,
            'affectations_actuelles' => $poste->affectations()
                                             ->where('statut', true)
                                             ->with(['user', 'entite'])
                                             ->get()
                                             ->map(function ($affectation) {
                                                 return [
                                                     'id' => $affectation->id,
                                                     'user' => [
                                                         'id' => $affectation->user->id,
                                                         'nom' => $affectation->user->nom,
                                                         'prenom' => $affectation->user->prenom,
                                                         'matricule' => $affectation->user->matricule,
                                                         'email' => $affectation->user->email,
                                                     ],
                                                     'entite' => [
                                                         'id' => $affectation->entite->id,
                                                         'nom' => $affectation->entite->nom,
                                                         'type' => $affectation->entite->typeEntite->nom,
                                                     ],
                                                     'date_debut' => $affectation->date_debut,
                                                     'statut' => $affectation->statut,
                                                 ];
                                             }),
            'historique_affectations' => $poste->affectations()
                                              ->where('statut', false)
                                              ->with(['user', 'entite'])
                                              ->orderBy('date_fin', 'desc')
                                              ->get()
                                              ->map(function ($affectation) {
                                                  return [
                                                      'user' => [
                                                          'nom' => $affectation->user->nom,
                                                          'prenom' => $affectation->user->prenom,
                                                          'matricule' => $affectation->user->matricule,
                                                      ],
                                                      'entite' => $affectation->entite->nom,
                                                      'date_debut' => $affectation->date_debut,
                                                      'date_fin' => $affectation->date_fin,
                                                  ];
                                              }),
            'statistiques' => [
                'total_affectations' => $poste->affectations->count(),
                'affectations_actives' => $poste->affectations()->where('statut', true)->count(),
                'affectations_terminees' => $poste->affectations()->where('statut', false)->count(),
            ],
            'date_creation' => $poste->date_creation,
            'date_modification' => $poste->date_modification,
            'creer_par' => $poste->creer_par,
            'modifier_par' => $poste->modifier_par,
        ];

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Poste récupéré avec succès'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $poste = Poste::findOrFail($id);

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('postes')->ignore($id)
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        $poste->update([
            'nom' => $validated['nom'],
            'description' => $validated['description'] ?? $poste->description,
            'date_modification' => Carbon::now(),
            'modifier_par' => $request->user()->email,
        ]);

        return response()->json([
            'success' => true,
            'data' => $poste->fresh(),
            'message' => 'Poste mis à jour avec succès'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $poste = Poste::findOrFail($id);

        // Vérifier s'il y a des affectations actives
        if ($poste->affectations()->where('statut', true)->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce poste car il a des affectations actives'
            ], 422);
        }

        $poste->delete();

        return response()->json([
            'success' => true,
            'message' => 'Poste supprimé avec succès'
        ]);
    }
}
