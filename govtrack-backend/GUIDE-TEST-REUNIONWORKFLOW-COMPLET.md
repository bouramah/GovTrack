# Guide de Test - ReunionWorkflowService

## 📋 Vue d'ensemble

Ce guide détaille comment tester le service `ReunionWorkflowService` qui gère les workflows de validation pour les réunions. Le service permet de créer, configurer et exécuter des workflows d'approbation multi-étapes.

## 🔧 Prérequis

### 1. **Environnement de Test**
- Serveur Laravel en cours d'exécution (`php artisan serve`)
- Base de données configurée avec les migrations appliquées
- Utilisateur authentifié avec les permissions appropriées

### 2. **Permissions Requises**
- `view_reunion_workflows` - Consulter les workflows
- `create_reunion_workflow` - Créer des workflows
- `start_reunion_workflow` - Démarrer des workflows
- `validate_reunion_workflow` - Valider/rejeter des étapes
- `cancel_reunion_workflow` - Annuler des workflows

### 3. **Données de Test**
- Type de réunion existant (`type_reunion_id`)
- Réunion existante (`reunion_id`)
- Utilisateur avec permissions (`user_id`)

## 🚀 Configuration Postman

### 1. **Import de la Collection**
1. Ouvrir Postman
2. Importer le fichier `GovTrack-ReunionWorkflowService-Complete.postman_collection.json`
3. Créer un environnement avec les variables suivantes :

### 2. **Variables d'Environnement**
```json
{
  "base_url": "http://localhost:8000",
  "auth_token": "VOTRE_TOKEN_JWT",
  "type_reunion_id": "1",
  "reunion_id": "1",
  "user_id": "1",
  "workflow_config_id": "",
  "execution_id": ""
}
```

### 3. **Authentification**
- Obtenir un token JWT via l'endpoint de login
- Définir la variable `auth_token` dans l'environnement Postman

## 📝 Tests par Endpoint

### **1. Récupérer les workflows configurés**
**Endpoint:** `GET /api/v1/workflows/configs/{typeReunionId}`

**Description:** Récupère tous les workflows configurés pour un type de réunion spécifique.

**Variables utilisées:**
- `{{type_reunion_id}}` - ID du type de réunion

**Réponse attendue:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type_reunion_id": 1,
      "nom_workflow": "Validation Comité Exécutif",
      "etapes": [...],
      "actif": true,
      "obligatoire": true,
      "configuration": {...}
    }
  ],
  "message": "Workflows récupérés avec succès"
}
```

### **2. Créer un workflow de validation**
**Endpoint:** `POST /api/v1/workflows/configs`

**Description:** Crée un nouveau workflow de validation avec plusieurs étapes.

**Payload:**
```json
{
  "type_reunion_id": 1,
  "nom_workflow": "Validation Comité Exécutif",
  "etapes": [
    {
      "nom": "Validation Directeur Général",
      "validateur_id": 1,
      "ordre": 1,
      "notifier_validateur": true
    },
    {
      "nom": "Validation Président",
      "validateur_id": 1,
      "ordre": 2,
      "notifier_validateur": true
    }
  ],
  "actif": true,
  "obligatoire": true,
  "configuration": {
    "delai_max_etape": 7,
    "notifications": {
      "rappel_automatique": true,
      "delai_rappel": 3
    }
  }
}
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "type_reunion_id": 1,
    "nom_workflow": "Validation Comité Exécutif",
    "etapes": [...],
    "actif": true,
    "obligatoire": true,
    "configuration": {...}
  },
  "message": "Workflow créé avec succès"
}
```

**Note:** L'ID du workflow créé sera automatiquement sauvegardé dans `{{workflow_config_id}}`.

### **3. Démarrer un workflow pour une réunion**
**Endpoint:** `POST /api/v1/workflows/start/{reunionId}`

**Description:** Démarre l'exécution d'un workflow pour une réunion spécifique.

**Variables utilisées:**
- `{{reunion_id}}` - ID de la réunion
- `{{workflow_config_id}}` - ID du workflow configuré

**Payload:**
```json
{
  "workflow_config_id": 1
}
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "reunion_id": 1,
    "workflow_config_id": 1,
    "etape_actuelle": 1,
    "statut_global": "EN_COURS",
    "date_debut": "2025-01-15T10:00:00.000000Z",
    "historique_etapes": []
  },
  "message": "Workflow démarré avec succès"
}
```

**Note:** L'ID de l'exécution sera automatiquement sauvegardé dans `{{execution_id}}`.

### **4. Valider une étape du workflow**
**Endpoint:** `POST /api/v1/workflows/validate/{executionId}`

**Description:** Valide une étape spécifique du workflow en cours.

**Variables utilisées:**
- `{{execution_id}}` - ID de l'exécution du workflow

**Payload:**
```json
{
  "etape": 1,
  "commentaire": "Validation approuvée après révision du document"
}
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "etape_actuelle": 2,
    "statut_global": "EN_COURS",
    "historique_etapes": [
      {
        "etape": 1,
        "validateur": 1,
        "statut": "VALIDE",
        "date": "2025-01-15T10:30:00.000000Z",
        "commentaire": "Validation approuvée après révision du document"
      }
    ]
  },
  "message": "Étape validée avec succès"
}
```

### **5. Rejeter une étape du workflow**
**Endpoint:** `POST /api/v1/workflows/reject/{executionId}`

**Description:** Rejette une étape du workflow avec une raison.

**Payload:**
```json
{
  "etape": 1,
  "raison": "Document incomplet, informations manquantes sur le budget"
}
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "statut_global": "BLOQUE",
    "historique_etapes": [
      {
        "etape": 1,
        "validateur": 1,
        "statut": "REJETE",
        "date": "2025-01-15T11:00:00.000000Z",
        "commentaire": "Document incomplet, informations manquantes sur le budget"
      }
    ]
  },
  "message": "Étape rejetée avec succès"
}
```

### **6. Obtenir les workflows en cours**
**Endpoint:** `GET /api/v1/workflows/en-cours`

**Description:** Récupère tous les workflows en cours pour l'utilisateur connecté.

**Réponse attendue:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "reunion": {...},
      "workflowConfig": {
        "id": 1,
        "nom_workflow": "Validation Comité Exécutif",
        "typeReunion": {...}
      },
      "etape_actuelle": 2,
      "statut_global": "EN_COURS"
    }
  ],
  "message": "Workflows en cours récupérés avec succès"
}
```

### **7. Obtenir les détails d'une exécution**
**Endpoint:** `GET /api/v1/workflows/execution/{executionId}`

**Description:** Récupère les détails complets d'une exécution de workflow.

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "reunion": {...},
    "workflowConfig": {
      "id": 1,
      "nom_workflow": "Validation Comité Exécutif",
      "etapes": [...],
      "typeReunion": {...}
    },
    "etape_actuelle": 2,
    "statut_global": "EN_COURS",
    "date_debut": "2025-01-15T10:00:00.000000Z",
    "historique_etapes": [...]
  },
  "message": "Exécution de workflow récupérée avec succès"
}
```

### **8. Annuler un workflow en cours**
**Endpoint:** `POST /api/v1/workflows/cancel/{executionId}`

**Description:** Annule un workflow en cours avec une raison.

**Payload:**
```json
{
  "raison": "Réunion annulée par le directeur général"
}
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "statut_global": "ANNULE",
    "historique_etapes": [
      {
        "etape": 2,
        "validateur": 1,
        "statut": "ANNULE",
        "date": "2025-01-15T12:00:00.000000Z",
        "commentaire": "Réunion annulée par le directeur général"
      }
    ]
  },
  "message": "Workflow annulé avec succès"
}
```

## 🧪 Tests Avancés

### **9. Test - Workflow avec étapes multiples**
Teste la création d'un workflow complexe avec 4 étapes de validation différentes.

### **10. Test - Workflow avec configuration avancée**
Teste la création d'un workflow avec configuration avancée incluant des conditions spécifiques et des intégrations.

## 🔍 Validation des Données

### **Structure des Étapes**
Chaque étape doit contenir :
- `nom` (string) - Nom de l'étape
- `validateur_id` (integer) - ID de l'utilisateur validateur
- `ordre` (integer) - Ordre de l'étape (1, 2, 3...)
- `notifier_validateur` (boolean) - Notification automatique

### **Configuration Avancée**
La configuration peut inclure :
- `delai_max_etape` - Délai maximum par étape
- `notifications` - Configuration des notifications
- `conditions` - Conditions de validation
- `integrations` - Intégrations externes

## ⚠️ Cas d'Erreur

### **Erreurs de Validation**
- `422` - Données invalides (champs manquants ou incorrects)
- `400` - Erreur métier (workflow déjà en cours, permissions insuffisantes)
- `404` - Ressource non trouvée
- `500` - Erreur serveur

### **Messages d'Erreur Courants**
- "Vous n'avez pas les permissions pour créer un workflow"
- "Un workflow est déjà en cours pour cette réunion"
- "Cette étape ne peut pas être validée actuellement"
- "Seuls les workflows en cours peuvent être annulés"

## 📊 Tests de Performance

### **Scénarios de Test**
1. **Création de workflow** - Mesurer le temps de création
2. **Validation d'étapes** - Tester la validation séquentielle
3. **Workflows multiples** - Tester plusieurs workflows simultanés
4. **Historique volumineux** - Tester avec beaucoup d'étapes

### **Métriques à Surveiller**
- Temps de réponse des endpoints
- Utilisation mémoire lors de la création de workflows
- Performance des requêtes avec historique volumineux

## 🔧 Dépannage

### **Problèmes Courants**

1. **Token d'authentification expiré**
   - Solution: Renouveler le token via l'endpoint de login

2. **Permissions insuffisantes**
   - Solution: Vérifier les permissions de l'utilisateur

3. **Workflow déjà en cours**
   - Solution: Terminer ou annuler le workflow existant

4. **Étape non valide**
   - Solution: Vérifier que l'étape actuelle correspond à celle à valider

### **Logs de Debug**
Les erreurs sont loggées avec les informations suivantes :
- ID de l'utilisateur
- ID de la ressource concernée
- Message d'erreur détaillé
- Timestamp de l'erreur

## ✅ Checklist de Validation

- [ ] Tous les endpoints répondent correctement
- [ ] Les validations fonctionnent pour les données invalides
- [ ] Les permissions sont respectées
- [ ] Les workflows se terminent correctement
- [ ] L'historique est correctement mis à jour
- [ ] Les notifications sont déclenchées (si implémentées)
- [ ] Les cas d'erreur sont gérés proprement
- [ ] Les performances sont acceptables

## 📈 Améliorations Futures

1. **Notifications automatiques** - Implémenter les notifications email/SMS
2. **Workflows conditionnels** - Ajouter des conditions d'exécution
3. **Parallélisation** - Permettre des étapes en parallèle
4. **Templates** - Créer des templates de workflows réutilisables
5. **Reporting** - Ajouter des rapports de performance des workflows 
