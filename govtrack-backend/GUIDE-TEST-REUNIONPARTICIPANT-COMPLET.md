# Guide de Test - ReunionParticipantService

## 📋 Vue d'ensemble

Ce guide détaille l'exécution complète des tests pour le service `ReunionParticipantService` qui gère les participants aux réunions.

## 🔧 Configuration requise

### Variables d'environnement Postman

```json
{
  "base_url": "http://localhost:8000",
  "token": "VOTRE_TOKEN_JWT",
  "reunion_test_id": "1",
  "participant_test_id": "",
  "participant_created_id": ""
}
```

### Prérequis

1. **Base de données** : Avoir une réunion existante avec l'ID spécifié dans `reunion_test_id`
2. **Authentification** : Token JWT valide avec les permissions appropriées
3. **Utilisateurs** : Avoir des utilisateurs avec les IDs 2, 3, 4, 5, 6 dans la base de données

## 🧪 Tests détaillés

### **1. Lister les participants d'une réunion**
- **URL :** `GET {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants`
- **Objectif :** Récupérer tous les participants d'une réunion spécifique
- **Tests :** Vérification du statut 200, structure de réponse, et récupération automatique de l'ID de test
- **Permissions :** `view_reunions`

### **2. Ajouter un participant**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants`
- **Payload :**
```json
{
    "user_id": 2,
    "role": "PARTICIPANT",
    "type": "INVITE",
    "statut_presence": "EN_ATTENTE",
    "notifications_actives": {
        "CONFIRMATION_PRESENCE": true,
        "RAPPEL_24H": true,
        "RAPPEL_1H": false
    }
}
```
- **Objectif :** Ajouter un nouveau participant à la réunion
- **Tests :** Vérification du statut 201, création réussie, et sauvegarde de l'ID
- **Permissions :** `update_reunions`

### **3. Ajouter plusieurs participants**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants/multiple`
- **Payload :**
```json
{
    "participants": [
        {
            "user_id": 3,
            "role": "SECRETAIRE",
            "type": "PERMANENT",
            "statut_presence": "CONFIRME",
            "notifications_actives": {
                "CONFIRMATION_PRESENCE": true,
                "RAPPEL_24H": true,
                "RAPPEL_1H": true
            }
        },
        {
            "user_id": 4,
            "role": "OBSERVATEUR",
            "type": "INVITE",
            "statut_presence": "EN_ATTENTE",
            "notifications_actives": {
                "CONFIRMATION_PRESENCE": false,
                "RAPPEL_24H": true
            }
        }
    ]
}
```
- **Objectif :** Ajouter plusieurs participants en une seule requête
- **Tests :** Vérification de la création de multiples participants
- **Permissions :** `update_reunions`

### **4. Mettre à jour un participant**
- **URL :** `PUT {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants/{{participant_created_id}}`
- **Payload :**
```json
{
    "role": "PRESIDENT",
    "type": "PERMANENT",
    "statut_presence": "CONFIRME",
    "notifications_actives": {
        "CONFIRMATION_PRESENCE": true,
        "RAPPEL_24H": true,
        "RAPPEL_1H": true,
        "RAPPEL_15MIN": true
    }
}
```
- **Objectif :** Modifier les informations d'un participant existant
- **Tests :** Vérification de la mise à jour du rôle vers PRESIDENT
- **Permissions :** `update_reunions`

### **5. Mettre à jour le statut de présence**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants/{{participant_created_id}}/presence`
- **Payload :**
```json
{
    "statut": "ABSENT",
    "commentaire_absence": "Participant indisponible pour cette réunion"
}
```
- **Objectif :** Changer le statut de présence d'un participant
- **Tests :** Vérification de la mise à jour vers ABSENT
- **Permissions :** `update_reunions`

### **6. Obtenir les statistiques des participants**
- **URL :** `GET {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants/stats`
- **Objectif :** Récupérer les statistiques des participants d'une réunion
- **Tests :** Vérification de la structure des statistiques
- **Permissions :** `view_reunions`

### **7. Test de validation - User ID manquant**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants`
- **Payload :**
```json
{
    "role": "PARTICIPANT",
    "type": "INVITE"
}
```
- **Objectif :** Tester la validation du champ `user_id` obligatoire
- **Tests :** Vérification du statut 422 et des erreurs de validation

### **8. Test de validation - Rôle invalide**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants`
- **Payload :**
```json
{
    "user_id": 5,
    "role": "INVALID_ROLE",
    "type": "INVITE"
}
```
- **Objectif :** Tester la validation des valeurs ENUM pour le rôle
- **Tests :** Vérification du statut 422 et des erreurs de validation

### **9. Test de validation - Statut de présence invalide**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants`
- **Payload :**
```json
{
    "user_id": 6,
    "role": "PARTICIPANT",
    "type": "INVITE",
    "statut_presence": "INVALID_STATUS"
}
```
- **Objectif :** Tester la validation des valeurs ENUM pour le statut de présence
- **Tests :** Vérification du statut 422 et des erreurs de validation

### **10. Supprimer un participant**
- **URL :** `DELETE {{base_url}}/api/v1/reunions/{{reunion_test_id}}/participants/{{participant_created_id}}`
- **Objectif :** Supprimer un participant créé pendant les tests
- **Tests :** Vérification de la suppression réussie
- **Permissions :** `update_reunions`

## 🔍 Corrections appliquées

### **1. Modèle ReunionParticipant.php**

#### **Rôles corrigés :**
```php
// Avant
const ROLE_INVITE = 'INVITE';

// Après
const ROLE_VALIDATEUR_PV = 'VALIDATEUR_PV';
```

#### **Types corrigés :**
```php
// Avant
const TYPE_INTERNE = 'INTERNE';
const TYPE_EXTERNE = 'EXTERNE';

// Après
const TYPE_PERMANENT = 'PERMANENT';
const TYPE_INVITE = 'INVITE';
```

#### **Statuts de présence corrigés :**
```php
// Avant
const STATUT_PRESENCE_INVITE = 'INVITE';
const STATUT_PRESENCE_REFUSE = 'REFUSE';
const STATUT_PRESENCE_PRESENT = 'PRESENT';

// Après
const STATUT_PRESENCE_CONFIRME = 'CONFIRME';
const STATUT_PRESENCE_ABSENT = 'ABSENT';
const STATUT_PRESENCE_EN_ATTENTE = 'EN_ATTENTE';
```

### **2. Routes api.php**

#### **Cohérence des paramètres :**
```php
// Avant (incohérent)
Route::get('{id}/participants', ...);
Route::post('{id}/participants', ...);
Route::put('{reunionId}/participants/{participantId}', ...);

// Après (cohérent)
Route::get('{reunionId}/participants', ...);
Route::post('{reunionId}/participants', ...);
Route::put('{reunionId}/participants/{participantId}', ...);
```

### **3. Contrôleur ReunionController.php**

#### **Signatures des méthodes corrigées :**
```php
// Avant
public function participants(Request $request, int $id): JsonResponse
public function addParticipant(Request $request, int $id): JsonResponse

// Après
public function participants(Request $request, int $reunionId): JsonResponse
public function addParticipant(Request $request, int $reunionId): JsonResponse
```

## 📊 Valeurs ENUM finales

### **Rôles :**
- `PRESIDENT` - Président
- `SECRETAIRE` - Secrétaire
- `PARTICIPANT` - Participant
- `OBSERVATEUR` - Observateur
- `VALIDATEUR_PV` - Validateur PV

### **Types :**
- `PERMANENT` - Permanent
- `INVITE` - Invité

### **Statuts de présence :**
- `CONFIRME` - Confirmé
- `ABSENT` - Absent
- `EN_ATTENTE` - En attente

## ✅ État final

| **Migration** | **Modèle** | **Service** | **Contrôleur** |
|---------------|------------|-------------|----------------|
| `PRESIDENT` | ✅ | ✅ | ✅ |
| `SECRETAIRE` | ✅ | ✅ | ✅ |
| `PARTICIPANT` | ✅ | ✅ | ✅ |
| `OBSERVATEUR` | ✅ | ✅ | ✅ |
| `VALIDATEUR_PV` | ✅ | ✅ | ✅ |
| `PERMANENT` | ✅ | ✅ | ✅ |
| `INVITE` | ✅ | ✅ | ✅ |
| `CONFIRME` | ✅ | ✅ | ✅ |
| `ABSENT` | ✅ | ✅ | ✅ |
| `EN_ATTENTE` | ✅ | ✅ | ✅ |

## 🚀 Exécution

1. **Importer** la collection Postman
2. **Configurer** les variables d'environnement
3. **Exécuter** les tests dans l'ordre
4. **Vérifier** que tous les tests passent

**Le ReunionParticipantService est maintenant cohérent et prêt pour la production !** 🎉 
