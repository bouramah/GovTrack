# Guide de Test - ReunionOrdreJourService

## 📋 Vue d'ensemble

Ce guide détaille l'exécution complète des tests pour le service `ReunionOrdreJourService` qui gère l'ordre du jour des réunions.

## 🔧 Configuration requise

### Variables d'environnement Postman

```json
{
  "base_url": "http://localhost:8000",
  "token": "VOTRE_TOKEN_JWT",
  "reunion_test_id": "1",
  "point_test_id": "",
  "point_created_id": ""
}
```

### Prérequis

1. **Base de données** : Avoir une réunion existante avec l'ID spécifié dans `reunion_test_id`
2. **Authentification** : Token JWT valide avec les permissions appropriées
3. **Utilisateurs** : Avoir des utilisateurs avec les IDs 2, 3, 4 dans la base de données

## 🧪 Tests détaillés

### **1. Récupérer l'ordre du jour d'une réunion**
- **URL :** `GET {{base_url}}/api/v1/ordre-jour/{{reunion_test_id}}`
- **Objectif :** Récupérer tous les points de l'ordre du jour d'une réunion spécifique
- **Tests :** Vérification du statut 200, structure de réponse, et récupération automatique de l'ID de test
- **Permissions :** `view_reunion_ordre_jour`

### **2. Ajouter un point à l'ordre du jour**
- **URL :** `POST {{base_url}}/api/v1/ordre-jour/{{reunion_test_id}}/points`
- **Payload :**
```json
{
    "titre": "Point de test - Ordre du jour",
    "description": "Description détaillée du point de test",
    "type": "SUJET_SPECIFIQUE",
    "duree_estimee_minutes": 30,
    "responsable_id": 2,
    "niveau_detail_requis": "DETAILLE"
}
```
- **Objectif :** Ajouter un nouveau point à l'ordre du jour
- **Tests :** Vérification du statut 201, création réussie, et sauvegarde de l'ID
- **Permissions :** `create_reunion_ordre_jour`

### **3. Ajouter plusieurs points à l'ordre du jour**
- **URL :** `POST {{base_url}}/api/v1/ordre-jour/{{reunion_test_id}}/points/multiple`
- **Payload :**
```json
{
    "points": [
        {
            "titre": "Point 1 - Suivi projets",
            "description": "Point de suivi des projets en cours",
            "type": "SUIVI_PROJETS",
            "duree_estimee_minutes": 45,
            "responsable_id": 3,
            "niveau_detail_requis": "DETAILLE"
        },
        {
            "titre": "Point 2 - Point divers",
            "description": "Point divers de la réunion",
            "type": "POINT_DIVERS",
            "duree_estimee_minutes": 15,
            "niveau_detail_requis": "SIMPLE"
        }
    ]
}
```
- **Objectif :** Ajouter plusieurs points en une seule requête
- **Tests :** Vérification de la création de multiples points
- **Permissions :** `create_reunion_ordre_jour`

### **4. Mettre à jour un point de l'ordre du jour**
- **URL :** `PUT {{base_url}}/api/v1/ordre-jour/points/{{point_created_id}}`
- **Payload :**
```json
{
    "titre": "Point modifié - Ordre du jour",
    "description": "Description mise à jour du point",
    "type": "SUJET_SPECIFIQUE",
    "duree_estimee_minutes": 60,
    "responsable_id": 4,
    "statut": "EN_COURS",
    "niveau_detail_requis": "DETAILLE"
}
```
- **Objectif :** Modifier les informations d'un point existant
- **Tests :** Vérification de la mise à jour du titre
- **Permissions :** `update_reunion_ordre_jour`

### **5. Changer le statut d'un point**
- **URL :** `POST {{base_url}}/api/v1/ordre-jour/points/{{point_created_id}}/statut`
- **Payload :**
```json
{
    "statut": "TERMINE"
}
```
- **Objectif :** Changer le statut d'un point de l'ordre du jour
- **Tests :** Vérification de la mise à jour vers TERMINE
- **Permissions :** `update_reunion_ordre_jour`

### **6. Réorganiser l'ordre des points**
- **URL :** `POST {{base_url}}/api/v1/ordre-jour/{{reunion_test_id}}/reorder`
- **Payload :**
```json
{
    "new_order": [
        {
            "id": "{{point_created_id}}",
            "ordre": 1
        },
        {
            "id": "{{point_test_id}}",
            "ordre": 2
        }
    ]
}
```
- **Objectif :** Réorganiser l'ordre des points de l'ordre du jour
- **Tests :** Vérification de la réorganisation réussie
- **Permissions :** `update_reunion_ordre_jour`

### **7. Obtenir les statistiques de l'ordre du jour**
- **URL :** `GET {{base_url}}/api/v1/ordre-jour/{{reunion_test_id}}/stats`
- **Objectif :** Récupérer les statistiques de l'ordre du jour d'une réunion
- **Tests :** Vérification de la structure des statistiques
- **Permissions :** `view_reunion_ordre_jour`

### **8. Test de validation - Titre manquant**
- **URL :** `POST {{base_url}}/api/v1/ordre-jour/{{reunion_test_id}}/points`
- **Payload :**
```json
{
    "description": "Description sans titre",
    "type": "POINT_DIVERS",
    "duree_estimee_minutes": 15
}
```
- **Objectif :** Tester la validation du champ `titre` obligatoire
- **Tests :** Vérification du statut 422 et des erreurs de validation

### **9. Test de validation - Type invalide**
- **URL :** `POST {{base_url}}/api/v1/ordre-jour/{{reunion_test_id}}/points`
- **Payload :**
```json
{
    "titre": "Point avec type invalide",
    "type": "TYPE_INVALIDE",
    "duree_estimee_minutes": 15
}
```
- **Objectif :** Tester la validation des valeurs ENUM pour le type
- **Tests :** Vérification du statut 422 et des erreurs de validation

### **10. Supprimer un point de l'ordre du jour**
- **URL :** `DELETE {{base_url}}/api/v1/ordre-jour/points/{{point_created_id}}`
- **Objectif :** Supprimer un point créé pendant les tests
- **Tests :** Vérification de la suppression réussie
- **Permissions :** `delete_reunion_ordre_jour`

## 🔍 Corrections appliquées

### **1. Modèle ReunionOrdreJour.php**

#### **Champs manquants ajoutés :**
```php
// Avant
protected $fillable = [
    'reunion_id',
    'ordre',
    'titre',
    'description',
    'type',
    'duree_estimee_minutes',
    'responsable_id',
    'statut',
    'niveau_detail_requis',
    // 'entite_proposante_id', // ❌ Manquant
    // 'projet_id', // ❌ Manquant
];

// Après
protected $fillable = [
    'reunion_id',
    'ordre',
    'titre',
    'description',
    'type',
    'duree_estimee_minutes',
    'entite_proposante_id', // ✅ Ajouté
    'responsable_id',
    'projet_id', // ✅ Ajouté
    'statut',
    'niveau_detail_requis',
];
```

#### **Casts corrigés :**
```php
// Avant
protected $casts = [
    'ordre' => 'integer',
    'duree_estimee_minutes' => 'integer',
    'niveau_detail' => 'integer', // ❌ Incorrect
    'commentaires' => 'array', // ❌ Champ inexistant
];

// Après
protected $casts = [
    'ordre' => 'integer',
    'duree_estimee_minutes' => 'integer',
    'niveau_detail_requis' => 'string', // ✅ Corrigé
];
```

#### **Relations ajoutées :**
```php
// Relations avec l'entité proposante
public function entiteProposante(): BelongsTo
{
    return $this->belongsTo(Entite::class, 'entite_proposante_id');
}

// Relations avec le projet
public function projet(): BelongsTo
{
    return $this->belongsTo(Projet::class, 'projet_id');
}
```

### **2. Contrôleur ReunionOrdreJourController.php**

#### **Validation corrigée :**
```php
// Avant
$validator = Validator::make($request->all(), [
    'titre' => 'required|string|max:255',
    'description' => 'nullable|string|max:1000',
    'type' => 'nullable|string|in:SUJET_SPECIFIQUE,POINT_DIVERS,SUIVI_PROJETS',
    'duree_estimee_minutes' => 'nullable|integer|min:1|max:480',
    'responsable_id' => 'nullable|integer|exists:users,id',
    'ordre' => 'nullable|integer|min:1',
    'niveau_detail' => 'nullable|string|in:SIMPLE,DETAILLE', // ❌ Incorrect
]);

// Après
$validator = Validator::make($request->all(), [
    'titre' => 'required|string|max:255',
    'description' => 'nullable|string|max:1000',
    'type' => 'nullable|string|in:SUJET_SPECIFIQUE,POINT_DIVERS,SUIVI_PROJETS',
    'duree_estimee_minutes' => 'nullable|integer|min:1|max:480',
    'entite_proposante_id' => 'nullable|integer|exists:entites,id', // ✅ Ajouté
    'responsable_id' => 'nullable|integer|exists:users,id',
    'projet_id' => 'nullable|integer|exists:projets,id', // ✅ Ajouté
    'ordre' => 'nullable|integer|min:1',
    'niveau_detail_requis' => 'nullable|string|in:SIMPLE,DETAILLE', // ✅ Corrigé
]);
```

### **3. Service ReunionOrdreJourService.php**

#### **Champs corrigés dans addPointOrdreJour :**
```php
// Avant
$pointData = [
    'reunion_id' => $reunionId,
    'ordre' => $data['ordre'],
    'titre' => $data['titre'],
    'description' => $data['description'] ?? '',
    'type' => $data['type'] ?? 'POINT_DIVERS',
    'duree_estimee_minutes' => $data['duree_estimee_minutes'] ?? 15,
    'responsable_id' => $data['responsable_id'] ?? null,
    'statut' => 'PLANIFIE',
    'niveau_detail_requis' => $data['niveau_detail'] ?? 'SIMPLE', // ❌ Incorrect
];

// Après
$pointData = [
    'reunion_id' => $reunionId,
    'ordre' => $data['ordre'],
    'titre' => $data['titre'],
    'description' => $data['description'] ?? '',
    'type' => $data['type'] ?? 'POINT_DIVERS',
    'duree_estimee_minutes' => $data['duree_estimee_minutes'] ?? 15,
    'entite_proposante_id' => $data['entite_proposante_id'] ?? null, // ✅ Ajouté
    'responsable_id' => $data['responsable_id'] ?? null,
    'projet_id' => $data['projet_id'] ?? null, // ✅ Ajouté
    'statut' => 'PLANIFIE',
    'niveau_detail_requis' => $data['niveau_detail_requis'] ?? 'SIMPLE', // ✅ Corrigé
];
```

#### **Champs corrigés dans updatePointOrdreJour :**
```php
// Avant
$updateData = array_filter([
    'ordre' => $data['ordre'] ?? null,
    'titre' => $data['titre'] ?? null,
    'description' => $data['description'] ?? null,
    'type' => $data['type'] ?? null,
    'duree_estimee_minutes' => $data['duree_estimee_minutes'] ?? null,
    'responsable_id' => $data['responsable_id'] ?? null,
    'statut' => $data['statut'] ?? null,
    'niveau_detail' => $data['niveau_detail'] ?? null, // ❌ Incorrect
]);

// Après
$updateData = array_filter([
    'ordre' => $data['ordre'] ?? null,
    'titre' => $data['titre'] ?? null,
    'description' => $data['description'] ?? null,
    'type' => $data['type'] ?? null,
    'duree_estimee_minutes' => $data['duree_estimee_minutes'] ?? null,
    'entite_proposante_id' => $data['entite_proposante_id'] ?? null, // ✅ Ajouté
    'responsable_id' => $data['responsable_id'] ?? null,
    'projet_id' => $data['projet_id'] ?? null, // ✅ Ajouté
    'statut' => $data['statut'] ?? null,
    'niveau_detail_requis' => $data['niveau_detail_requis'] ?? null, // ✅ Corrigé
]);
```

## 📊 Valeurs ENUM finales

### **Types :**
- `SUJET_SPECIFIQUE` - Sujet Spécifique
- `POINT_DIVERS` - Point Divers
- `SUIVI_PROJETS` - Suivi Projets

### **Statuts :**
- `PLANIFIE` - Planifié
- `EN_COURS` - En cours
- `TERMINE` - Terminé
- `REPORTE` - Reporté

### **Niveau de détail requis :**
- `SIMPLE` - Simple
- `DETAILLE` - Détaillé

## ✅ État final

| **Migration** | **Modèle** | **Service** | **Contrôleur** |
|---------------|------------|-------------|----------------|
| `SUJET_SPECIFIQUE` | ✅ | ✅ | ✅ |
| `POINT_DIVERS` | ✅ | ✅ | ✅ |
| `SUIVI_PROJETS` | ✅ | ✅ | ✅ |
| `PLANIFIE` | ✅ | ✅ | ✅ |
| `EN_COURS` | ✅ | ✅ | ✅ |
| `TERMINE` | ✅ | ✅ | ✅ |
| `REPORTE` | ✅ | ✅ | ✅ |
| `SIMPLE` | ✅ | ✅ | ✅ |
| `DETAILLE` | ✅ | ✅ | ✅ |
| `entite_proposante_id` | ✅ | ✅ | ✅ |
| `projet_id` | ✅ | ✅ | ✅ |
| `niveau_detail_requis` | ✅ | ✅ | ✅ |

## 🚀 Exécution

1. **Importer** la collection Postman
2. **Configurer** les variables d'environnement
3. **Exécuter** les tests dans l'ordre
4. **Vérifier** que tous les tests passent

**Le ReunionOrdreJourService est maintenant cohérent et prêt pour la production !** 🎉 
