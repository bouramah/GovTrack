# 🏛️ GovTrack Backend API

> **API REST complète pour la gestion gouvernementale des projets et tâches**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg)](tests)

## 📋 Table des Matières

- [🎯 Vue d'Ensemble](#-vue-densemble)
- [🚀 Fonctionnalités](#-fonctionnalités)
- [🏗️ Architecture](#️-architecture)
- [⚙️ Installation](#️-installation)
- [🔧 Configuration](#-configuration)
- [📚 API Documentation](#-api-documentation)
- [🧪 Tests](#-tests)
- [🔐 Sécurité](#-sécurité)
- [📊 Base de Données](#-base-de-données)
- [📦 Déploiement](#-déploiement)
- [🤝 Contribution](#-contribution)

## 🎯 Vue d'Ensemble

GovTrack Backend est une API REST robuste développée avec Laravel 12 pour la gestion complète des projets gouvernementaux. Elle offre un système de permissions granulaire, une traçabilité complète et une validation métier avancée.

### 🎯 Objectifs

- **Gestion centralisée** des projets et tâches gouvernementaux
- **Système de permissions** à 3 niveaux (Admin/Directeur/Employé)
- **Traçabilité complète** avec historiques et audit
- **Collaboration** via discussions et pièces jointes
- **API sécurisée** avec validation métier robuste

## 🚀 Fonctionnalités

### 🔐 Authentification & Autorisation
- **Laravel Sanctum** pour l'authentification API
- **Système de permissions granulaire** à 3 niveaux
- **Gestion des rôles** et permissions par entité
- **Middleware de sécurité** personnalisé

### 📊 Gestion des Projets
- **CRUD complet** des projets avec validation métier
- **Système de SLA** automatique
- **Niveaux d'exécution** avec commentaires
- **Historique des statuts** détaillé
- **Pièces jointes** sécurisées

### 📋 Gestion des Tâches
- **Tâches et sous-tâches** hiérarchiques
- **Assignation** par utilisateur et entité
- **Suivi d'avancement** en temps réel
- **Notifications** automatiques

### 🏢 Gestion Organisationnelle
- **Hiérarchie des entités** gouvernementales
- **Gestion des postes** et rôles
- **Historique des affectations** utilisateurs
- **Chefs d'entité** avec historique

### 💬 Collaboration
- **Discussions** structurées par projet/tâche
- **Pièces jointes** sécurisées
- **Notifications** par email
- **Audit trail** complet

### 📈 Audit & Traçabilité
- **Logs d'audit** automatiques
- **Historique des modifications** détaillé
- **Traçabilité** des actions utilisateur
- **Rapports** d'activité

## 🏗️ Architecture

### Structure du Projet

```
govtrack-backend/
├── app/
│   ├── Http/Controllers/Api/     # Contrôleurs API
│   ├── Models/                   # Modèles Eloquent
│   ├── Events/                   # Événements
│   ├── Listeners/                # Écouteurs d'événements
│   ├── Mail/                     # Classes de mail
│   ├── Notifications/            # Notifications
│   ├── Traits/                   # Traits réutilisables
│   └── Providers/                # Fournisseurs de services
├── database/
│   ├── migrations/               # Migrations de base de données
│   ├── seeders/                  # Seeders de données
│   └── factories/                # Factories pour les tests
├── routes/
│   └── api.php                   # Routes API
├── tests/                        # Tests automatisés
└── config/                       # Configuration
```

### Système de Permissions

| Niveau | Rôle | Permission | Description |
|--------|------|------------|-------------|
| 🌐 **Global** | Admin | `view_all_projects` | Accès à tous les projets |
| 🏢 **Entité** | Directeur | `view_my_entity_projects` | Projets de son entité |
| 👤 **Personnel** | Employé | `view_my_projects` | Ses projets uniquement |

### Modèles Principaux

- **User** : Utilisateurs du système
- **Entite** : Entités gouvernementales
- **Projet** : Projets avec SLA
- **Tache** : Tâches et sous-tâches
- **Discussion** : Discussions par projet/tâche
- **AuditLog** : Logs d'audit

## ⚙️ Installation

### Prérequis

- **PHP** 8.2 ou supérieur
- **Composer** 2.0 ou supérieur
- **MySQL** 8.0 ou **PostgreSQL** 13
- **Node.js** 18+ (pour les assets)
- **Git**

### Installation Rapide

```bash
# 1. Cloner le projet
git clone https://github.com/votre-org/govtrack.git
cd govtrack/govtrack-backend

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier d'environnement
cp .env.example .env

# 4. Générer la clé d'application
php artisan key:generate

# 5. Configurer la base de données dans .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=govtrack
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Exécuter les migrations
php artisan migrate

# 7. Seeder les données initiales
php artisan db:seed --class=UserManagementSeeder
php artisan db:seed --class=Partie2Seeder
php artisan db:seed --class=ProjectPermissionsSeeder

# 8. Lancer le serveur de développement
php artisan serve
```

### Installation avec Docker

```bash
# 1. Cloner le projet
git clone https://github.com/votre-org/govtrack.git
cd govtrack/govtrack-backend

# 2. Copier le fichier d'environnement
cp .env.example .env

# 3. Lancer avec Docker Compose
docker-compose up -d

# 4. Installer les dépendances
docker-compose exec app composer install

# 5. Configurer l'application
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

## 🔧 Configuration

### Variables d'Environnement

```env
# Application
APP_NAME=GovTrack
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=govtrack
DB_USERNAME=root
DB_PASSWORD=

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@govtrack.gov"
MAIL_FROM_NAME="${APP_NAME}"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost
```

### Configuration des Permissions

```php
// config/permissions.php
return [
    'levels' => [
        'global' => ['view_all_projects', 'manage_users', 'manage_entities'],
        'entity' => ['view_my_entity_projects', 'manage_entity_users'],
        'personal' => ['view_my_projects', 'manage_my_tasks']
    ]
];
```

## 📚 API Documentation

### Authentification

```bash
# Connexion
POST /api/v1/auth/login
{
    "email": "admin@govtrack.gov",
    "password": "password"
}

# Réponse
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@govtrack.gov",
        "permissions": [...]
    }
}
```

### Projets

```bash
# Liste des projets (selon permissions)
GET /api/v1/projets
Authorization: Bearer {token}

# Créer un projet
POST /api/v1/projets
{
    "titre": "Nouveau projet",
    "description": "Description du projet",
    "type_projet_id": 1,
    "entite_id": 1,
    "date_debut": "2024-01-01",
    "date_fin": "2024-12-31"
}

# Mettre à jour le niveau d'exécution
POST /api/v1/projets/{id}/niveau-execution
{
    "niveau_execution": 75,
    "commentaire": "Avancement significatif"
}
```

### Tâches

```bash
# Liste des tâches
GET /api/v1/taches
Authorization: Bearer {token}

# Créer une tâche
POST /api/v1/taches
{
    "titre": "Nouvelle tâche",
    "description": "Description de la tâche",
    "projet_id": 1,
    "type_tache_id": 1,
    "assignee_id": 2
}
```

### Utilisateurs et Entités

```bash
# Utilisateurs par entité
GET /api/v1/entites/{id}/utilisateurs
?statut=actuel&role=chef&include_historique=true

# Gestion des rôles
GET /api/v1/roles
POST /api/v1/roles
```

### Collection Postman

Importez la collection complète : `GovTrack-API-Complete.postman_collection.json`

## 🧪 Tests

### Exécution des Tests

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=ProjectTest

# Tests avec couverture
php artisan test --coverage
```

### Comptes de Test

| Rôle | Email | Mot de passe | Permissions |
|------|-------|--------------|-------------|
| 🔑 **Admin** | admin@govtrack.gov | password | Accès complet |
| 🏢 **Directeur** | amadou.diop@govtrack.gov | password | Projets d'entité |
| 👤 **Employé** | fatou.fall@govtrack.gov | password | Projets personnels |

### Tests API

```bash
# Test d'authentification
php artisan test tests/Feature/AuthTest.php

# Test des permissions
php artisan test tests/Feature/PermissionTest.php

# Test des projets
php artisan test tests/Feature/ProjectTest.php
```

## 🔐 Sécurité

### Authentification

- **Laravel Sanctum** pour l'authentification API
- **Tokens d'accès** avec expiration
- **Protection CSRF** activée
- **Validation** des entrées utilisateur

### Autorisation

- **Middleware de permissions** personnalisé
- **Vérification des rôles** par entité
- **Audit trail** de toutes les actions
- **Validation métier** stricte

### Protection des Données

- **Chiffrement** des données sensibles
- **Validation** des fichiers uploadés
- **Sanitisation** des entrées
- **Logs de sécurité** détaillés

## 📊 Base de Données

### Migrations Principales

```bash
# Structure organisationnelle
php artisan migrate --path=database/migrations/2025_06_29_152745_create_type_entites_table.php
php artisan migrate --path=database/migrations/2025_06_29_152748_create_entites_table.php
php artisan migrate --path=database/migrations/2025_06_29_152750_create_postes_table.php

# Gestion des utilisateurs
php artisan migrate --path=database/migrations/2025_06_29_152801_modify_users_table.php
php artisan migrate --path=database/migrations/2025_06_29_152816_create_roles_table.php
php artisan migrate --path=database/migrations/2025_06_29_152817_create_permissions_table.php

# Gestion des projets
php artisan migrate --path=database/migrations/2025_06_29_152823_create_type_projets_table.php
php artisan migrate --path=database/migrations/2025_06_29_152825_create_projets_table.php
php artisan migrate --path=database/migrations/2025_06_29_152827_create_taches_table.php
```

### Seeders

```bash
# Données de base
php artisan db:seed --class=UserManagementSeeder
php artisan db:seed --class=Partie2Seeder
php artisan db:seed --class=ProjectPermissionsSeeder
php artisan db:seed --class=EntityPermissionsSeeder
```

### Relations Principales

```sql
-- Utilisateurs et entités
users -> utilisateur_entite_histories -> entites
entites -> entite_chef_histories -> users

-- Projets et tâches
projets -> taches
projets -> projet_historique_statuts
taches -> tache_historique_statuts

-- Permissions
users -> roles -> permissions
```

## 📦 Déploiement

### Production

```bash
# 1. Préparer l'environnement
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Migrations
php artisan migrate --force

# 3. Optimisations
php artisan optimize
```

### Variables d'Environnement Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.govtrack.gov

DB_CONNECTION=mysql
DB_HOST=production-db-host
DB_DATABASE=govtrack_prod
DB_USERNAME=prod_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### Monitoring

- **Logs Laravel** : `storage/logs/laravel.log`
- **Logs d'audit** : Table `audit_logs`
- **Performance** : Laravel Telescope (dev)
- **Erreurs** : Sentry (production)

## 🤝 Contribution

### Développement

```bash
# 1. Fork le projet
git clone https://github.com/votre-fork/govtrack.git

# 2. Créer une branche
git checkout -b feature/nouvelle-fonctionnalite

# 3. Développer et tester
php artisan test

# 4. Commit et push
git commit -m "feat: ajouter nouvelle fonctionnalité"
git push origin feature/nouvelle-fonctionnalite

# 5. Créer une Pull Request
```

### Standards de Code

- **PSR-12** pour le style de code
- **Laravel Pint** pour le formatage
- **PHPStan** pour l'analyse statique
- **Tests unitaires** obligatoires

### Documentation

- **Commentaires** en français
- **Documentation API** à jour
- **README** maintenu
- **Changelog** détaillé

## 📞 Support

- **Documentation** : [docs.govtrack.gov](https://docs.govtrack.gov)
- **Issues** : [GitHub Issues](https://github.com/votre-org/govtrack/issues)
- **Email** : support@govtrack.gov

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

**Développé avec ❤️ pour la gestion gouvernementale** 
