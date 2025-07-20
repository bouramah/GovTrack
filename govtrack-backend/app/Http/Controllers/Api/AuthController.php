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
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Services\LoginActivityService;

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

            // Enregistrer l'activité de connexion
            LoginActivityService::logLogin($user, $request, $token);

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
                        'photo' => $user->photo_url,
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
            // Enregistrer la tentative de connexion échouée
            LoginActivityService::logFailedLogin($request->email, $request);

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
            $user = $request->user();
            $token = $user->currentAccessToken();

            // Enregistrer l'activité de déconnexion
            LoginActivityService::logLogout($user, $request, $token->id);

            $token->delete();

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
                        'photo' => $user->photo_url,
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
                    ]
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
     * Upload de photo de profil
     */
    public function uploadProfilePhoto(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max
            ]);

            $photo = $request->file('photo');

            // Supprimer l'ancienne photo si elle existe
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            // Générer un nom unique pour la photo
            $nomPhoto = 'users/photos/' . $user->id . '_' . time() . '.' . $photo->getClientOriginalExtension();

            // Stocker la photo
            $path = $photo->storeAs('users/photos', basename($nomPhoto), 'public');

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du stockage de la photo'
                ], 500);
            }

            // Mettre à jour le chemin de la photo dans la base de données
            $user->update([
                'photo' => $path,
                'date_modification' => Carbon::now(),
                'modifier_par' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo de profil mise à jour avec succès',
                'data' => [
                    'photo_url' => $path,
                    'photo_full_url' => Storage::disk('public')->url($path)
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la photo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le profil utilisateur
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'matricule' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('users')->ignore($user->id)
                ],
                'nom' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id)
                ],
                'telephone' => 'nullable|string|max:20',
                'adresse' => 'nullable|string|max:500',
                'password' => 'nullable|string|min:8',
            ]);

            // Construire les données de mise à jour seulement avec les champs fournis
            $updateData = [
                'date_modification' => Carbon::now(),
                'modifier_par' => $user->email,
            ];

            if (isset($validated['matricule'])) {
                $updateData['matricule'] = $validated['matricule'];
            }

            if (isset($validated['nom'])) {
                $updateData['nom'] = $validated['nom'];
            }

            if (isset($validated['prenom'])) {
                $updateData['prenom'] = $validated['prenom'];
            }

            // Reconstruire le name si nom ou prenom ont changé
            if (isset($validated['nom']) || isset($validated['prenom'])) {
                $nom = $validated['nom'] ?? $user->nom;
                $prenom = $validated['prenom'] ?? $user->prenom;
                $updateData['name'] = $prenom . ' ' . $nom;
            }

            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }

            if (isset($validated['telephone'])) {
                $updateData['telephone'] = $validated['telephone'];
            }

            if (isset($validated['adresse'])) {
                $updateData['adresse'] = $validated['adresse'];
            }

            // Mettre à jour le mot de passe seulement s'il est fourni
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // Recharger les relations
            $user->load(['roles.permissions', 'affectations.poste', 'affectations.entite']);

            $affectationActuelle = $user->affectationActuelle();
            $entitesDigees = $user->entitesDigees()
                ->with('entite')
                ->whereNull('date_fin')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
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
                        'photo' => $user->photo_url,
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
                    ]
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
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
