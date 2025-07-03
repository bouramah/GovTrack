    public function show(string $id): JsonResponse
    {
        $user = User::with([
            'roles.permissions',
            'affectations.poste',
            'affectations.entite.typeEntite',
            'entitesDigees.entite'
        ])->findOrFail($id);

        // Récupérer l'affectation actuelle
        $affectationActuelle = $user->affectations()
            ->where('statut', true)
            ->with(['poste', 'entite'])
            ->first();

        $response = [
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
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'nom' => $role->nom,
                    'description' => $role->description,
                    'permissions' => $role->permissions->pluck('nom'),
                ];
            }),
            'permissions' => $user->getAllPermissions()->pluck('nom')->unique()->values(),
            'affectation_actuelle' => $affectationActuelle ? [
                'poste' => $affectationActuelle->poste->nom,
                'entite' => $affectationActuelle->entite->nom,
                'date_debut' => $affectationActuelle->date_debut,
            ] : null,
            'historique_affectations' => $user->affectations()
                ->where('statut', false)
                ->with(['poste', 'entite'])
                ->orderBy('date_fin', 'desc')
                ->get()
                ->map(function ($affectation) {
                    return [
                        'poste' => $affectation->poste->nom,
                        'entite' => $affectation->entite->nom,
                        'date_debut' => $affectation->date_debut,
                        'date_fin' => $affectation->date_fin,
                    ];
                }),
            'entites_dirigees' => $user->entitesDigees()
                ->whereNull('date_fin')
                ->with('entite')
                ->get()
                ->map(function ($direction) {
                    return [
                        'entite_id' => $direction->entite->id,
                        'entite_nom' => $direction->entite->nom,
                        'date_debut' => $direction->date_debut,
                    ];
                }),
            'statistiques' => [
                'total_affectations' => $user->affectations->count(),
                'entites_dirigees_actuellement' => $user->entitesDigees()->whereNull('date_fin')->count(),
                'roles_actifs' => $user->roles->count(),
            ],
            'date_creation' => $user->date_creation,
            'date_modification' => $user->date_modification,
            'creer_par' => $user->creer_par,
            'modifier_par' => $user->modifier_par,
        ];

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Utilisateur récupéré avec succès'
        ]);
    }
