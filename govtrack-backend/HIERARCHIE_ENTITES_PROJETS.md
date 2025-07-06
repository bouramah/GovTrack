# Hiérarchie des Entités et Permissions de Projets

## Modifications Apportées

### Problème Identifié
La permission `view_my_entity_projects` ne permettait de voir que les projets de l'entité directe de l'utilisateur, sans inclure les projets des entités enfants.

### Solution Implémentée
Modification du `ProjetController` pour inclure récursivement tous les projets des entités enfants dans la permission `view_my_entity_projects`.

## Détails Techniques

### 1. Nouvelles Méthodes Helper

#### `getEntitesEnfantsRecursives(int $entiteId): array`
- Récupère récursivement toutes les entités enfants d'une entité donnée
- Retourne un tableau d'IDs d'entités incluant l'entité parent et tous ses descendants

#### `getEnfantsRecursifs(Entite $entite, array &$entitesIds): void`
- Méthode récursive pour parcourir l'arbre des entités enfants
- Modifie le tableau `$entitesIds` en référence pour ajouter tous les IDs

### 2. Modifications des Méthodes Principales

#### `index()` - Liste des Projets
```php
// AVANT
$utilisateursEntite = \App\Models\UtilisateurEntiteHistory::where('service_id', $entiteId)
    ->distinct()
    ->pluck('user_id');

// APRÈS
$entitesIds = $this->getEntitesEnfantsRecursives($entiteId);
$utilisateursEntite = \App\Models\UtilisateurEntiteHistory::whereIn('service_id', $entitesIds)
    ->distinct()
    ->pluck('user_id');
```

#### `tableauBord()` - Tableau de Bord
- Même modification que pour `index()`
- Les statistiques incluent maintenant les projets des entités enfants

#### `getUsersForFilter()` - Filtres Utilisateurs
- Les listes de porteurs et donneurs d'ordre incluent maintenant les utilisateurs des entités enfants

### 3. Mise à Jour des Descriptions

Les descriptions des permissions ont été mises à jour pour refléter le nouveau comportement :
- `'Projets de votre entité'` → `'Projets de votre entité et entités enfants'`
- `'Tableau de bord de votre entité'` → `'Tableau de bord de votre entité et entités enfants'`

## Exemple de Hiérarchie

```
Direction Générale (ID: 1)
├── Service Informatique (ID: 2)
│   ├── Division Développement (ID: 3)
│   └── Division Support (ID: 4)
└── Service RH (ID: 5)
    └── Division Recrutement (ID: 6)
```

### Comportement des Permissions

#### Utilisateur dans "Direction Générale"
- **Avant** : Voir seulement les projets de la Direction Générale
- **Après** : Voir les projets de la Direction + Service Informatique + Division Développement + Division Support + Service RH + Division Recrutement

#### Utilisateur dans "Service Informatique"
- **Avant** : Voir seulement les projets du Service Informatique
- **Après** : Voir les projets du Service Informatique + Division Développement + Division Support

#### Utilisateur dans "Division Développement"
- **Avant** : Voir seulement les projets de la Division Développement
- **Après** : Voir seulement les projets de la Division Développement (pas d'enfants)

## Impact sur les Filtres

### Filtres de Porteurs/Donneurs d'Ordre
Les utilisateurs avec `view_my_entity_projects` peuvent maintenant filtrer par :
- Tous les porteurs de leur entité ET entités enfants
- Tous les donneurs d'ordre de leur entité ET entités enfants

### Filtres d'Entités
- Reste inchangé : seulement pour `view_all_projects`

## Tests

Un script de test a été créé : `test_entity_hierarchy_permissions.php`

Pour l'exécuter :
```bash
cd govtrack-backend
php test_entity_hierarchy_permissions.php
```

Ce script :
1. Crée une structure hiérarchique d'entités
2. Crée des utilisateurs dans chaque niveau
3. Attribue la permission `view_my_entity_projects`
4. Crée des projets pour chaque utilisateur
5. Teste que chaque utilisateur voit les bons projets selon la hiérarchie

## Compatibilité

### Rétrocompatibilité
- ✅ Les utilisateurs existants continuent de fonctionner
- ✅ Aucune modification de base de données requise
- ✅ Les permissions existantes restent valides

### Performance
- ⚠️ Impact mineur sur les performances due aux requêtes récursives
- ✅ Optimisé avec `whereIn()` au lieu de multiples requêtes
- ✅ Utilisation de `distinct()` pour éviter les doublons

## Sécurité

### Contrôle d'Accès
- ✅ Respect de la hiérarchie organisationnelle
- ✅ Pas d'accès aux projets d'entités non liées
- ✅ Maintien des permissions existantes

### Audit
- ✅ Toutes les actions restent tracées
- ✅ Pas de modification des logs d'audit existants

## Migration

### Automatique
- ✅ Aucune action requise
- ✅ Les modifications sont actives immédiatement

### Vérification
Pour vérifier que les modifications fonctionnent :

1. Connectez-vous avec un utilisateur ayant `view_my_entity_projects`
2. Allez dans la liste des projets
3. Vérifiez que vous voyez les projets des entités enfants
4. Testez les filtres de porteurs/donneurs d'ordre

## Support

En cas de problème :
1. Vérifiez que les entités ont bien une hiérarchie définie (`parent_id`)
2. Vérifiez que les utilisateurs sont bien affectés aux entités
3. Vérifiez que la permission `view_my_entity_projects` est attribuée
4. Consultez les logs Laravel pour les erreurs éventuelles 
