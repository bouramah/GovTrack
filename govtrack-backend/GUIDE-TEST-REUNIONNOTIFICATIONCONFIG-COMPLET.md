# Guide de Test - ReunionNotificationConfigService

## 📋 Vue d'ensemble

Ce guide détaille les procédures de test complètes pour le service `ReunionNotificationConfigService` qui gère les configurations de notifications pour les réunions.

### 🎯 Objectifs du Service
- Gérer les configurations de notifications par type de réunion
- Configurer différents types de notifications (confirmation, rappel, PV, actions)
- Personnaliser les templates d'emails et les destinataires
- Activer/désactiver les notifications
- Copier des configurations entre types de réunions
- Générer des statistiques d'utilisation

## 🚀 Prérequis

### 1. Environnement de Test
```bash
# Vérifier que le serveur Laravel est démarré
php artisan serve

# Vérifier que la base de données est configurée
php artisan migrate:status

# Vérifier que les seeders sont exécutés
php artisan db:seed --class=TypeReunionSeeder
```

### 2. Données de Test Requises
- Au moins un type de réunion existant dans la base de données
- Un utilisateur authentifié avec les permissions appropriées
- Token d'authentification valide

### 3. Permissions Nécessaires
- `view_reunion_notifications` - Voir les configurations
- `create_reunion_notifications` - Créer des configurations
- `update_reunion_notifications` - Modifier des configurations
- `delete_reunion_notifications` - Supprimer des configurations

## 📦 Configuration Postman

### 1. Import de la Collection
1. Ouvrir Postman
2. Cliquer sur "Import"
3. Sélectionner le fichier : `GovTrack-ReunionNotificationConfigService-Complete.postman_collection.json`

### 2. Configuration de l'Environnement
Créer un nouvel environnement avec les variables suivantes :

| Variable | Valeur | Description |
|----------|--------|-------------|
| `base_url` | `http://localhost:8000` | URL de base de l'API |
| `auth_token` | `[VOTRE_TOKEN]` | Token d'authentification |
| `type_reunion_id` | `1` | ID du type de réunion de test |
| `config_id` | `[AUTO]` | ID de configuration (sauvegardé automatiquement) |

### 3. Obtention du Token d'Authentification
```bash
# Via l'API d'authentification
POST {{base_url}}/api/v1/auth/login
{
  "email": "votre_email@example.com",
  "password": "votre_mot_de_passe"
}
```

## 🔧 Endpoints Disponibles

### 1. Récupération des Configurations

#### GET `/api/v1/notification-configs/{typeReunionId}`
**Description :** Récupérer les configurations d'un type de réunion

**Paramètres de requête :**
- `type_notification` (optionnel) : Filtrer par type de notification
- `actif` (optionnel) : Filtrer par statut actif

**Exemple de réponse :**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type_reunion_id": 1,
      "type_notification": "CONFIRMATION_PRESENCE",
      "actif": true,
      "delai_jours": 2,
      "template_email": "Bonjour {{nom}}, veuillez confirmer...",
      "destinataires_par_defaut": ["PARTICIPANTS", "ORGANISATEUR"],
      "configuration_avancee": {
        "rappel_automatique": true,
        "nombre_rappel": 2
      }
    }
  ],
  "total": 1,
  "filters_applied": {
    "type_notification": "CONFIRMATION_PRESENCE",
    "actif": true
  }
}
```

### 2. Récupération d'une Configuration Spécifique

#### GET `/api/v1/notification-configs/config/{configId}`
**Description :** Récupérer les détails d'une configuration spécifique

**Exemple de réponse :**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "type_reunion_id": 1,
    "type_notification": "CONFIRMATION_PRESENCE",
    "actif": true,
    "delai_jours": 2,
    "template_email": "Bonjour {{nom}}, veuillez confirmer...",
    "destinataires_par_defaut": ["PARTICIPANTS", "ORGANISATEUR"],
    "configuration_avancee": {
      "rappel_automatique": true,
      "nombre_rappel": 2
    },
    "date_creation": "2024-01-15T10:30:00.000000Z",
    "date_modification": "2024-01-15T10:30:00.000000Z"
  }
}
```

### 3. Création de Configuration

#### POST `/api/v1/notification-configs/`
**Description :** Créer une nouvelle configuration de notification

**Payload requis :**
```json
{
  "type_reunion_id": 1,
  "type_notification": "CONFIRMATION_PRESENCE",
  "actif": true,
  "delai_jours": 2,
  "template_email": "Bonjour {{nom}}, veuillez confirmer votre présence à la réunion {{titre}}.",
  "destinataires_par_defaut": ["PARTICIPANTS", "ORGANISATEUR"],
  "configuration_avancee": {
    "rappel_automatique": true,
    "nombre_rappel": 2,
    "intervalle_rappel": 24
  }
}
```

**Types de notifications disponibles :**
- `CONFIRMATION_PRESENCE` - Confirmation de présence
- `RAPPEL` - Rappel de réunion
- `PV_DISPONIBLE` - PV disponible
- `RAPPEL_ACTIONS` - Rappel des actions

**Destinataires disponibles :**
- `PARTICIPANTS` - Participants de la réunion
- `ORGANISATEUR` - Organisateur de la réunion
- `VALIDATEUR` - Validateur du PV
- `ADMINISTRATEUR` - Administrateur système

### 4. Mise à Jour de Configuration

#### PUT `/api/v1/notification-configs/{configId}`
**Description :** Mettre à jour une configuration existante

**Payload (tous les champs optionnels) :**
```json
{
  "type_notification": "RAPPEL",
  "actif": true,
  "delai_jours": 1,
  "template_email": "Rappel : Réunion {{titre}} demain à {{heure}}.",
  "destinataires_par_defaut": ["PARTICIPANTS"],
  "configuration_avancee": {
    "rappel_automatique": true,
    "nombre_rappel": 1
  }
}
```

### 5. Suppression de Configuration

#### DELETE `/api/v1/notification-configs/{configId}`
**Description :** Supprimer une configuration de notification

**Exemple de réponse :**
```json
{
  "success": true,
  "message": "Configuration supprimée avec succès"
}
```

### 6. Activation/Désactivation

#### POST `/api/v1/notification-configs/{configId}/toggle-actif`
**Description :** Activer ou désactiver une configuration

**Payload :**
```json
{
  "actif": false
}
```

### 7. Statistiques

#### GET `/api/v1/notification-configs/stats/{typeReunionId?}`
**Description :** Obtenir les statistiques des configurations

**Exemple de réponse :**
```json
{
  "success": true,
  "data": {
    "total_configs": 5,
    "configs_par_type": {
      "CONFIRMATION_PRESENCE": 2,
      "RAPPEL": 1,
      "PV_DISPONIBLE": 1,
      "RAPPEL_ACTIONS": 1
    },
    "configs_actives": 4,
    "configs_inactives": 1,
    "types_reunion_avec_configs": 3
  }
}
```

### 8. Copie de Configurations

#### POST `/api/v1/notification-configs/copier`
**Description :** Copier les configurations d'un type de réunion vers un autre

**Payload :**
```json
{
  "source_type_reunion_id": 1,
  "destination_type_reunion_id": 2
}
```

**Exemple de réponse :**
```json
{
  "success": true,
  "message": "3 configurations copiées avec succès",
  "data": {
    "configs_copiees": 3,
    "destination_type_reunion_id": 2
  }
}
```

## 🧪 Tests Avancés

### Test 1 : Configuration de Confirmation de Présence
```json
{
  "type_reunion_id": 1,
  "type_notification": "CONFIRMATION_PRESENCE",
  "actif": true,
  "delai_jours": 2,
  "template_email": "Bonjour {{nom}}, veuillez confirmer votre présence à la réunion {{titre}} qui se tiendra le {{date}} à {{heure}} dans {{lieu}}.",
  "destinataires_par_defaut": ["PARTICIPANTS", "ORGANISATEUR"],
  "configuration_avancee": {
    "rappel_automatique": true,
    "nombre_rappel": 2,
    "intervalle_rappel": 24,
    "inclure_ordre_jour": true,
    "inclure_lien_confirmation": true
  }
}
```

### Test 2 : Configuration de Rappel
```json
{
  "type_reunion_id": 1,
  "type_notification": "RAPPEL",
  "actif": true,
  "delai_jours": 1,
  "template_email": "Rappel : Réunion {{titre}} demain à {{heure}} dans {{lieu}}. Ordre du jour : {{ordre_jour}}",
  "destinataires_par_defaut": ["PARTICIPANTS"],
  "configuration_avancee": {
    "rappel_automatique": true,
    "nombre_rappel": 1,
    "inclure_ordre_jour": true,
    "inclure_lien_reunion": true
  }
}
```

### Test 3 : Configuration PV Disponible
```json
{
  "type_reunion_id": 1,
  "type_notification": "PV_DISPONIBLE",
  "actif": true,
  "delai_jours": 0,
  "template_email": "Le procès-verbal de la réunion {{titre}} est maintenant disponible. Vous pouvez le consulter via le lien suivant : {{lien_pv}}",
  "destinataires_par_defaut": ["PARTICIPANTS", "ORGANISATEUR", "VALIDATEUR"],
  "configuration_avancee": {
    "inclure_lien_pv": true,
    "inclure_resume": true,
    "notifier_actions": true,
    "inclure_actions_assignees": true
  }
}
```

## ✅ Validation des Réponses

### Structure de Réponse Standard
```json
{
  "success": true|false,
  "message": "Message descriptif",
  "data": {...},
  "errors": {...} // En cas d'erreur
}
```

### Codes de Statut HTTP
- `200` - Succès (GET, PUT, DELETE)
- `201` - Créé avec succès (POST)
- `204` - Supprimé avec succès (DELETE)
- `400` - Requête invalide
- `401` - Non authentifié
- `403` - Non autorisé
- `404` - Ressource non trouvée
- `422` - Erreur de validation
- `500` - Erreur serveur

### Validation des Données
- Vérifier que tous les champs requis sont présents
- Valider les types de données (entiers, booléens, tableaux)
- Contrôler les valeurs ENUM pour `type_notification`
- Vérifier la cohérence des `destinataires_par_defaut`

## 🚨 Gestion des Erreurs

### Erreurs de Validation
```json
{
  "success": false,
  "message": "Données de validation invalides",
  "errors": {
    "type_reunion_id": ["Le type de réunion sélectionné est invalide."],
    "type_notification": ["Le type de notification sélectionné est invalide."],
    "template_email": ["Le template d'email est requis."]
  }
}
```

### Erreurs d'Authentification
```json
{
  "success": false,
  "message": "Token d'authentification manquant, invalide ou expiré",
  "error": "Unauthenticated"
}
```

### Erreurs de Permission
```json
{
  "success": false,
  "message": "Vous n'avez pas les permissions nécessaires",
  "error": "Forbidden"
}
```

## 📊 Tests de Performance

### Test de Charge
1. **Création en lot :** Créer 10 configurations simultanément
2. **Récupération avec filtres :** Tester avec différents filtres
3. **Copie de configurations :** Copier vers un type de réunion avec beaucoup de configurations existantes

### Métriques à Surveiller
- Temps de réponse < 500ms pour les opérations CRUD
- Temps de réponse < 1s pour les statistiques
- Utilisation mémoire < 100MB
- Pas d'erreurs 500

## 🔍 Dépannage

### Problèmes Courants

#### 1. Erreur 404 - Configuration non trouvée
**Cause :** ID de configuration invalide
**Solution :** Vérifier l'existence de la configuration dans la base de données

#### 2. Erreur 422 - Validation échouée
**Cause :** Données invalides dans le payload
**Solution :** Vérifier le format des données et les valeurs ENUM

#### 3. Erreur 403 - Permission refusée
**Cause :** Utilisateur sans permissions appropriées
**Solution :** Vérifier les permissions de l'utilisateur

#### 4. Erreur 500 - Erreur serveur
**Cause :** Problème dans le service ou la base de données
**Solution :** Vérifier les logs Laravel (`storage/logs/laravel.log`)

### Logs à Surveiller
```bash
# Logs d'application
tail -f storage/logs/laravel.log | grep "ReunionNotificationConfig"

# Logs de base de données
tail -f storage/logs/laravel.log | grep "SQL"
```

## 📋 Checklist de Validation

### ✅ Fonctionnalités de Base
- [ ] Création de configuration avec tous les types de notifications
- [ ] Récupération de configurations avec filtres
- [ ] Mise à jour de configuration partielle
- [ ] Suppression de configuration
- [ ] Activation/désactivation de configuration

### ✅ Fonctionnalités Avancées
- [ ] Copie de configurations entre types de réunions
- [ ] Statistiques par type de réunion
- [ ] Statistiques globales
- [ ] Filtres avancés (type, statut)

### ✅ Validation des Données
- [ ] Validation des types ENUM
- [ ] Validation des champs requis
- [ ] Validation des formats de données
- [ ] Gestion des valeurs par défaut

### ✅ Sécurité et Permissions
- [ ] Authentification requise
- [ ] Permissions appropriées
- [ ] Validation des données utilisateur
- [ ] Protection contre les injections

### ✅ Performance
- [ ] Temps de réponse acceptable
- [ ] Pas de fuites mémoire
- [ ] Gestion des erreurs
- [ ] Logs appropriés

## 🎯 Scénarios de Test Recommandés

### Scénario 1 : Workflow Complet
1. Créer une configuration de confirmation
2. Créer une configuration de rappel
3. Créer une configuration PV
4. Récupérer toutes les configurations
5. Modifier une configuration
6. Activer/désactiver une configuration
7. Obtenir les statistiques
8. Supprimer une configuration

### Scénario 2 : Copie de Configurations
1. Créer plusieurs configurations pour un type de réunion
2. Copier vers un autre type de réunion
3. Vérifier que toutes les configurations sont copiées
4. Modifier une configuration copiée
5. Vérifier l'indépendance des configurations

### Scénario 3 : Filtres et Recherche
1. Créer des configurations avec différents types
2. Tester tous les filtres disponibles
3. Vérifier les résultats de recherche
4. Tester les combinaisons de filtres

## 📞 Support

En cas de problème ou de question :
1. Vérifier les logs Laravel
2. Consulter la documentation de l'API
3. Tester avec Postman
4. Contacter l'équipe de développement

---

**Note :** Ce guide doit être mis à jour à chaque modification du service pour maintenir sa pertinence et son exactitude. 
