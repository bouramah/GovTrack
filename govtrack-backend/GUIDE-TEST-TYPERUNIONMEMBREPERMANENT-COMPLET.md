# Guide de Test - TypeReunionMembrePermanentService

## 📊 Vue d'ensemble

Ce guide couvre le test complet du **TypeReunionMembrePermanentService** qui gère les membres permanents des types de réunion.

### **🔍 Corrections appliquées :**

**Problème identifié :** Le contrôleur utilisait directement les relations du modèle au lieu du service dédié.

**Solution :** Intégration du service dans le contrôleur :
- Injection des services dans le constructeur
- Utilisation des méthodes du service au lieu des relations directes
- Validation améliorée des données avec rôles et notifications

## 🚀 Configuration

### **Variables d'environnement Postman :**

```json
{
  "base_url": "http://localhost:8000",
  "auth_token": "your_auth_token_here",
  "type_reunion_id": "1",
  "membre_user_id": "4",
  "membre_user_id_2": "5",
  "source_type_reunion_id": "1",
  "destination_type_reunion_id": "2"
}
```

### **Prérequis :**
- Serveur Laravel démarré
- Token d'authentification valide
- Types de réunion existants dans la base de données
- Utilisateurs existants pour les tests

## 📋 Tests détaillés

### **1. Récupérer les Membres Permanents**
```http
GET {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres
```

**Description :** Récupérer la liste des membres permanents d'un type de réunion.

**Réponse attendue :**
```json
{
  "success": true,
  "data": [
    {
      "id": 4,
      "nom": "Durand Marie",
      "prenom": "Marie",
      "email": "marie.durand@example.com",
      "pivot": {
        "type_reunion_id": 1,
        "user_id": 4,
        "role_defaut": "PARTICIPANT",
        "actif": true,
        "notifications_par_defaut": [
          "CONFIRMATION_PRESENCE",
          "RAPPEL_24H"
        ],
        "date_creation": "2025-07-27T17:45:00.000000Z",
        "date_modification": "2025-07-27T17:45:00.000000Z"
      }
    }
  ],
  "message": "Membres récupérés avec succès"
}
```

### **2. Ajouter un Membre Permanent**
```http
POST {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres
Content-Type: application/json

{
  "membres": [
    {
      "user_id": {{membre_user_id}},
      "role_defaut": "PARTICIPANT",
      "notifications_par_defaut": [
        "CONFIRMATION_PRESENCE",
        "RAPPEL_24H"
      ],
      "actif": true
    }
  ]
}
```

**Description :** Ajouter un membre permanent à un type de réunion.

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Ajout terminé : 1 succès, 0 erreurs",
  "results": [
    {
      "success": true,
      "message": "Membre permanent ajouté avec succès",
      "data": {
        "type_reunion_id": 1,
        "membre_id": 4,
        "membre": {
          "id": 4,
          "nom": "Durand Marie",
          "prenom": "Marie",
          "email": "marie.durand@example.com"
        }
      }
    }
  ]
}
```

### **3. Ajouter Plusieurs Membres Permanents**
```http
POST {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres
Content-Type: application/json

{
  "membres": [
    {
      "user_id": {{membre_user_id}},
      "role_defaut": "PRESIDENT",
      "notifications_par_defaut": [
        "CONFIRMATION_PRESENCE",
        "RAPPEL_24H",
        "RAPPEL_1H"
      ],
      "actif": true
    },
    {
      "user_id": {{membre_user_id_2}},
      "role_defaut": "SECRETAIRE",
      "notifications_par_defaut": [
        "CONFIRMATION_PRESENCE",
        "PV_DISPONIBLE"
      ],
      "actif": true
    }
  ]
}
```

**Description :** Ajouter plusieurs membres permanents à un type de réunion.

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Ajout terminé : 2 succès, 0 erreurs",
  "results": [
    {
      "success": true,
      "message": "Membre permanent ajouté avec succès"
    },
    {
      "success": true,
      "message": "Membre permanent ajouté avec succès"
    }
  ]
}
```

### **4. Supprimer un Membre Permanent**
```http
DELETE {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres
Content-Type: application/json

{
  "membres": [{{membre_user_id}}]
}
```

**Description :** Supprimer un membre permanent d'un type de réunion.

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Suppression terminée : 1 succès, 0 erreurs",
  "results": [
    {
      "success": true,
      "message": "Membre permanent retiré avec succès"
    }
  ]
}
```

### **5. Supprimer Plusieurs Membres Permanents**
```http
DELETE {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres
Content-Type: application/json

{
  "membres": [{{membre_user_id}}, {{membre_user_id_2}}]
}
```

**Description :** Supprimer plusieurs membres permanents d'un type de réunion.

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Suppression terminée : 2 succès, 0 erreurs",
  "results": [
    {
      "success": true,
      "message": "Membre permanent retiré avec succès"
    },
    {
      "success": true,
      "message": "Membre permanent retiré avec succès"
    }
  ]
}
```

### **6. Vérifier si Utilisateur est Membre Permanent**
```http
GET {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres?user_id={{membre_user_id}}
```

**Description :** Vérifier si un utilisateur est membre permanent d'un type de réunion.

**Réponse attendue :**
```json
{
  "success": true,
  "data": [
    {
      "id": 4,
      "nom": "Durand Marie",
      "prenom": "Marie",
      "email": "marie.durand@example.com",
      "pivot": {
        "type_reunion_id": 1,
        "user_id": 4,
        "role_defaut": "PARTICIPANT",
        "actif": true,
        "notifications_par_defaut": [
          "CONFIRMATION_PRESENCE",
          "RAPPEL_24H"
        ]
      }
    }
  ],
  "message": "Membres récupérés avec succès"
}
```

### **7. Obtenir le Rôle Défaut d'un Membre**
```http
GET {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres?user_id={{membre_user_id}}&role_defaut=true
```

**Description :** Obtenir le rôle défaut d'un membre permanent.

**Réponse attendue :**
```json
{
  "success": true,
  "data": [
    {
      "id": 4,
      "nom": "Durand Marie",
      "prenom": "Marie",
      "email": "marie.durand@example.com",
      "role_defaut": "PARTICIPANT",
      "actif": true
    }
  ],
  "message": "Membres récupérés avec succès"
}
```

### **8. Obtenir les Notifications par Défaut**
```http
GET {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres?user_id={{membre_user_id}}&notifications=true
```

**Description :** Obtenir les notifications par défaut d'un membre permanent.

**Réponse attendue :**
```json
{
  "success": true,
  "data": [
    {
      "id": 4,
      "nom": "Durand Marie",
      "prenom": "Marie",
      "email": "marie.durand@example.com",
      "notifications_par_defaut": [
        "CONFIRMATION_PRESENCE",
        "RAPPEL_24H"
      ],
      "actif": true
    }
  ],
  "message": "Membres récupérés avec succès"
}
```

### **9. Statistiques des Membres Permanents**
```http
GET {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres?stats=true
```

**Description :** Obtenir les statistiques des membres permanents.

**Réponse attendue :**
```json
{
  "success": true,
  "data": {
    "total_membres": 5,
    "membres_actifs": 4,
    "membres_inactifs": 1,
    "repartition_roles": {
      "PRESIDENT": 1,
      "SECRETAIRE": 1,
      "PARTICIPANT": 2,
      "OBSERVATEUR": 1
    },
    "repartition_notifications": {
      "CONFIRMATION_PRESENCE": 5,
      "RAPPEL_24H": 3,
      "RAPPEL_1H": 1,
      "PV_DISPONIBLE": 1
    },
    "membres": [...]
  },
  "message": "Membres récupérés avec succès"
}
```

### **10. Copier les Membres Permanents**
```http
POST {{base_url}}/api/v1/types-reunions/{{type_reunion_id}}/membres/copy
Content-Type: application/json

{
  "source_type_reunion_id": {{source_type_reunion_id}},
  "destination_type_reunion_id": {{destination_type_reunion_id}}
}
```

**Description :** Copier les membres permanents d'un type de réunion vers un autre.

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Membres permanents copiés avec succès",
  "data": {
    "source_type_reunion_id": 1,
    "destination_type_reunion_id": 2,
    "membres_copies": 3,
    "details": [
      {
        "user_id": 4,
        "status": "copied",
        "role_defaut": "PARTICIPANT",
        "notifications_par_defaut": [
          "CONFIRMATION_PRESENCE",
          "RAPPEL_24H"
        ]
      }
    ]
  }
}
```

## 🔧 Méthodes du Service

### **TypeReunionMembrePermanentService.php :**

1. **`addMembrePermanent()`** - Ajouter un membre permanent
2. **`updateMembrePermanent()`** - Mettre à jour un membre permanent
3. **`removeMembrePermanent()`** - Supprimer un membre permanent
4. **`getMembresPermanents()`** - Récupérer les membres permanents
5. **`isMembrePermanent()`** - Vérifier si utilisateur est membre permanent
6. **`getMembreRoleDefaut()`** - Obtenir le rôle défaut
7. **`getMembreNotificationsDefaut()`** - Obtenir les notifications par défaut
8. **`getStats()`** - Statistiques des membres permanents
9. **`copierMembresPermanents()`** - Copier les membres permanents

### **TypeReunionController.php :**

1. **`membres()`** - GET `/api/v1/types-reunions/{id}/membres`
2. **`addMembres()`** - POST `/api/v1/types-reunions/{id}/membres`
3. **`removeMembres()`** - DELETE `/api/v1/types-reunions/{id}/membres`

## ✅ Validation des Routes

```bash
php artisan route:list | grep "types-reunions.*membres"
```

**Routes disponibles :**
- `GET api/v1/types-reunions/{id}/membres`
- `POST api/v1/types-reunions/{id}/membres`
- `DELETE api/v1/types-reunions/{id}/membres`

## 🎯 Points de Test Clés

### **1. Validation des données :**
- User ID existant
- Rôles valides (PRESIDENT, SECRETAIRE, PARTICIPANT, OBSERVATEUR)
- Notifications valides
- Statut actif/inactif
- Contraintes d'unicité

### **2. Gestion des erreurs :**
- Membre déjà existant
- Type de réunion inexistant
- Utilisateur inexistant
- Rôles invalides

### **3. Opérations multiples :**
- Ajout de plusieurs membres
- Suppression de plusieurs membres
- Copie entre types de réunion

### **4. Rôles et notifications :**
- Rôles par défaut
- Notifications par défaut
- Configuration personnalisée

## 🚨 Gestion d'Erreurs

### **Erreurs courantes :**
- Membre déjà existant
- Type de réunion non trouvé
- Utilisateur non trouvé
- Rôles invalides
- Contraintes d'unicité violées

### **Codes de réponse :**
- `200` : Succès
- `201` : Création réussie
- `207` : Succès partiel (multi-status)
- `400` : Erreur de validation
- `404` : Ressource non trouvée
- `422` : Données invalides

## 📈 Fonctionnalités Avancées

### **Rôles disponibles :**
- `PRESIDENT` - Président de réunion
- `SECRETAIRE` - Secrétaire de réunion
- `PARTICIPANT` - Participant standard
- `OBSERVATEUR` - Observateur

### **Notifications disponibles :**
- `CONFIRMATION_PRESENCE` - Confirmation de présence
- `RAPPEL_24H` - Rappel 24h avant
- `RAPPEL_1H` - Rappel 1h avant
- `RAPPEL_15MIN` - Rappel 15min avant
- `PV_DISPONIBLE` - PV disponible
- `RAPPEL_ACTIONS` - Rappel actions

### **Statistiques fournies :**
- Nombre total de membres
- Répartition actifs/inactifs
- Répartition par rôles
- Répartition par notifications
- Historique des actions

## ✅ Résumé des Corrections

### **Corrections appliquées :**
1. **Intégration du service** : Utilisation du TypeReunionMembrePermanentService dans le contrôleur
2. **Validation améliorée** : Validation des rôles et notifications
3. **Gestion des erreurs** : Messages d'erreur détaillés
4. **Opérations multiples** : Support pour ajout/suppression multiple
5. **Rôles et notifications** : Gestion des rôles par défaut et notifications personnalisées

### **Structure cohérente :**
- Service et contrôleur alignés
- Routes correctement définies
- Validation des paramètres
- Gestion d'erreurs appropriée

**Le TypeReunionMembrePermanentService est maintenant entièrement fonctionnel et cohérent !** 🎉 
