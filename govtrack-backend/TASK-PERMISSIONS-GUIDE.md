# Guide des Permissions des Tâches

## Vue d'ensemble

Ce document décrit le système de permissions pour les tâches et leurs onglets dans GovTrack. Le système est basé sur des permissions granulaires qui permettent un contrôle précis de l'accès aux fonctionnalités des tâches.

## Permissions Principales

### Permissions de Base pour les Tâches

| Permission | Description | Rôles |
|------------|-------------|-------|
| `view_tasks_list` | Voir la liste des tâches | Admin, Directeur, Employé |
| `create_task` | Créer une tâche | Admin, Directeur |
| `edit_task` | Modifier une tâche | Admin, Directeur |
| `delete_task` | Supprimer une tâche | Admin |
| `view_task_details` | Voir les détails d'une tâche | Admin, Directeur, Employé |
| `change_task_status` | Changer le statut d'une tâche | Admin, Directeur |
| `view_task_history` | Voir l'historique d'une tâche | Admin, Directeur, Employé |

### Permissions pour les Pièces Jointes des Tâches

| Permission | Description | Rôles |
|------------|-------------|-------|
| `add_task_attachment` | Ajouter une pièce jointe à une tâche | Admin, Directeur |
| `view_task_attachments` | Voir les pièces jointes d'une tâche | Admin, Directeur, Employé |
| `download_task_attachment` | Télécharger une pièce jointe d'une tâche | Admin, Directeur, Employé |
| `delete_task_attachment` | Supprimer une pièce jointe d'une tâche | Admin, Directeur |

### Permissions pour les Commentaires des Tâches

| Permission | Description | Rôles |
|------------|-------------|-------|
| `add_task_comment` | Ajouter un commentaire à une tâche | Admin, Directeur |
| `view_task_comments` | Voir les commentaires d'une tâche | Admin, Directeur, Employé |
| `edit_task_comment` | Modifier un commentaire d'une tâche | Admin, Directeur |
| `delete_task_comment` | Supprimer un commentaire d'une tâche | Admin, Directeur |
| `view_task_comment_stats` | Voir les statistiques des commentaires d'une tâche | Admin, Directeur, Employé |

## Niveaux d'Accès par Rôle

### Administrateur
- **Accès complet** : Toutes les permissions
- **Pouvoirs** : Peut gérer toutes les tâches, créer, modifier, supprimer
- **Onglets accessibles** : Tous les onglets

### Directeur
- **Accès étendu** : Toutes les permissions sauf suppression
- **Pouvoirs** : Peut gérer les tâches de son entité, créer, modifier
- **Onglets accessibles** : Tous les onglets
- **Restrictions** : Ne peut pas supprimer des tâches

### Employé
- **Accès limité** : Permissions de lecture uniquement
- **Pouvoirs** : Peut consulter les tâches, voir les détails
- **Onglets accessibles** : Vue d'ensemble, pièces jointes (lecture), historique, commentaires (lecture)
- **Restrictions** : Ne peut pas créer, modifier ou supprimer

## Utilisation dans le Code

### Backend (Laravel)

```php
// Vérifier une permission
if ($user->hasPermission('view_tasks_list')) {
    // Accès autorisé
}

// Middleware dans les routes
Route::get('taches', [TacheController::class, 'index'])
    ->middleware('permission:view_tasks_list');
```

### Frontend (React/TypeScript)

```typescript
// Hook pour les permissions
import { useTachePermissions } from '@/hooks/useTachePermissions';

const permissions = useTachePermissions();

// Vérifier les permissions
if (permissions.canViewList) {
    // Afficher la liste des tâches
}

if (permissions.canCreate) {
    // Afficher le bouton de création
}
```

## Routes API Sécurisées

### Tâches
- `GET /api/v1/taches` → `view_tasks_list`
- `GET /api/v1/taches/mes-taches` → `view_tasks_list`
- `GET /api/v1/taches/{id}` → `view_task_details`
- `POST /api/v1/taches` → `create_task`
- `PUT /api/v1/taches/{id}` → `edit_task`
- `DELETE /api/v1/taches/{id}` → `delete_task`
- `POST /api/v1/taches/{id}/changer-statut` → `change_task_status`
- `GET /api/v1/taches/{id}/historique-statuts` → `view_task_history`

### Pièces Jointes des Tâches
- `GET /api/v1/taches/{tacheId}/pieces-jointes` → `view_task_attachments`
- `POST /api/v1/taches/{tacheId}/pieces-jointes` → `add_task_attachment`
- `GET /api/v1/taches/{tacheId}/pieces-jointes/{id}/download` → `download_task_attachment`
- `PUT /api/v1/taches/{tacheId}/pieces-jointes/{id}` → `view_task_attachments`
- `DELETE /api/v1/taches/{tacheId}/pieces-jointes/{id}` → `delete_task_attachment`

### Commentaires des Tâches
- `GET /api/v1/taches/{tacheId}/discussions` → `view_task_comments`
- `POST /api/v1/taches/{tacheId}/discussions` → `add_task_comment`
- `GET /api/v1/taches/{tacheId}/discussions/statistiques` → `view_task_comment_stats`
- `PUT /api/v1/taches/{tacheId}/discussions/{id}` → `edit_task_comment`
- `DELETE /api/v1/taches/{tacheId}/discussions/{id}` → `delete_task_comment`

## Gestion des Erreurs

### Backend
```php
// Retourner une erreur 403 avec les permissions requises
return response()->json([
    'success' => false,
    'message' => 'Vous n\'avez pas les permissions nécessaires',
    'permissions_required' => [
        'view_tasks_list' => 'Voir la liste des tâches',
        'create_task' => 'Créer une tâche'
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
                Vous n'avez pas les permissions nécessaires pour consulter les tâches.
            </p>
        </div>
    );
}
```

## Composants Frontend

### TaskTabs
Le composant `TaskTabs` gère l'affichage conditionnel des onglets selon les permissions :

```typescript
import TaskTabs from '@/components/TaskTabs';

<TaskTabs taskId="123" task={taskData} />
```

### Onglets Disponibles
1. **Vue d'ensemble** : Informations de base de la tâche
2. **Pièces jointes** : Gestion des fichiers attachés
3. **Commentaires** : Système de commentaires
4. **Historique** : Chronologie des modifications

## Bonnes Pratiques

1. **Vérification côté serveur** : Toujours vérifier les permissions côté backend
2. **Interface adaptative** : Masquer les éléments non autorisés côté frontend
3. **Messages d'erreur clairs** : Expliquer pourquoi l'accès est refusé
4. **Permissions granulaires** : Utiliser des permissions spécifiques plutôt que génériques
5. **Documentation** : Maintenir à jour la documentation des permissions

## Ajout de Nouvelles Permissions

1. Ajouter la permission dans le seeder `TachePermissionsSeeder`
2. Mettre à jour les routes API avec le middleware approprié
3. Ajouter la permission dans le hook `useTachePermissions`
4. Mettre à jour la documentation
5. Tester avec différents rôles utilisateur

## Différences avec les Permissions des Projets

Les permissions des tâches sont distinctes des permissions des projets pour permettre un contrôle plus granulaire :

- **Projets** : Gestion au niveau projet (création, modification, suppression de projets)
- **Tâches** : Gestion au niveau tâche (création, modification, suppression de tâches individuelles)

Un utilisateur peut avoir des permissions différentes sur les projets et les tâches selon son rôle et ses responsabilités. 
