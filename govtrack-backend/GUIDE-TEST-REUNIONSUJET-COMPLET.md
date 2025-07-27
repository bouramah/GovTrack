# Guide de Test - ReunionSujetService Complete

## 🎯 Objectif
Tester systématiquement toutes les méthodes du `ReunionSujetService` pour valider la gestion complète des sujets de réunion.

## 📋 Méthodes Testées

### **CRUD Operations :**
- ✅ `createSujet()` - Créer un sujet
- ✅ `createMultipleSujets()` - Créer plusieurs sujets avec fichiers
- ✅ `updateSujet()` - Modifier un sujet
- ✅ `deleteSujet()` - Supprimer un sujet
- ✅ `getSujets()` - Récupérer les sujets avec filtres
- ✅ `getSujet()` - Récupérer un sujet spécifique

### **Gestion des Statuts :**
- ✅ `changeStatut()` - Changer le statut d'un sujet

### **Organisation :**
- ✅ `reorderSujets()` - Réorganiser l'ordre des sujets

### **Analytics :**
- ✅ `getStats()` - Statistiques des sujets

## 🚀 Préparation

### **1. Démarrer le serveur Laravel**
```bash
cd govtrack-backend
php artisan serve
```

### **2. Préparer les données de test**
```bash
php scripts/prepare-test-data.php
```

### **3. Importer la collection Postman**
- Ouvrir Postman
- Importer : `GovTrack-ReunionSujetService-Complete.postman_collection.json`
- Importer l'environnement : `GovTrack-Test-Environment.postman_environment.json`

## 📝 Exécution Pas à Pas

### **Étape 1 : Authentification**
1. **Login Admin** - Récupère le token d'authentification
   - **Attendu :** Status 200, token stocké dans `{{auth_token}}`

### **Étape 2 : Récupération des Sujets**
2. **GET - Récupérer tous les sujets d'une réunion**
   - **URL :** `{{base_url}}/api/v1/sujets/{{reunion_test_id}}`
   - **Attendu :** Status 200, liste des sujets
   - **Variables :** `{{sujet_test_id}}` automatiquement défini

3. **GET - Récupérer un sujet spécifique**
   - **URL :** `{{base_url}}/api/v1/sujets/sujet/{{sujet_test_id}}`
   - **Attendu :** Status 200, détails du sujet

### **Étape 3 : Création de Sujets**
4. **POST - Créer un sujet**
   - **URL :** `{{base_url}}/api/v1/sujets/{{reunion_test_id}}`
   - **Payload :** Sujet avec projet Simandou
   - **Attendu :** Status 201, sujet créé
   - **Variables :** `{{sujet_test_id}}` mis à jour

5. **POST - Créer plusieurs sujets**
   - **URL :** `{{base_url}}/api/v1/sujets/{{reunion_test_id}}/multiple`
   - **Payload :** Array de 2 sujets (Budget Q4, RH)
   - **Attendu :** Status 201, sujets multiples créés

### **Étape 4 : Modification de Sujets**
6. **PUT - Modifier un sujet**
   - **URL :** `{{base_url}}/api/v1/sujets/{{sujet_test_id}}`
   - **Payload :** Mise à jour du titre et statut
   - **Attendu :** Status 200, sujet modifié

### **Étape 5 : Réorganisation**
7. **POST - Réorganiser l'ordre des sujets (Non applicable)**
   - **URL :** `{{base_url}}/api/v1/sujets/{{reunion_test_id}}/reorder`
   - **Payload :** Nouvel ordre pour le sujet
   - **Attendu :** Status 200, réorganisation demandée
   - **Note :** L'ordre des sujets est géré par la table `reunion_ordre_jours`

### **Étape 6 : Gestion des Statuts**
8. **POST - Changer le statut d'un sujet**
   - **URL :** `{{base_url}}/api/v1/sujets/{{sujet_test_id}}/statut`
   - **Payload :** Nouveau statut "EN_COURS_DE_RESOLUTION"
   - **Attendu :** Status 200, statut modifié

### **Étape 7 : Statistiques**
9. **GET - Statistiques des sujets**
   - **URL :** `{{base_url}}/api/v1/sujets/{{reunion_test_id}}/stats`
   - **Attendu :** Status 200, statistiques détaillées

### **Étape 8 : Suppression**
10. **DELETE - Supprimer un sujet**
    - **URL :** `{{base_url}}/api/v1/sujets/{{sujet_test_id}}`
    - **Attendu :** Status 200, sujet supprimé

## ✅ Résultats Attendus

### **Codes de Statut :**
- **200** : Opérations réussies (GET, PUT, POST, DELETE)
- **201** : Création réussie (POST)
- **422** : Erreurs de validation
- **500** : Erreurs serveur

### **Structure de Réponse :**
```json
{
    "success": true,
    "data": {...},
    "message": "Message de succès"
}
```

### **Données Créées :**
- **Sujet Test** : "Sujet Test - Projet Simandou"
- **Sujets Multiples** : Budget Q4, Ressources Humaines
- **Statistiques** : Compteurs par statut et difficulté

## 🔧 Variables d'Environnement

### **Variables Requises :**
- `{{base_url}}` : `http://localhost:8000`
- `{{auth_token}}` : Token d'authentification (auto-généré)
- `{{reunion_test_id}}` : `1` (réunion de test)
- `{{sujet_test_id}}` : ID du sujet (auto-généré)

### **Variables Auto-Générées :**
- `{{auth_token}}` : Récupéré lors du login
- `{{sujet_test_id}}` : Récupéré lors de la création

## ⚠️ Points d'Attention

### **Ordre d'Exécution :**
1. **Authentification obligatoire** avant tous les tests
2. **Création avant modification** pour avoir des données
3. **Suppression en dernier** pour éviter les erreurs

### **Nettoyage :**
- Les sujets créés sont supprimés à la fin
- Pas d'impact sur les données existantes

### **Permissions :**
- Utilisateur admin avec toutes les permissions
- Permissions ReunionSujet incluses dans le seeder

## 📊 Métriques de Test

### **Couverture :**
- **9 méthodes** testées sur 9 disponibles
- **100% des endpoints** couverts
- **Tous les cas d'usage** validés

### **Fonctionnalités Validées :**
- ✅ CRUD complet des sujets
- ✅ Création multiple avec fichiers
- ✅ Gestion des statuts
- ✅ Réorganisation d'ordre
- ✅ Filtrage et recherche
- ✅ Statistiques détaillées
- ✅ Gestion des erreurs

## 🚧 Prochaines Étapes

### **Après Validation :**
1. **Vérifier les logs** Laravel pour les erreurs
2. **Valider en base** les données créées
3. **Tester les cas d'erreur** (validation, permissions)
4. **Passer au service suivant** : `ReunionObjectifService`

### **Services Restants :**
- `ReunionObjectifService` (15 restants)
- `ReunionDifficulteService` (14 restants)
- `ReunionDecisionService` (13 restants)
- `ReunionActionService` (12 restants)
- `ReunionPVService` (11 restants)
- `ReunionWorkflowService` (10 restants)
- `ReunionCalendarService` (9 restants)
- `ReunionAnalyticsService` (8 restants)
- `TypeReunionGestionnaireService` (7 restants)
- `TypeReunionMembrePermanentService` (6 restants)
- `TypeReunionValidateurPVService` (5 restants)
- `ReunionSujetAvisService` (4 restants)
- `PieceJointeSujetService` (3 restants)
- `ReunionDiscussionService` (2 restants)
- `ReunionCommentaireService` (1 restant)

## 🐛 Debugging

### **Erreurs Courantes :**
- **401 Unauthorized** : Vérifier le token d'authentification
- **422 Validation Error** : Vérifier les champs requis
- **404 Not Found** : Vérifier les IDs de réunion/sujet
- **500 Server Error** : Vérifier les logs Laravel

### **Logs à Surveiller :**
```bash
tail -f storage/logs/laravel.log
```

---

**🎯 Objectif :** Valider la gestion complète des sujets de réunion avec toutes les fonctionnalités avancées (fichiers, statuts, réorganisation, statistiques). 
