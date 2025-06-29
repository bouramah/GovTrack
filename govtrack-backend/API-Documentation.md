# 🚀 GovTrack API - Documentation Complète

## 📋 Vue d'ensemble

L'API GovTrack Partie 1 est une API REST complète pour la gestion des utilisateurs, entités organisationnelles, postes, rôles et permissions. Cette documentation présente tous les endpoints disponibles avec des exemples d'utilisation.

## 🔧 Configuration

### Prérequis
- Laravel 12 en cours d'exécution
- Base de données MySQL configurée
- Serveur web démarré : `php artisan serve`

### Base URL
```
http://localhost:8000/api/v1
```

### Headers requis
```
Accept: application/json
Content-Type: application/json (pour POST/PUT)
```

## 📦 Import de la Collection Postman

1. **Ouvrir Postman**
2. **Cliquer sur "Import"**
3. **Sélectionner le fichier :** `GovTrack-API-Collection.postman_collection.json`
4. **La collection sera automatiquement importée** avec tous les endpoints

## 👥 Comptes de test disponibles

| Email | Rôle | Mot de passe | Permissions |
|-------|------|--------------|-------------|
| `admin@govtrack.gov` | Administrateur | `password` | Toutes (6) |
| `amadou.diop@govtrack.gov` | Directeur | `password` | 4 permissions |
| `fatou.fall@govtrack.gov` | Développeur | `password` | 2 permissions |

## 📚 Endpoints par Catégorie

### 🏢 Type Entités
Gestion des types d'entités organisationnelles (Direction, Service, Division)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/type-entites` | Lister tous les types |
| `POST` | `/type-entites` | Créer un nouveau type |
| `GET` | `/type-entites/{id}` | Voir un type spécifique |
| `PUT` | `/type-entites/{id}` | Modifier un type |
| `DELETE` | `/type-entites/{id}` | Supprimer un type |

**Exemple création :**
```json
{
    "nom": "Bureau",
    "description": "Bureau départemental"
}
```

### 🏛️ Entités
Gestion des entités avec hiérarchie parent/enfant

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/entites` | Lister toutes les entités |
| `POST` | `/entites` | Créer une nouvelle entité |
| `GET` | `/entites/{id}` | Voir une entité détaillée |
| `PUT` | `/entites/{id}` | Modifier une entité |
| `DELETE` | `/entites/{id}` | Supprimer une entité |
| `GET` | `/entites/{id}/enfants` | 🌳 Obtenir entités enfants |
| `GET` | `/entites/{id}/hierarchy` | 🌳 Hiérarchie complète |
| `GET` | `/entites/organigramme` | 📊 Organigramme complet organisation |
| `GET` | `/entites/chefs-actuels` | 👑 Liste tous les chefs actuels |
| `POST` | `/entites/{id}/affecter-chef` | 👑 Affecter chef à entité |
| `POST` | `/entites/{id}/terminer-mandat-chef` | 👑 Terminer mandat chef |
| `GET` | `/entites/{id}/historique-chefs` | 👑 Historique des chefs |

**Exemple création avec parent :**
```json
{
    "nom": "Service Communication",
    "type_entite_id": 2,
    "parent_id": 1,
    "description": "Service de communication et relations publiques"
}
```

**Exemple affectation chef :**
```json
{
    "user_id": 3,
    "date_debut": "2025-01-01",
    "terminer_mandat_precedent": true
}
```

**Exemple terminer mandat chef :**
```json
{
    "date_fin": "2025-06-30",
    "raison": "Fin de mandat temporaire"
}
```

#### Endpoint Spécialisé - Organigramme

**`GET /entites/organigramme`** retourne la structure hiérarchique complète de l'organisation avec :
- 📊 **Structure récursive** : entités racines → enfants → descendants
- 👤 **Chef actuel** pour chaque entité (nom, contact, durée mandat)
- 👥 **Effectifs détaillés** (nombre + liste employés avec postes)
- 📈 **Statistiques** (niveau hiérarchique, descendants, présence chef)
- 🔢 **Métriques globales** (totaux, profondeur max)

**Parfait pour :**
- Interface graphique d'organigramme
- Tableaux de bord direction
- Visualisations hiérarchiques
- Rapports organisationnels

### 💼 Postes
Gestion des postes (sans lien direct aux entités)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/postes` | Lister tous les postes |
| `POST` | `/postes` | Créer un nouveau poste |
| `GET` | `/postes/{id}` | Voir un poste détaillé |
| `PUT` | `/postes/{id}` | Modifier un poste |
| `DELETE` | `/postes/{id}` | Supprimer un poste |

**Exemple création :**
```json
{
    "nom": "Chef de Projet Senior",
    "description": "Responsable de la gestion de projets stratégiques"
}
```

### 👥 Utilisateurs
Gestion complète des utilisateurs avec affectations et rôles

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/users` | Lister tous les utilisateurs |
| `POST` | `/users` | Créer un nouvel utilisateur |
| `GET` | `/users/{id}` | Voir un utilisateur détaillé |
| `PUT` | `/users/{id}` | Modifier un utilisateur |
| `DELETE` | `/users/{id}` | Supprimer un utilisateur |
| `GET` | `/users/{id}/affectations` | 📋 Historique affectations |
| `POST` | `/users/{id}/affecter` | 📋 Affecter à un poste |
| `POST` | `/users/{id}/terminer-affectation` | 📋 Terminer affectation |
| `POST` | `/users/{id}/assign-role` | 🛡️ Assigner un rôle |
| `DELETE` | `/users/{userId}/roles/{roleId}` | 🛡️ Retirer un rôle |

**Exemple création utilisateur :**
```json
{
    "matricule": "TECH001",
    "nom": "Sow",
    "prenom": "Moussa",
    "email": "moussa.sow@govtrack.gov",
    "telephone": "+221 77 123 45 67",
    "adresse": "Dakar, Sénégal",
    "password": "password123",
    "statut": true
}
```

**Exemple affectation :**
```json
{
    "poste_id": 2,
    "entite_id": 3,
    "date_debut": "2025-01-15",
    "terminer_affectation_precedente": true
}
```

**Exemple terminer affectation :**
```json
{
    "date_fin": "2024-12-29",
    "raison": "Mutation vers une autre direction"
}
```

### 🛡️ Rôles
Gestion des rôles et de leurs permissions

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/roles` | Lister tous les rôles |
| `POST` | `/roles` | Créer un nouveau rôle |
| `GET` | `/roles/{id}` | Voir un rôle détaillé |
| `PUT` | `/roles/{id}` | Modifier un rôle |
| `DELETE` | `/roles/{id}` | Supprimer un rôle |
| `GET` | `/roles/{id}/available-permissions` | 🔐 Permissions disponibles |
| `POST` | `/roles/{id}/assign-permission` | 🔐 Assigner permission |
| `DELETE` | `/roles/{roleId}/permissions/{permissionId}` | 🔐 Retirer permission |

**Exemple création avec permissions :**
```json
{
    "nom": "Superviseur",
    "description": "Rôle de supervision et contrôle",
    "permissions": [1, 2, 3]
}
```

### 🔐 Permissions
Gestion des permissions et traçabilité

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/permissions` | Lister toutes les permissions |
| `POST` | `/permissions` | Créer une nouvelle permission |
| `GET` | `/permissions/{id}` | Voir une permission détaillée |
| `PUT` | `/permissions/{id}` | Modifier une permission |
| `DELETE` | `/permissions/{id}` | Supprimer une permission |
| `GET` | `/permissions/{id}/users` | 👥 Utilisateurs ayant cette permission |
| `GET` | `/permissions/{id}/available-roles` | 🛡️ Rôles disponibles |

**Exemple création :**
```json
{
    "nom": "export_reports",
    "description": "Exporter les rapports et statistiques"
}
```

## 🧪 Tests Spéciaux

La collection inclut une section "Tests Spéciaux" pour tester la validation et gestion d'erreurs :

- **Test création entité avec parent** ✅
- **Test affectation avec conflit** ❌ (doit échouer)
- **Test double assignation de rôle** ❌ (doit échouer) 
- **Test suppression entité avec enfants** ❌ (doit échouer)
- **Test données utilisateur existant** ❌ (doit échouer)

## 📊 Réponses JSON Standard

### Succès
```json
{
    "success": true,
    "data": { /* données */ },
    "message": "Opération réussie"
}
```

### Erreur
```json
{
    "success": false,
    "message": "Description de l'erreur",
    "errors": { /* détails validation */ }
}
```

## 🔍 Fonctionnalités Avancées

### 🌳 Hiérarchie d'Entités
- Navigation parent/enfant
- Prévention des cycles 
- Visualisation complète de l'arbre

### 👑 Gestion des Chefs d'Entités
- Affectation/terminaison des mandats de chef
- Historique complet des dirigeants
- Suivi des durées de mandat
- Vue d'ensemble de toutes les directions

### 📋 Gestion des Affectations
- Historique complet des postes
- Gestion automatique des transitions
- Validation des conflits

### 🛡️ Système de Permissions
- Rôles multiples par utilisateur
- Permissions granulaires
- Traçabilité des assignations

### 🔐 Validations Robustes
- Unicité des matricules/emails
- Intégrité référentielle
- Vérifications métier

## 🚀 Ordre de Test Recommandé

1. **Type Entités** - Créer Direction, Service, Division
2. **Entités** - Créer hiérarchie organisationnelle
3. **Postes** - Créer postes disponibles
4. **Permissions** - Créer permissions nécessaires
5. **Rôles** - Créer rôles avec permissions
6. **Utilisateurs** - Créer utilisateurs
7. **Affectations** - Affecter utilisateurs aux postes
8. **Assignation rôles** - Donner droits aux utilisateurs

## ⚠️ Notes Importantes

- **Serveur requis** : `php artisan serve` doit être actif
- **Base de données** : Migrations et seeders exécutés
- **Validation** : Tous les champs requis doivent être fournis
- **Références** : IDs d'entités existantes pour les relations
- **Sécurité** : Éviter suppression d'éléments avec dépendances

## 🎯 Prochaines Étapes

1. **Authentification JWT/Sanctum** pour sécuriser l'API
2. **Partie 2 - Instructions/Recommandations** selon spécifications
3. **Interface Web React** pour administration
4. **Notifications par email**
5. **Rapports et tableaux de bord**

---

**✨ Votre API GovTrack est maintenant opérationnelle avec toutes les fonctionnalités de gestion des utilisateurs !** 🎉 
