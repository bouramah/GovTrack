# 🏛️ GovTrack - API de Gestion Gouvernementale

> **Système complet de gestion d'instructions et recommandations gouvernementales**

![Version](https://img.shields.io/badge/version-2.0-blue.svg)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## 🎯 **Vue d'Ensemble**

GovTrack est une API REST robuste développée avec Laravel pour la gestion complète des instructions et recommandations gouvernementales. Elle offre un système de permissions granulaire, une traçabilité complète et une validation métier avancée.

### ⭐ **Fonctionnalités Clés**

- 🔐 **Système de permissions à 3 niveaux** (Admin/Directeur/Employé)
- 📊 **Tableau de bord intelligent** adapté aux permissions
- ✅ **Gestion complète des projets et tâches** avec SLA automatique
- 🏢 **Organisation hiérarchique** des entités et utilisateurs
- 💬 **Collaboration** via discussions et pièces jointes
- 📈 **Traçabilité complète** avec historiques détaillés
- 🚀 **API sécurisée** avec validation métier robuste

---

## 🏗️ **Architecture du Projet**

### **Structure en 3 Parties**

```
📁 GovTrack/
├── 🔐 Partie 1: Gestion des Utilisateurs
│   ├── Authentification sécurisée
│   ├── Gestion des entités/organisations
│   ├── Système de rôles et permissions
│   └── Hiérarchie organisationnelle
│
├── 📊 Partie 2: Gestion des Projets  
│   ├── Projets avec SLA automatique
│   ├── Tâches et sous-tâches
│   ├── Système de permissions à 3 niveaux
│   └── Validation métier avancée
│
└── 💬 Partie 3: Collaboration
    ├── Discussions structurées
    ├── Pièces jointes sécurisées
    ├── Historiques et audit
    └── Notifications (à venir)
```

### **Innovation : Système de Permissions Granulaire**

| Niveau | Rôle | Accès | Description |
|--------|------|-------|-------------|
| 🌐 **Global** | Admin | `view_all_projects` | Tous les projets + filtres complets |
| 🏢 **Entité** | Directeur | `view_my_entity_projects` | Projets de son entité |
| 👤 **Personnel** | Employé | `view_my_projects` | Ses projets uniquement |

### **Permissions de Terminaison de Projets**

| Rôle | Permission | Description |
|------|------------|-------------|
| 🔓 **Admin** | `terminate_project` | Peut terminer tous les projets |
| 🏢 **Directeur** | `terminate_project` | Peut terminer les projets de son entité |
| 👤 **Employé** | ❌ Aucune | Ne peut pas terminer les projets |

---

## 🚀 **Installation & Configuration**

### **Prérequis**
- PHP 8.2+
- Composer
- MySQL/PostgreSQL
- Laravel 11.x

### **Installation**

```bash
# 1. Cloner le projet
git clone https://github.com/votre-org/govtrack.git
cd govtrack/govtrack-backend

# 2. Installer les dépendances
composer install

# 3. Configuration environnement
cp .env.example .env
php artisan key:generate

# 4. Configuration base de données
# Modifier .env avec vos paramètres DB

# 5. Migrations et données initiales
php artisan migrate
php artisan db:seed --class=UserManagementSeeder
php artisan db:seed --class=Partie2Seeder
php artisan db:seed --class=ProjectPermissionsSeeder

# 6. Lancement du serveur
php artisan serve
```

### **Comptes de Test Créés**

```bash
# 🔑 Administrateur (toutes permissions)
Email: admin@govtrack.gov
Mot de passe: password
Permissions: view_all_projects, manage_users, manage_entities

# 🏢 Directeur d'Entité (DSI)
Email: amadou.diop@govtrack.gov  
Mot de passe: password
Permissions: view_my_entity_projects, view_my_projects

# 👤 Employé
Email: fatou.fall@govtrack.gov
Mot de passe: password
Permissions: view_my_projects
```

---

## 📋 **Guide d'Utilisation**

### **1. Authentification**

```bash
# Connexion
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@govtrack.gov","password":"password"}'
```

### **2. Test des Permissions**

```bash
# Liste des projets (varie selon le niveau d'accès)
curl -X GET http://127.0.0.1:8000/api/v1/projets \
  -H "Authorization: Bearer TOKEN"

# Réponse avec infos de permissions
{
  "data": [...],
  "permissions": {
    "level": "all_projects",
    "can_filter_by_user": true,
    "description": "Accès complet à tous les projets"
  }
}
```

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

### ⭐ **Endpoint Utilisateurs par Entité**
```bash
GET /api/v1/entites/{id}/utilisateurs
?statut=actuel&role=chef&include_historique=true
```

---

## �� **Documentation & Tests**

### **Collection Postman Complète**
- **70+ endpoints** documentés et testés
- **Tests automatiques** pour validation
- **Variables d'environnement** configurées
- **Scénarios complets** pour chaque permission

```bash
# Import dans Postman
Fichier: GovTrack-API-Complete.postman_collection.json
```

### **Documentation API**
- **Guide complet** : `API-Documentation.md`
- **Endpoints détaillés** avec exemples
- **Architecture** et règles métier

---

## 🧪 **Tests et Validation**

### **Comptes de Test**

| Niveau | Email | Mot de passe | Permissions |
|--------|-------|--------------|-------------|
| 🔑 **Admin** | admin@govtrack.gov | password | Accès complet |
| 🏢 **Directeur** | amadou.diop@govtrack.gov | password | Projets d'entité |
| 👤 **Employé** | fatou.fall@govtrack.gov | password | Projets personnels |

### **Tests avec Postman**

1. **Importer** la collection `GovTrack-API-Complete.postman_collection.json`
2. **Se connecter** avec un des comptes test
3. **Tester** les différents niveaux de permissions
4. **Valider** les règles métier

---

## 🗃️ **Base de Données**

### **Migrations Principales**

```bash
# Structure organisationnelle
create_type_entites_table.php
create_entites_table.php
create_postes_table.php

# Gestion des utilisateurs
modify_users_table.php
create_roles_table.php
create_permissions_table.php

# Gestion des projets
create_type_projets_table.php
create_projets_table.php
create_taches_table.php

# Historiques et audit
create_projet_historique_statuts_table.php
create_tache_historique_statuts_table.php
```

### **Seeders de Données**

```bash
# Données de base
php artisan db:seed --class=UserManagementSeeder      # Utilisateurs et structure
php artisan db:seed --class=Partie2Seeder             # Projets et tâches
php artisan db:seed --class=ProjectPermissionsSeeder  # Nouvelles permissions
```

---

## 🔐 **Sécurité & Performance**

### **Sécurité**
- **Laravel Sanctum** pour les tokens API
- **Middleware** de vérification des permissions
- **Validation** des entrées utilisateur
- **Audit complet** des actions

### **Performance**
- **Eager Loading** pour éviter les requêtes N+1
- **Pagination** sur toutes les listes
- **Index** sur les colonnes critiques
- **Cache** des permissions utilisateur

---

## 📈 **Statut du Projet**

✅ **Partie 1** : Gestion des utilisateurs - **TERMINÉE**  
✅ **Partie 2** : Gestion des projets - **TERMINÉE**  
✅ **Partie 3** : Collaboration - **TERMINÉE**  
🚀 **Version 2.0** : Système de permissions avancé - **DÉPLOYÉE**

---

## 📞 **Informations Techniques**

### **Technologies**
- **Backend** : Laravel 11.x + PHP 8.2+
- **Base de données** : MySQL/PostgreSQL
- **Authentification** : Laravel Sanctum
- **API** : REST avec validation complète

### **Standards**
- **PSR-12** pour le style de code
- **Tests** unitaires et d'intégration
- **Documentation** complète

---

## 📄 **Licence**

Ce projet est sous licence MIT.

---

*GovTrack v2.0 - Une solution complète et professionnelle pour la gestion gouvernementale moderne.*
