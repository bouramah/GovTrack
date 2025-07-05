# Guide des Permissions des Projets

## Vue d'ensemble

Ce document décrit le système de permissions pour les projets et leurs onglets dans GovTrack. Le système est basé sur des permissions granulaires qui permettent un contrôle précis de l'accès aux fonctionnalités.

## Permissions Principales

### Permissions de Base pour les Projets

| Permission | Description | Rôles |
|------------|-------------|-------|
| `view_projects_list` | Voir la liste des projets | Admin, Directeur, Employé |
| `create_project` | Créer un projet | Admin, Directeur |
| `edit_project` | Modifier un projet | Admin, Directeur |
| `delete_project` | Supprimer un projet | Admin |
| `view_project_details` | Voir les détails d'un projet | Admin, Directeur, Employé |
| `update_project_execution_level` | Mettre à jour le niveau d'exécution | Admin, Directeur |
| `change_project_status` | Changer le statut d'un projet | Admin, Directeur |

### Permissions pour les Tâches

| Permission | Description | Rôles |
|------------|-------------|-------|
| `view_project_tasks` | Voir les tâches d'un projet | Admin, Directeur, Employé |
| `create_project_task` | Créer une tâche d'un projet | Admin, Directeur |
| `edit_project_task` | Modifier une tâche d'un projet | Admin, Directeur |
| `delete_project_task` | Supprimer une tâche d'un projet | Admin, Directeur |
| `view_project_task_details` | Voir les détails d'une tâche | Admin, Directeur, Employé |

### Permissions pour les Pièces Jointes

| Permission | Description | Rôles |
|------------|-------------|-------|
| `add_project_attachment` | Ajouter une pièce jointe | Admin, Directeur |
| `view_project_attachments` | Voir les pièces jointes | Admin, Directeur, Employé |
| `download_project_attachment` | Télécharger une pièce jointe | Admin, Directeur, Employé |
| `edit_project_attachment` | Modifier une pièce jointe | Admin, Directeur |
| `delete_project_attachment` | Supprimer une pièce jointe | Admin, Directeur |

### Permissions pour l'Historique

| Permission | Description | Rôles |
|------------|-------------|-------|
| `view_project_history` | Voir l'historique d'un projet | Admin, Directeur, Employé |

### Permissions pour les Commentaires

| Permission | Description | Rôles |
|------------|-------------|-------|
| `add_project_comment` | Ajouter un commentaire | Admin, Directeur |
| `view_project_comments` | Voir les commentaires | Admin, Directeur, Employé |
| `edit_project_comment` | Modifier un commentaire | Admin, Directeur |
| `delete_project_comment` | Supprimer un commentaire | Admin, Directeur |
| `view_project_comment_stats` | Voir les statistiques des commentaires | Admin, Directeur, Employé |

## Niveaux d'Accès par Rôle

### Administrateur
- **Accès complet** : Toutes les permissions
- **Pouvoirs** : Peut gérer tous les projets, créer, modifier, supprimer
- **Onglets accessibles** : Tous les onglets

### Directeur
- **Accès étendu** : Toutes les permissions sauf suppression
- **Pouvoirs** : Peut gérer les projets de son entité, créer, modifier
- **Onglets accessibles** : Tous les onglets
- **Restrictions** : Ne peut pas supprimer des projets

### Employé
- **Accès limité** : Permissions de lecture uniquement
- **Pouvoirs** : Peut consulter les projets, voir les détails
- **Onglets accessibles** : Vue d'ensemble, tâches (lecture), pièces jointes (lecture), historique, commentaires (lecture)
- **Restrictions** : Ne peut pas créer, modifier ou supprimer

## Utilisation dans le Code

### Backend (Laravel)

```php
// Vérifier une permission
if ($user->hasPermission('view_projects_list')) {
    // Accès autorisé
}

// Middleware dans les routes
Route::get('projets', [ProjetController::class, 'index'])
    ->middleware('permission:view_projects_list');
```

### Frontend (React/TypeScript)

```typescript
// Hook pour les permissions
import { useProjetPermissions } from '@/hooks/useProjetPermissions';

const permissions = useProjetPermissions();

// Vérifier les permissions
if (permissions.canViewList) {
    // Afficher la liste des projets
}

if (permissions.canCreate) {
    // Afficher le bouton de création
}
```

## Routes API Sécurisées

### Projets
- `GET /api/v1/projets` → `view_projects_list`
- `GET /api/v1/projets/{id}` → `view_project_details`
- `POST /api/v1/projets` → `create_project`
- `PUT /api/v1/projets/{id}` → `edit_project`
- `DELETE /api/v1/projets/{id}` → `delete_project`
- `POST /api/v1/projets/{id}/changer-statut` → `change_project_status`
- `POST /api/v1/projets/{id}/niveau-execution` → `update_project_execution_level`
- `GET /api/v1/projets/{id}/historique` → `view_project_history`

### Tâches
- `GET /api/v1/taches` → `view_project_tasks`
- `GET /api/v1/taches/{id}` → `view_project_task_details`
- `POST /api/v1/taches` → `create_project_task`
- `PUT /api/v1/taches/{id}` → `edit_project_task`
- `DELETE /api/v1/taches/{id}` → `delete_project_task`

### Pièces Jointes
- `GET /api/v1/projets/{projetId}/pieces-jointes` → `view_project_attachments`
- `POST /api/v1/projets/{projetId}/pieces-jointes` → `add_project_attachment`
- `GET /api/v1/projets/{projetId}/pieces-jointes/{id}/download` → `download_project_attachment`
- `PUT /api/v1/projets/{projetId}/pieces-jointes/{id}` → `edit_project_attachment`
- `DELETE /api/v1/projets/{projetId}/pieces-jointes/{id}` → `delete_project_attachment`

### Commentaires
- `GET /api/v1/projets/{projetId}/discussions` → `view_project_comments`
- `POST /api/v1/projets/{projetId}/discussions` → `add_project_comment`
- `GET /api/v1/projets/{projetId}/discussions/statistiques` → `view_project_comment_stats`
- `PUT /api/v1/projets/{projetId}/discussions/{id}` → `edit_project_comment`
- `DELETE /api/v1/projets/{projetId}/discussions/{id}` → `delete_project_comment`

## Gestion des Erreurs

### Backend
```php
// Retourner une erreur 403 avec les permissions requises
return response()->json([
    'success' => false,
    'message' => 'Vous n\'avez pas les permissions nécessaires',
    'permissions_required' => [
        'view_projects_list' => 'Voir la liste des projets',
        'create_project' => 'Créer un projet'
    ]
], 403);
```

### Frontend
```typescript
// Afficher un message d'erreur
if (!permissions.canViewList) {
    return (
        <div className="text-center py-8">
            <p className="text-gray-500">
                Vous n'avez pas les permissions nécessaires pour consulter les projets.
            </p>
        </div>
    );
}
```

## Bonnes Pratiques

1. **Vérification côté serveur** : Toujours vérifier les permissions côté backend
2. **Interface adaptative** : Masquer les éléments non autorisés côté frontend
3. **Messages d'erreur clairs** : Expliquer pourquoi l'accès est refusé
4. **Permissions granulaires** : Utiliser des permissions spécifiques plutôt que génériques
5. **Documentation** : Maintenir à jour la documentation des permissions

## Ajout de Nouvelles Permissions

1. Ajouter la permission dans le seeder `ProjetPermissionsSeeder`
2. Mettre à jour les routes API avec le middleware approprié
3. Ajouter la permission dans le hook `useProjetPermissions`
4. Mettre à jour la documentation
5. Tester avec différents rôles utilisateur 
