# Guide de Test Complet - TypeReunionMembrePermanentService

## 📋 Vue d'ensemble

Ce guide couvre les tests complets pour le service `TypeReunionMembrePermanentService` qui gère les membres permanents des types de réunion.

## 🔧 Configuration de l'environnement

### Variables d'environnement Postman

```json
{
  "base_url": "http://localhost:8000",
  "type_reunion_id": "3",
  "user_id": "2",
  "membre_id": "2"
}
```

### Prérequis

1. **Base de données** : Assurez-vous que les tables suivantes existent :
   - `type_reunions`
   - `type_reunion_membres_permanents`
   - `users`

2. **Données de test** :
   - Au moins un type de réunion avec l'ID spécifié
   - Au moins un utilisateur avec l'ID spécifié

## 🧪 Tests détaillés

### 1. Récupérer les membres permanents d'un type de réunion
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres`
- **Méthode** : `GET`
- **Résultat attendu** : Liste des membres permanents avec leurs rôles et notifications

### 2. Ajouter un membre permanent
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres`
- **Méthode** : `POST`
- **Body** :
```json
{
  "membres": [
    {
      "user_id": 2,
      "role_defaut": "PARTICIPANT",
      "notifications_par_defaut": ["email", "sms"],
      "actif": true
    }
  ]
}
```

### 3. Vérifier si un utilisateur est membre permanent
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres/{{user_id}}/check`
- **Méthode** : `GET`
- **Résultat attendu** : Statut de membre permanent de l'utilisateur

### 4. Récupérer le rôle par défaut d'un membre
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres/{{user_id}}/role`
- **Méthode** : `GET`
- **Résultat attendu** : Rôle par défaut du membre

### 5. Récupérer les notifications par défaut d'un membre
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres/{{user_id}}/notifications`
- **Méthode** : `GET`
- **Résultat attendu** : Notifications par défaut du membre

### 6. Mettre à jour un membre permanent
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres/{{user_id}}`
- **Méthode** : `PUT`
- **Body** :
```json
{
  "role_defaut": "MODERATEUR",
  "notifications_par_defaut": ["email"],
  "actif": false
}
```

### 7. Supprimer un membre permanent
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres/{{user_id}}`
- **Méthode** : `DELETE`
- **Résultat attendu** : Confirmation de suppression

### 8. Ajouter plusieurs membres permanents
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres`
- **Méthode** : `POST`
- **Body** :
```json
{
  "membres": [
    {
      "user_id": 2,
      "role_defaut": "PARTICIPANT",
      "notifications_par_defaut": ["email"],
      "actif": true
    },
    {
      "user_id": 3,
      "role_defaut": "MODERATEUR",
      "notifications_par_defaut": ["email", "sms"],
      "actif": true
    }
  ]
}
```

### 9. Supprimer plusieurs membres permanents
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres`
- **Méthode** : `DELETE`
- **Body** :
```json
{
  "membres": [2, 3]
}
```

### 10. Récupérer les statistiques
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres/stats`
- **Méthode** : `GET`
- **Résultat attendu** : Statistiques des membres permanents

### 11. Copier les membres vers un autre type
- **URL** : `{{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres/copier`
- **Méthode** : `POST`
- **Body** :
```json
{
  "type_reunion_destination_id": 4
}
```

## 🔧 Corrections appliquées

### 1. Refactoring du contrôleur
- **Problème** : Le `TypeReunionController` manipulait directement les relations au lieu d'utiliser le service dédié
- **Solution** : Injection du `TypeReunionMembrePermanentService` et délégation de la logique

### 2. Amélioration de la validation
- **Problème** : Validation insuffisante pour les champs complexes
- **Solution** : Validation détaillée pour `membres.*.user_id`, `membres.*.role_defaut`, `membres.*.notifications_par_defaut`, `membres.*.actif`

### 3. Correction du casting JSON avec modèles pivot personnalisés
- **Problème** : Erreur "Instantiation of class Closure is not allowed" lors de l'utilisation de `->using(function ($pivot) {...})`
- **Solution** : Création de modèles pivot personnalisés :
  - **`TypeReunionGestionnaire`** : Modèle pivot pour les gestionnaires avec casting JSON
  - **`TypeReunionMembrePermanent`** : Modèle pivot pour les membres permanents avec casting JSON
  
  ```php
  // Dans TypeReunion.php
  public function membresPermanents(): BelongsToMany
  {
      return $this->belongsToMany(User::class, 'type_reunion_membres_permanents', 'type_reunion_id', 'user_id')
                  ->withPivot('role_defaut', 'actif', 'notifications_par_defaut', 'date_creation', 'date_modification')
                  ->withTimestamps('date_creation', 'date_modification')
                  ->using(TypeReunionMembrePermanent::class);
  }
  
  // Dans TypeReunionMembrePermanent.php
  protected $casts = [
      'role_defaut' => 'string',
      'actif' => 'boolean',
      'notifications_par_defaut' => 'array',
      'date_creation' => 'datetime',
      'date_modification' => 'datetime',
  ];
  ```

### 4. Correction des champs audit manquants
- **Problème** : Erreur "Field 'creer_par' doesn't have a default value" lors de l'insertion
- **Solution** : Ajout des champs `creer_par` et `modifier_par` dans les opérations d'attachement :
  ```php
  // Dans addMembrePermanent()
  $typeReunion->membresPermanents()->attach($data['user_id'], [
      'role_defaut' => $data['role_defaut'] ?? 'PARTICIPANT',
      'actif' => $data['actif'] ?? true,
      'notifications_par_defaut' => $data['notifications_par_defaut'] ?? [],
      'creer_par' => $userId,
      'modifier_par' => $userId,
  ]);
  
  // Dans updateMembrePermanent()
  $typeReunion->membresPermanents()->updateExistingPivot($membreId, [
      'role_defaut' => $data['role_defaut'] ?? $existingMembre->pivot->role_defaut,
      'actif' => $data['actif'] ?? $existingMembre->pivot->actif,
      'notifications_par_defaut' => $data['notifications_par_defaut'] ?? $existingMembre->pivot->notifications_par_defaut,
      'modifier_par' => $userId,
  ]);
  ```

### 5. Ajout des routes manquantes
- **Problème** : Routes "Not Found" pour les opérations individuelles sur les membres permanents
- **Solution** : Ajout de toutes les routes manquantes dans `routes/api.php` :
  ```php
  // Routes pour les opérations individuelles sur les membres
  Route::get('{id}/membres/{userId}/check', [TypeReunionController::class, 'checkMembre']);
  Route::get('{id}/membres/{userId}/role', [TypeReunionController::class, 'getMembreRole']);
  Route::get('{id}/membres/{userId}/notifications', [TypeReunionController::class, 'getMembreNotifications']);
  Route::put('{id}/membres/{userId}', [TypeReunionController::class, 'updateMembre']);
  Route::delete('{id}/membres/{userId}', [TypeReunionController::class, 'removeMembre']);
  Route::get('{id}/membres/stats', [TypeReunionController::class, 'getMembresStats']);
  Route::post('{id}/membres/copier', [TypeReunionController::class, 'copyMembres']);
  ```

### 6. Gestion des réponses multiples
- **Problème** : Pas de gestion des opérations multiples avec statuts individuels
- **Solution** : Implémentation de réponses multi-statuts pour les opérations en lot

## 📊 Validation des résultats

### Réponses attendues

1. **Succès** : `{"success": true, "data": {...}}`
2. **Erreur** : `{"success": false, "message": "...", "error": "..."}`
3. **Multi-statut** : `{"success": true, "message": "Ajout terminé : X succès, Y erreurs", "results": [...]}`

### Points de vérification

- ✅ Les notifications par défaut sont correctement stockées en JSON
- ✅ Les rôles par défaut respectent les ENUMs définis
- ✅ Les opérations multiples retournent des statuts individuels
- ✅ La validation empêche les données invalides
- ✅ Les relations utilisent des modèles pivot personnalisés avec casting JSON
- ✅ Plus d'erreur "Instantiation of class Closure is not allowed"
- ✅ Plus d'erreur "Field 'creer_par' doesn't have a default value"
- ✅ Plus d'erreur "Not Found" pour les routes individuelles
- ✅ Les champs d'audit (`creer_par`, `modifier_par`) sont correctement assignés
- ✅ Toutes les routes sont correctement définies et accessibles

## 🚀 Exécution des tests

1. Importez la collection Postman
2. Configurez les variables d'environnement
3. Exécutez les tests dans l'ordre
4. Vérifiez les réponses et les données en base

## 📝 Notes importantes

- Les notifications par défaut sont stockées comme un tableau JSON dans la base de données
- Les rôles par défaut doivent respecter les ENUMs : `PARTICIPANT`, `MODERATEUR`, `PRESIDENT`, `SECRETAIRE`
- Les opérations multiples permettent d'ajouter/supprimer plusieurs membres en une seule requête
- Le service gère automatiquement les timestamps personnalisés (`date_creation`, `date_modification`)
- Les contraintes d'unicité empêchent les doublons sur `type_reunion_id` et `user_id`
- **IMPORTANT** : L'utilisation de modèles pivot personnalisés résout le problème de casting JSON de manière propre et maintenable
- **IMPORTANT** : Les champs d'audit (`creer_par`, `modifier_par`) sont maintenant correctement assignés lors des opérations CRUD
- **IMPORTANT** : Toutes les routes individuelles sont maintenant disponibles et fonctionnelles 
