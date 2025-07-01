# 🚀 GovTrack API - Documentation Complète

## 🎯 Vue d'ensemble

L'API GovTrack est un système complet de gestion et de suivi d'instructions/recommandations gouvernementales. Elle combine la gestion des utilisateurs (Partie 1) avec la gestion des projets, tâches, discussions et pièces jointes (Partie 2).

### 🏗️ Architecture

- **Framework**: Laravel 11 avec authentification Sanctum
- **Base de données**: MySQL avec 16 tables relationnelles
- **Sécurité**: Authentification Bearer token + système de permissions granulaires
- **Format**: API REST avec réponses JSON standardisées

### 🔗 URLs de base

- **Local**: `http://127.0.0.1:8000/api/v1`
- **Production**: `https://api.govtrack.gov/v1`

---

## 🔐 Authentification

### Obtenir un token d'accès

```http
POST /auth/login
Content-Type: application/json

{
    "email": "admin@govtrack.gov",
    "password": "password123"
}
```

**Réponse:**
```json
{
    "success": true,
    "message": "Connexion réussie",
    "access_token": "1|laravel_sanctum_token...",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "matricule": "ADMIN001",
        "nom": "Administrateur",
        "prenom": "Système",
        "email": "admin@govtrack.gov"
    }
}
```

### Utilisation du token

Tous les endpoints (sauf login) nécessitent l'en-tête d'autorisation:

```http
Authorization: Bearer {access_token}
```

### Déconnexion

```http
POST /auth/logout
Authorization: Bearer {token}
```

---

## 📋 Partie 2: Gestion des Instructions/Recommandations

### 🏷️ Types de Projets (SLA)

Les types de projets définissent les SLA (Service Level Agreement) automatiques.

#### Créer un type de projet

```http
POST /type-projets
Authorization: Bearer {token}
Content-Type: application/json

{
    "nom": "Instruction Urgente",
    "description": "Instructions nécessitant une réponse rapide",
    "duree_previsionnelle_jours": 7,
    "description_sla": "Délai de 7 jours pour les instructions urgentes"
}
```

#### Lister les types de projets

```http
GET /type-projets
Authorization: Bearer {token}
```

#### Voir un type de projet

```http
GET /type-projets/{id}
Authorization: Bearer {token}
```

#### Statistiques d'un type de projet

```http
GET /type-projets/{id}/statistiques
Authorization: Bearer {token}
```

**Réponse:**
```json
{
    "success": true,
    "data": {
        "total_projets": 15,
        "projets_en_retard": 3,
        "duree_moyenne_realisation": 8.5,
        "taux_respect_sla": 87.5
    }
}
```

---

### 📊 Projets (Instructions/Recommandations)

#### Créer un projet avec SLA automatique

```http
POST /projets
Authorization: Bearer {token}
Content-Type: application/json

{
    "titre": "Digitalisation des Processus",
    "description": "Mise en place d'un système de digitalisation des processus administratifs",
    "type_projet_id": 1,
    "porteur_id": 2,
    "donneur_ordre_id": 1,
    "date_debut_previsionnelle": "2025-01-15"
}
```

> **💡 SLA Automatique**: La `date_fin_previsionnelle` sera calculée automatiquement en ajoutant la `duree_previsionnelle_jours` du type de projet à la date de début.

#### Créer un projet avec dates personnalisées

```http
POST /projets
Authorization: Bearer {token}
Content-Type: application/json

{
    "titre": "Formation du Personnel",
    "description": "Programme de formation complet",
    "type_projet_id": 1,
    "porteur_id": 2,
    "donneur_ordre_id": 1,
    "date_debut_previsionnelle": "2025-01-20",
    "date_fin_previsionnelle": "2025-03-15",
    "justification_modification_dates": "Délai étendu nécessaire pour coordonner avec tous les départements"
}
```

> **⚠️ Justification Obligatoire**: Lorsque vous spécifiez des dates personnalisées qui diffèrent du SLA, une justification est obligatoire.

#### Lister les projets

```http
GET /projets?per_page=10&sort_by=date_creation&sort_order=desc&statut=en_cours
Authorization: Bearer {token}
```

**Paramètres de filtrage:**
- `per_page`: Nombre d'éléments par page (défaut: 15)
- `sort_by`: Champ de tri (`date_creation`, `titre`, `niveau_execution`)
- `sort_order`: Ordre (`asc`, `desc`)
- `statut`: Filtrer par statut
- `type_projet_id`: Filtrer par type de projet
- `porteur_id`: Filtrer par porteur

#### Tableau de bord des projets

```http
GET /projets/tableau-bord
Authorization: Bearer {token}
```

**Réponse:**
```json
{
    "success": true,
    "data": {
        "statistiques_generales": {
            "total_projets": 45,
            "projets_en_cours": 18,
            "projets_termines": 22,
            "projets_en_retard": 5
        },
        "repartition_par_statut": {
            "a_faire": 8,
            "en_cours": 18,
            "termines": 22,
            "en_retard": 5
        },
        "projets_urgents": [
            {
                "id": 12,
                "titre": "Mise à jour Sécurité",
                "jours_restants": 2,
                "niveau_execution": 75
            }
        ],
        "performance_sla": {
            "taux_respect": 85.5,
            "duree_moyenne": 12.3
        }
    }
}
```

#### Changer le statut d'un projet

```http
POST /projets/{id}/changer-statut
Authorization: Bearer {token}
Content-Type: application/json

{
    "nouveau_statut": "en_cours",
    "commentaire": "Démarrage officiel du projet après validation des ressources"
}
```

**Statuts disponibles:**
- `a_faire`: À faire
- `en_cours`: En cours
- `demande_de_cloture`: Demande de clôture (nécessite justificatif)
- `termine`: Terminé
- `annule`: Annulé

> **🔒 Validation Justificatifs**: Pour passer au statut `demande_de_cloture`, le projet doit avoir au moins une pièce jointe marquée comme justificatif (`est_justificatif: true`).

#### Modifier un projet

```http
PUT /projets/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "titre": "Digitalisation des Processus - Phase 1",
    "description": "Première phase de la digitalisation",
    "niveau_execution": 45
}
```

---

### ✅ Tâches

#### Créer une tâche

```http
POST /taches
Authorization: Bearer {token}
Content-Type: application/json

{
    "titre": "Analyse des Besoins",
    "description": "Analyser les besoins spécifiques de chaque département",
    "projet_id": 1,
    "responsable_id": 3,
    "date_debut_previsionnelle": "2025-01-15",
    "date_fin_previsionnelle": "2025-01-20"
}
```

#### Lister les tâches

```http
GET /taches?projet_id=1&responsable_id=3&statut=en_cours
Authorization: Bearer {token}
```

#### Mes tâches assignées

```http
GET /taches/mes-taches
Authorization: Bearer {token}
```

#### Changer le statut d'une tâche

```http
POST /taches/{id}/changer-statut
Authorization: Bearer {token}
Content-Type: application/json

{
    "nouveau_statut": "termine",
    "commentaire": "Analyse terminée avec succès",
    "niveau_execution": 100
}
```

> **🔐 Validation Porteur**: Seul le porteur du projet peut terminer une tâche (passer au statut `termine`).

> **🔒 Justificatifs Tâches**: Pour certains statuts critiques, des justificatifs peuvent être requis via les pièces jointes.

---

### 📎 Pièces Jointes et Justificatifs

#### Upload d'une pièce jointe (Projet)

```http
POST /projets/{id}/pieces-jointes
Authorization: Bearer {token}
Content-Type: multipart/form-data

fichier: [FILE]
description: "Document de spécifications techniques"
est_justificatif: false
```

#### Upload d'un justificatif obligatoire

```http
POST /projets/{id}/pieces-jointes
Authorization: Bearer {token}
Content-Type: multipart/form-data

fichier: [FILE]
description: "Justificatif de clôture du projet"
est_justificatif: true
```

> **📄 Formats supportés**: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG
> **📏 Taille maximum**: 10 MB par fichier

#### Lister les pièces jointes

```http
GET /projets/{id}/pieces-jointes
Authorization: Bearer {token}
```

#### Télécharger une pièce jointe

```http
GET /projets/{projet_id}/pieces-jointes/{id}/download
Authorization: Bearer {token}
```

#### Statistiques des pièces jointes

```http
GET /projets/{id}/pieces-jointes/statistiques
Authorization: Bearer {token}
```

**Réponse:**
```json
{
    "success": true,
    "data": {
        "total_fichiers": 12,
        "total_justificatifs": 3,
        "taille_totale_mo": 45.6,
        "types_documents": {
            "PDF": 8,
            "DOCX": 3,
            "XLSX": 1
        }
    }
}
```

#### Pièces jointes pour les tâches

Les mêmes endpoints sont disponibles pour les tâches en remplaçant `/projets/{id}` par `/taches/{id}`.

---

### 💬 Discussions Collaboratives

#### Poster un message principal

```http
POST /projets/{id}/discussions
Authorization: Bearer {token}
Content-Type: application/json

{
    "message": "Début du projet de digitalisation. Merci de partager vos idées et commentaires."
}
```

#### Répondre à un message

```http
POST /projets/{id}/discussions
Authorization: Bearer {token}
Content-Type: application/json

{
    "message": "Excellente initiative ! Je suggère de commencer par une phase pilote.",
    "parent_id": 1
}
```

#### Lister les discussions

```http
GET /projets/{id}/discussions?per_page=20
Authorization: Bearer {token}
```

**Réponse hiérarchique:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "message": "Message principal",
            "auteur": {
                "nom": "Diallo",
                "prenom": "Amadou"
            },
            "date_creation": "2025-01-15T10:30:00Z",
            "reponses": [
                {
                    "id": 2,
                    "message": "Réponse au message",
                    "auteur": {
                        "nom": "Fall",
                        "prenom": "Fatou"
                    },
                    "date_creation": "2025-01-15T11:15:00Z"
                }
            ]
        }
    ]
}
```

#### Modifier un message

```http
PUT /projets/{projet_id}/discussions/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "message": "Message modifié avec mise à jour"
}
```

> **🔐 Autorisation**: Seul l'auteur du message peut le modifier ou le supprimer.

#### Supprimer un message

```http
DELETE /projets/{projet_id}/discussions/{id}
Authorization: Bearer {token}
```

#### Statistiques des discussions

```http
GET /projets/{id}/discussions/statistiques
Authorization: Bearer {token}
```

#### Discussions pour les tâches

Les mêmes endpoints sont disponibles pour les tâches: `/taches/{id}/discussions`

---

## 👥 Partie 1: Gestion des Utilisateurs

### Utilisateurs

#### Lister les utilisateurs

```http
GET /users?per_page=20&search=diallo
Authorization: Bearer {token}
```

#### Créer un utilisateur

```http
POST /users
Authorization: Bearer {token}
Content-Type: application/json

{
    "matricule": "EMP001",
    "nom": "Diallo",
    "prenom": "Mamadou",
    "email": "mamadou.diallo@govtrack.gov",
    "telephone": "+221771234567",
    "adresse": "Dakar, Sénégal",
    "statut": true
}
```

#### Affecter un utilisateur

```http
POST /users/{id}/affecter
Authorization: Bearer {token}
Content-Type: application/json

{
    "poste_id": 2,
    "entite_id": 3,
    "date_debut": "2025-01-15",
    "terminer_affectation_precedente": true
}
```

### Entités et Hiérarchie

#### Lister les entités

```http
GET /entites
Authorization: Bearer {token}
```

#### Organigramme complet

```http
GET /entites/organigramme
Authorization: Bearer {token}
```

#### Créer une entité

```http
POST /entites
Authorization: Bearer {token}
Content-Type: application/json

{
    "nom": "Service Communication",
    "type_entite_id": 2,
    "parent_id": 1,
    "description": "Service de communication et relations publiques"
}
```

---

## 🔒 Système de Permissions

### Permissions disponibles

- `manage_users`: Gestion complète des utilisateurs
- `manage_entities`: Gestion des entités et types d'entités
- `manage_roles`: Gestion des rôles et permissions
- `manage_projects`: Gestion complète des projets
- `manage_tasks`: Gestion des tâches
- `manage_discussions`: Modération des discussions

### Attribuer un rôle

```http
POST /users/{id}/assign-role
Authorization: Bearer {token}
Content-Type: application/json

{
    "role_id": 2
}
```

---

## 📊 Fonctionnalités Avancées

### 🎯 Équipe Projet Automatique

Le système maintient automatiquement l'équipe d'un projet:
- **Porteur du projet**: Automatiquement dans l'équipe
- **Responsables de tâches**: Rejoignent l'équipe dès qu'ils sont assignés
- **Participants actifs**: Utilisateurs qui postent dans les discussions

### 🕒 Calcul Automatique du Niveau d'Exécution

Le niveau d'exécution d'un projet se met à jour automatiquement:
```
Niveau Projet = Moyenne pondérée des niveaux des tâches
```

### 📈 SLA et Alertes

- **Calcul automatique** des dates prévisionnelles selon le type de projet
- **Détection des retards** avec alertes dans le tableau de bord
- **Suivi des performances** avec métriques de respect des SLA

### 🔍 Historique et Traçabilité

Tous les changements sont enregistrés:
- **Changements de statut** avec horodatage et commentaires
- **Modifications des affectations** avec historique complet
- **Actions utilisateur** avec logs d'audit

---

## 🚨 Gestion d'Erreurs

### Codes de statut HTTP

- `200`: Succès
- `201`: Créé avec succès
- `400`: Erreur de validation
- `401`: Non authentifié
- `403`: Non autorisé (permissions insuffisantes)
- `404`: Ressource non trouvée
- `422`: Erreur de validation métier
- `500`: Erreur serveur interne

### Format de réponse d'erreur

```json
{
    "success": false,
    "message": "Description de l'erreur",
    "errors": {
        "champ": ["Détail de l'erreur de validation"]
    }
}
```

### Erreurs spécifiques

#### Justificatifs manquants

```json
{
    "success": false,
    "message": "Justificatif obligatoire pour demander la clôture",
    "code": "JUSTIFICATIF_REQUIRED"
}
```

#### Permissions insuffisantes

```json
{
    "success": false,
    "message": "Permission 'manage_projects' requise pour cette action",
    "code": "INSUFFICIENT_PERMISSIONS"
}
```

---

## 🧪 Tests et Validation

### Script de test automatisé

Un script bash complet est fourni:

```bash
./test-api-partie2.sh
```

Ce script teste:
- ✅ Authentification
- ✅ Types de projets avec SLA
- ✅ Création de projets
- ✅ Gestion des tâches
- ✅ Upload de pièces jointes
- ✅ Justificatifs obligatoires
- ✅ Discussions collaboratives
- ✅ Logiques métier avancées

### Collection Postman

Importez `GovTrack-API-Secured.postman_collection.json` pour tester tous les endpoints avec des exemples pré-configurés.

---

## 📋 Exemples d'Usage Complets

### Workflow typique: Création et suivi d'un projet

1. **Créer un type de projet**
2. **Créer le projet** (SLA automatique)
3. **Ajouter des tâches**
4. **Uploader des documents**
5. **Suivre via discussions**
6. **Marquer les jalons**
7. **Clôturer avec justificatifs**

### Cas d'usage: Instruction urgente

```bash
# 1. Créer type urgent (3 jours)
POST /type-projets {"duree_previsionnelle_jours": 3}

# 2. Créer l'instruction
POST /projets {"type_projet_id": 1, "titre": "Sécurité Urgente"}

# 3. Assigner tâches
POST /taches {"projet_id": 1, "responsable_id": 2}

# 4. Suivre quotidiennement
GET /projets/tableau-bord

# 5. Uploader justificatifs
POST /projets/1/pieces-jointes {"est_justificatif": true}

# 6. Clôturer
POST /projets/1/changer-statut {"nouveau_statut": "demande_de_cloture"}
```

---

## 🔧 Configuration et Déploiement

### Variables d'environnement

```env
# API
APP_URL=https://api.govtrack.gov
API_VERSION=v1

# Stockage des fichiers
FILESYSTEM_DISK=public
MAX_FILE_SIZE=10240  # 10MB

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=govtrack_db

# Authentification
SANCTUM_EXPIRATION=1440  # 24 heures
```

### Commandes artisan utiles

```bash
# Migrations
php artisan migrate --seed

# Permissions et cache
php artisan permission:cache-reset
php artisan config:cache

# Stockage
php artisan storage:link
```

---

## 📞 Support et Contact

- **Documentation technique**: Consultez ce fichier
- **Collection Postman**: Importez pour tester
- **Script de test**: `./test-api-partie2.sh`
- **Logs d'erreur**: `storage/logs/laravel.log`

---

**Version**: 2.0  
**Dernière mise à jour**: Janvier 2025  
**Statut**: Production Ready ✅
