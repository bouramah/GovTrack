# 📚 Documentation API GovTrack v2.0

> **Système complet de gestion d'instructions/recommandations gouvernementales**

## 🏛️ **Vue d'Ensemble**

GovTrack est une API Laravel robuste conçue pour la gestion complète des instructions et recommandations gouvernementales. Elle offre un système de permissions granulaire, une traçabilité complète et une validation métier avancée.

### 🎯 **Fonctionnalités Principales**

- ✅ **Gestion complète des projets** avec SLA automatique
- ✅ **Système de permissions à 3 niveaux** (Admin/Directeur/Employé)
- ✅ **Traçabilité complète** avec historiques détaillés
- ✅ **Validation métier** robuste pour tous les changements
- ✅ **Collaboration** via discussions et pièces jointes
- ✅ **Tableau de bord intelligent** selon les permissions
- ✅ **API sécurisée** avec authentification Bearer

## 🔐 **Système de Permissions - Innovation Clé**

### **Niveaux d'Accès Granulaires**

| Permission | Rôle | Accès Projets | Filtres Disponibles |
|------------|------|---------------|-------------------|
| `view_all_projects` | **Administrateur** | 🌐 Tous les projets | Filtres complets (porteur, donneur d'ordre, etc.) |
| `view_my_entity_projects` | **Directeur** | 🏢 Projets de son entité | Filtres sur membres de l'entité |
| `view_my_projects` | **Employé** | 👤 Ses projets personnels | Filtres de base uniquement |

### **Logique Intelligente**

- **Fallback automatique** : Si un directeur n'a pas d'entité affectée → accès personnel
- **Validation dynamique** : Permissions vérifiées à chaque endpoint
- **Informations incluses** : Chaque réponse indique le niveau d'accès

---

## 🚀 **Guide de Démarrage Rapide**

### 1️⃣ **Authentification**

```bash
# Connexion
POST /api/v1/auth/login
{
  "email": "admin@govtrack.gov",
  "password": "password"
}

# Réponse
{
  "success": true,
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "email": "admin@govtrack.gov",
      "permissions": ["view_all_projects", "manage_users", ...]
    }
  }
}
```

### 2️⃣ **Utilisation du Token**

```bash
# Headers pour toutes les requêtes suivantes
Authorization: Bearer 1|abc123...
Content-Type: application/json
```

### 3️⃣ **Test des Permissions**

```bash
# Lister les projets (résultat varie selon le niveau)
GET /api/v1/projets

# Réponse avec informations de permissions
{
  "data": [...],
  "permissions": {
    "level": "all_projects|entity_projects|my_projects",
    "can_filter_by_user": true|false,
    "description": "Description adaptée au niveau"
  }
}
```

---

## 📊 **Endpoints Principaux**

### 🔐 **Authentification**

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/auth/login` | Connexion avec email/mot de passe |
| `GET` | `/auth/me` | Informations utilisateur connecté |
| `POST` | `/auth/logout` | Déconnexion (révoque le token) |
| `POST` | `/auth/logout-all` | Déconnexion complète (tous les tokens) |

### 📈 **Projets & Tableau de Bord**

| Méthode | Endpoint | Permissions | Description |
|---------|----------|-------------|-------------|
| `GET` | `/projets/tableau-bord` | view_* | **Tableau de bord intelligent** avec stats selon permissions |
| `GET` | `/projets` | view_* | **Liste projets** avec filtrage automatique |
| `GET` | `/projets/{id}` | view_* | Détails complets d'un projet |
| `POST` | `/projets` | create_instruction | Créer un nouveau projet |
| `POST` | `/projets/{id}/changer-statut` | edit_instruction | Changer le statut avec validation |
| `POST` | `/projets/{id}/niveau-execution` | edit_instruction | **NOUVEAU** : Mettre à jour l'avancement |

### ✅ **Tâches**

| Méthode | Endpoint | Permissions | Description |
|---------|----------|-------------|-------------|
| `GET` | `/taches` | view_* | Liste des tâches selon projets accessibles |
| `GET` | `/taches/mes-taches` | Authentifié | Tâches personnelles de l'utilisateur |
| `POST` | `/taches` | edit_instruction | Créer une nouvelle tâche |
| `POST` | `/taches/{id}/changer-statut` | Responsable OU Porteur | Changer statut avec règles spéciales |
| `GET` | `/taches/{id}/historique-statuts` | view_* | **NOUVEAU** : Historique complet |

### 🏢 **Gestion des Utilisateurs**

| Méthode | Endpoint | Permissions | Description |
|---------|----------|-------------|-------------|
| `GET` | `/entites` | Authentifié | Liste complète des entités |
| `GET` | `/entites/organigramme` | Authentifié | Structure hiérarchique |
| `GET` | `/entites/{id}/utilisateurs` | Authentifié | **NOUVEAU** : Utilisateurs avec filtres |
| `GET` | `/users` | view_users | Liste des utilisateurs |
| `POST` | `/users` | manage_users | Créer un utilisateur |
| `GET` | `/roles` | Authentifié | Liste des rôles |
| `GET` | `/permissions` | Authentifié | Liste des permissions |

### 💬 **Collaboration**

| Méthode | Endpoint | Permissions | Description |
|---------|----------|-------------|-------------|
| `GET` | `/projets/{id}/discussions` | view_* | Messages du projet |
| `POST` | `/projets/{id}/discussions` | Accès projet | Poster un message |
| `GET` | `/taches/{id}/discussions` | view_* | Messages de la tâche |
| `GET` | `/projets/{id}/pieces-jointes` | view_* | Fichiers du projet |
| `POST` | `/projets/{id}/pieces-jointes` | edit_instruction | Upload fichier |

---

## 🔥 **Nouveautés v2.0**

### ⭐ **Système de Permissions à 3 Niveaux**
- **Innovation majeure** : Filtrage intelligent selon l'entité d'affectation
- **Fallback graceful** : Accès personnel si pas d'entité affectée
- **Informations incluses** : Chaque réponse indique le niveau d'accès

### ⭐ **Endpoint Niveau d'Exécution Dédié**
```bash
POST /api/v1/projets/{id}/niveau-execution
{
  "niveau_execution": 45,
  "commentaire": "Avancement significatif"
}
```
**Règles métier :**
- ✅ Seulement si statut = "en_cours"
- ❌ Impossible de mettre 100% manuellement
- ❌ Impossible de diminuer le niveau
- ✅ Historique automatique complet

### ⭐ **Endpoint Utilisateurs par Entité**
```bash
GET /api/v1/entites/{id}/utilisateurs
?statut=actuel&role=tous&include_historique=false
```
**Filtres avancés :**
- `statut` : actuel | historique | tous
- `role` : chef | employe | tous
- `include_historique` : Affectations passées

### ⭐ **Historique des Tâches**
```bash
GET /api/v1/taches/{id}/historique-statuts
```
Traçabilité complète de tous les changements avec utilisateur et dates.

---

## 📋 **Validation Métier**

### **Changements de Statut - Projets**
```
a_faire → en_cours
en_cours → bloque | demande_de_cloture
bloque → en_cours
demande_de_cloture → termine (porteur seulement)
```

### **Changements de Statut - Tâches**
```
a_faire → en_cours
en_cours → bloque | termine
bloque → en_cours
termine : Porteur du projet seulement
```

### **Niveau d'Exécution**
- **100%** : Atteint automatiquement quand toutes les tâches sont terminées
- **Diminution** : Impossible (sauf correction d'erreur par admin)
- **Changement redondant** : Bloqué pour éviter l'historique inutile

---

## 🎯 **Filtres et Pagination**

### **Projets**
```bash
GET /api/v1/projets
?page=1&per_page=10
&sort_by=date_creation&sort_order=desc
&statut=en_cours
&search=gouvernance
&porteur_id=2  # Admin/Directeur seulement
&donneur_ordre_id=1  # Admin/Directeur seulement
```

### **Utilisateurs d'Entité** (NOUVEAU)
```bash
GET /api/v1/entites/1/utilisateurs
?statut=actuel
&role=chef
&include_historique=true
&page=1&per_page=20
```

---

## ⚠️ **Gestion d'Erreurs**

### **Codes de Réponse**
- `200` : Succès
- `201` : Créé avec succès
- `400` : Erreur de validation
- `401` : Non authentifié
- `403` : Permissions insuffisantes
- `404` : Ressource non trouvée
- `422` : Erreur de validation métier

### **Format des Erreurs**
```json
{
  "success": false,
  "error": "Permissions insuffisantes",
  "details": {
    "permissions_requises": ["view_all_projects", "view_my_entity_projects", "view_my_projects"],
    "permission_actuelle": null
  }
}
```

---

## 🧪 **Tests avec Postman**

### **Collection Complète Incluse**
- **70+ endpoints** documentés et testés
- **Tests automatiques** pour validation des réponses
- **Variables d'environnement** pour faciliter les tests
- **Scénarios complets** pour chaque niveau de permission

### **Comptes de Test**
```bash
# Administrateur (toutes permissions)
admin@govtrack.gov : password

# Directeur d'entité (DSI)
amadou.diop@govtrack.gov : password

# Employé (permissions limitées)
fatou.fall@govtrack.gov : password
```

---

## 🔄 **Migration et Évolution**

### **Nouvelles Permissions Ajoutées**
```sql
-- Exécuter le seeder pour ajouter les permissions
php artisan db:seed --class=ProjectPermissionsSeeder
```

### **Rétrocompatibilité**
- ✅ Tous les endpoints existants fonctionnent
- ✅ Nouvelles permissions assignées automatiquement
- ✅ Fallback sur permissions basiques si non définies

---

## 🏆 **Architecture & Performance**

### **Optimisations**
- **Eager Loading** : Relations préchargées pour éviter N+1
- **Pagination** : Toutes les listes sont paginées
- **Cache** : Permissions en cache pour performance
- **Validation** : Middleware dédié pour les permissions

### **Sécurité**
- **Tokens Bearer** : Authentification sécurisée
- **Middleware** : Vérification des permissions à chaque endpoint
- **Validation** : Règles métier strictes
- **Audit** : Historique complet de tous les changements

---

## 📞 **Support & Contact**

- **Version** : 2.0 (Janvier 2025)
- **Laravel** : 11.x
- **PHP** : 8.2+
- **Base de données** : MySQL/PostgreSQL

**Développement** : Équipe GovTrack - Architecture modulaire et évolutive

---

*Cette documentation reflète l'état complet de l'API GovTrack v2.0 avec toutes ses fonctionnalités avancées et son système de permissions innovant.*

## Filtres Avancés pour les Projets

### Permissions et Filtres Disponibles

Les filtres disponibles dépendent du niveau de permission de l'utilisateur :

#### 🔓 Niveau 1 : `view_all_projects` (Administrateur)
- **Accès** : Tous les projets
- **Filtres disponibles** :
  - Tous les filtres de base
  - Tous les filtres de date
  - Filtres par utilisateur (porteur, donneur d'ordre)
  - Filtres par entité
  - Recherche textuelle étendue

#### 🏢 Niveau 2 : `view_my_entity_projects` (Chef d'entité)
- **Accès** : Projets de son entité
- **Filtres disponibles** :
  - Tous les filtres de base
  - Tous les filtres de date
  - Filtres par utilisateur (porteur, donneur d'ordre) - limité à son entité
  - Recherche textuelle étendue

#### 👤 Niveau 3 : `view_my_projects` (Utilisateur standard)
- **Accès** : Ses projets personnels
- **Filtres disponibles** :
  - Filtres de base uniquement
  - Tous les filtres de date
  - Recherche textuelle basique

### Filtres de Base (Tous les niveaux)

```http
GET /api/v1/projets?statut=en_cours&type_projet_id=1&en_retard=true&niveau_execution_min=50&niveau_execution_max=100
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `statut` | string | Statut du projet (a_faire, en_cours, termine, etc.) |
| `type_projet_id` | integer | ID du type de projet |
| `en_retard` | boolean | Projets en retard uniquement |
| `niveau_execution_min` | integer | Niveau d'exécution minimum (0-100) |
| `niveau_execution_max` | integer | Niveau d'exécution maximum (0-100) |

### Filtres de Date (Tous les niveaux)

```http
GET /api/v1/projets?date_debut_previsionnelle_debut=2024-01-01&date_debut_previsionnelle_fin=2024-12-31&date_creation_debut=2024-01-01&date_creation_fin=2024-12-31
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `date_debut_previsionnelle_debut` | date | Date de début prévisionnelle (>=) |
| `date_debut_previsionnelle_fin` | date | Date de début prévisionnelle (<=) |
| `date_fin_previsionnelle_debut` | date | Date de fin prévisionnelle (>=) |
| `date_fin_previsionnelle_fin` | date | Date de fin prévisionnelle (<=) |
| `date_creation_debut` | date | Date de création (>=) |
| `date_creation_fin` | date | Date de création (<=) |

### Filtres par Utilisateur (Niveaux 1 et 2)

```http
GET /api/v1/projets?porteur_id=123&donneur_ordre_id=456
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `porteur_id` | integer | ID de l'utilisateur porteur |
| `donneur_ordre_id` | integer | ID de l'utilisateur donneur d'ordre |

### Filtre par Entité (Niveau 1 uniquement)

```http
GET /api/v1/projets?entite_id=789
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `entite_id` | integer | ID de l'entité |

### Recherche Textuelle

```http
GET /api/v1/projets?search=terme de recherche
```

**Niveaux 1 et 2** : Recherche dans titre, description, nom/prénom du porteur et donneur d'ordre
**Niveau 3** : Recherche dans titre et description uniquement

### Endpoints pour les Filtres

#### Récupérer les Entités Disponibles

```http
GET /api/v1/projets/filtres/entites
```

**Permissions** : `view_all_projects`

**Réponse** :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nom": "Direction Générale",
      "type": "Direction"
    }
  ]
}
```

#### Récupérer les Utilisateurs Disponibles

```http
GET /api/v1/projets/filtres/utilisateurs
```

**Permissions** : `view_all_projects` ou `view_my_entity_projects`

**Réponse** :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nom": "Dupont",
      "prenom": "Jean",
      "email": "jean.dupont@example.com",
      "matricule": "EMP001",
      "display_name": "Jean Dupont (EMP001)"
    }
  ]
}
```

### Informations de Permissions dans la Réponse

La réponse de l'endpoint `/api/v1/projets` inclut des informations sur les filtres disponibles :

```json
{
  "success": true,
  "data": [...],
  "pagination": {...},
  "permissions": {
    "level": "all_projects",
    "can_filter_by_user": true,
    "can_filter_by_entity": true,
    "can_filter_by_date": true,
    "available_filters": {
      "basic": ["statut", "type_projet_id", "en_retard", "niveau_execution_min", "niveau_execution_max", "search"],
      "date": ["date_debut_previsionnelle_debut", "date_debut_previsionnelle_fin", "date_fin_previsionnelle_debut", "date_fin_previsionnelle_fin", "date_creation_debut", "date_creation_fin"],
      "user": ["porteur_id", "donneur_ordre_id"],
      "entity": ["entite_id"]
    },
    "description": "Accès complet à tous les projets"
  }
}
```

### Exemples d'Utilisation

#### Filtre Complexe (Administrateur)
```http
GET /api/v1/projets?statut=en_cours&entite_id=5&date_debut_previsionnelle_debut=2024-01-01&niveau_execution_min=25&search=urgent
```

#### Filtre Simple (Utilisateur Standard)
```http
GET /api/v1/projets?statut=en_cours&date_creation_debut=2024-01-01&search=mon projet
```

#### Filtre par Utilisateur (Chef d'Entité)
```http
GET /api/v1/projets?porteur_id=123&en_retard=true&date_fin_previsionnelle_fin=2024-12-31
```
