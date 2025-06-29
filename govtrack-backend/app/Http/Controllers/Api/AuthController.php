<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Connexion utilisateur
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Les informations d\'identification fournies sont incorrectes.'],
                ]);
            }

            if (!$user->statut) {
                return response()->json([
                    'message' => 'Votre compte est désactivé. Contactez l\'administrateur.',
                    'success' => false
                ], 403);
            }

            // Supprimer les anciens tokens
            $user->tokens()->delete();

            // Créer un nouveau token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Charger les relations nécessaires
            $user->load(['roles.permissions', 'affectations.poste', 'affectations.entite']);

            // Obtenir l'affectation actuelle
            $affectationActuelle = $user->affectationActuelle();

            // Obtenir les entités dirigées actuellement
            $entitesDigees = $user->entitesDigees()
                ->with('entite')
                ->whereNull('date_fin')
                ->get();

            return response()->json([
                'message' => 'Connexion réussie',
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'matricule' => $user->matricule,
                        'nom' => $user->nom,
                        'prenom' => $user->prenom,
                        'name' => $user->name,
                        'email' => $user->email,
                        'telephone' => $user->telephone,
                        'adresse' => $user->adresse,
                        'photo' => $user->photo,
                        'statut' => $user->statut,
                        'affectation_actuelle' => $affectationActuelle ? [
                            'poste' => $affectationActuelle->poste->nom,
                            'entite' => $affectationActuelle->entite->nom,
                            'date_debut' => $affectationActuelle->date_debut,
                        ] : null,
                        'entites_dirigees' => $entitesDigees->map(function ($item) {
                            return [
                                'entite_id' => $item->entite_id,
                                'entite_nom' => $item->entite->nom,
                                'date_debut' => $item->date_debut,
                            ];
                        }),
                        'roles' => $user->roles->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'nom' => $role->nom,
                                'description' => $role->description,
                                'permissions' => $role->permissions->pluck('nom')
                            ];
                        }),
                        'permissions' => $user->getAllPermissions()->pluck('nom')->unique()->values(),
                    ],
                    'token' => $token,
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la connexion',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déconnexion utilisateur
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Déconnexion réussie',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la déconnexion',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les informations de l'utilisateur connecté
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load(['roles.permissions', 'affectations.poste', 'affectations.entite']);

            $affectationActuelle = $user->affectationActuelle();

            $entitesDigees = $user->entitesDigees()
                ->with('entite')
                ->whereNull('date_fin')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'matricule' => $user->matricule,
                        'nom' => $user->nom,
                        'prenom' => $user->prenom,
                        'name' => $user->name,
                        'email' => $user->email,
                        'telephone' => $user->telephone,
                        'adresse' => $user->adresse,
                        'photo' => $user->photo,
                        'statut' => $user->statut,
                        'affectation_actuelle' => $affectationActuelle ? [
                            'poste' => $affectationActuelle->poste->nom,
                            'entite' => $affectationActuelle->entite->nom,
                            'date_debut' => $affectationActuelle->date_debut,
                        ] : null,
                        'entites_dirigees' => $entitesDigees->map(function ($item) {
                            return [
                                'entite_id' => $item->entite_id,
                                'entite_nom' => $item->entite->nom,
                                'date_debut' => $item->date_debut,
                            ];
                        }),
                        'roles' => $user->roles->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'nom' => $role->nom,
                                'description' => $role->description,
                                'permissions' => $role->permissions->pluck('nom')
                            ];
                        }),
                        'permissions' => $user->getAllPermissions()->pluck('nom')->unique()->values(),
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des informations utilisateur',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rafraîchir le token d'authentification
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Supprimer le token actuel
            $request->user()->currentAccessToken()->delete();

            // Créer un nouveau token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Token rafraîchi avec succès',
                'success' => true,
                'data' => [
                    'token' => $token,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du rafraîchissement du token',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déconnexion de tous les appareils
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json([
                'message' => 'Déconnexion de tous les appareils réussie',
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la déconnexion de tous les appareils',
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
