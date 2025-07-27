# 🎯 Guide de Test Complet - ReunionService

## **📋 Objectif**
Tester systématiquement tous les endpoints du **ReunionService** pour s'assurer qu'ils fonctionnent correctement avec les données de test.

---

## **🔧 Méthodes Testées**

### **ReunionService.php**
- ✅ `getReunions()` - Liste avec filtres avancés
- ✅ `getReunion()` - Récupération par ID
- ✅ `createReunion()` - Création de réunion
- ✅ `updateReunion()` - Modification de réunion
- ✅ `deleteReunion()` - Suppression de réunion
- ✅ `changeStatut()` - Changement de statut
- ✅ `getStats()` - Statistiques globales

### **ReunionParticipantService.php** (intégré)
- ✅ `getParticipants()` - Liste des participants
- ✅ `addParticipant()` - Ajout d'un participant
- ✅ `addMultipleParticipants()` - Ajout multiple
- ✅ `updateParticipant()` - Modification participant
- ✅ `updatePresenceStatus()` - Statut de présence
- ✅ `removeParticipant()` - Suppression participant
- ✅ `getStats()` - Statistiques participants

---

## **🚀 Préparation**

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
- Importer `GovTrack-ReunionService-Complete.postman_collection.json`
- Importer `GovTrack-Test-Environment.postman_environment.json`

---

## **📝 Exécution Pas à Pas**

### **Configuration Initiale**
1. **Login Admin** - Connecte l'administrateur et sauvegarde le token
2. **Login User** - Connecte l'utilisateur et sauvegarde le token

### **Tests Principaux (14 étapes)**

#### **1. Liste des réunions (avec filtres)**
- **URL :** `GET /api/v1/reunions?search=CODIR&statut=PLANIFIEE&type_reunion_id=1&date_debut=2024-01-01&date_fin=2024-12-31&sort_by=date_debut&sort_order=asc&per_page=10`
- **Attendu :** Code 200, liste des réunions avec filtres appliqués
- **Validation :** ✅ Réponse contient `success: true`

#### **2. Statistiques globales**
- **URL :** `GET /api/v1/reunions/stats`
- **Attendu :** Code 200, statistiques des réunions
- **Validation :** ✅ Réponse contient `success: true`

#### **3. Créer une réunion**
- **URL :** `POST /api/v1/reunions`
- **Payload :** Réunion CODIR Test avec tous les champs requis
- **Attendu :** Code 200/201, réunion créée
- **Validation :** ✅ Réponse contient `success: true` et `data.id`
- **Variable :** `reunion_test_id` sauvegardé

#### **4. Récupérer une réunion par ID**
- **URL :** `GET /api/v1/reunions/{{reunion_test_id}}`
- **Attendu :** Code 200, détails de la réunion
- **Validation :** ✅ Réponse contient `success: true`

#### **5. Modifier une réunion**
- **URL :** `PUT /api/v1/reunions/{{reunion_test_id}}`
- **Payload :** Modification du titre, lieu, quorum
- **Attendu :** Code 200, réunion modifiée
- **Validation :** ✅ Réponse contient `success: true`

#### **6. Changer le statut d'une réunion**
- **URL :** `POST /api/v1/reunions/{{reunion_test_id}}/changer-statut`
- **Payload :** `{"nouveau_statut": "EN_COURS", "commentaire": "Réunion démarrée"}`
- **Attendu :** Code 200, statut changé
- **Validation :** ✅ Réponse contient `success: true`

#### **7. Liste des participants**
- **URL :** `GET /api/v1/reunions/{{reunion_test_id}}/participants`
- **Attendu :** Code 200, liste des participants
- **Validation :** ✅ Réponse contient `success: true`

#### **8. Ajouter un participant**
- **URL :** `POST /api/v1/reunions/{{reunion_test_id}}/participants`
- **Payload :** Participant avec rôle, type, notifications
- **Attendu :** Code 200/201, participant ajouté
- **Validation :** ✅ Réponse contient `success: true` et `data.id`
- **Variable :** `participant_test_id` sauvegardé

#### **9. Ajouter plusieurs participants**
- **URL :** `POST /api/v1/reunions/{{reunion_test_id}}/participants/multiple`
- **Payload :** Array de 2 participants avec rôles différents
- **Attendu :** Code 200/201, participants ajoutés
- **Validation :** ✅ Réponse contient `success: true`

#### **10. Modifier un participant**
- **URL :** `PUT /api/v1/reunions/{{reunion_test_id}}/participants/{{participant_test_id}}`
- **Payload :** Modification du rôle et notifications
- **Attendu :** Code 200, participant modifié
- **Validation :** ✅ Réponse contient `success: true`

#### **11. Mettre à jour le statut de présence**
- **URL :** `POST /api/v1/reunions/{{reunion_test_id}}/participants/{{participant_test_id}}/presence`
- **Payload :** `{"statut_presence": "PRESENT", "commentaire": "Présent à la réunion"}`
- **Attendu :** Code 200, statut mis à jour
- **Validation :** ✅ Réponse contient `success: true`

#### **12. Statistiques des participants**
- **URL :** `GET /api/v1/reunions/{{reunion_test_id}}/participants/stats`
- **Attendu :** Code 200, statistiques des participants
- **Validation :** ✅ Réponse contient `success: true`

#### **13. Supprimer un participant**
- **URL :** `DELETE /api/v1/reunions/{{reunion_test_id}}/participants/{{participant_test_id}}`
- **Attendu :** Code 200, participant supprimé
- **Validation :** ✅ Réponse contient `success: true`

#### **14. Supprimer la réunion de test**
- **URL :** `DELETE /api/v1/reunions/{{reunion_test_id}}`
- **Attendu :** Code 200, réunion supprimée
- **Validation :** ✅ Réponse contient `success: true`

### **Tests de Permissions**
- **Accès non autorisé** - Code 403 pour utilisateur sans permissions
- **Création non autorisée** - Code 403 pour création sans permissions

### **Tests de Validation**
- **Données invalides** - Code 422 avec erreurs de validation
- **Statut invalide** - Code 422 pour statut non autorisé

---

## **✅ Résultats Attendus**

### **Codes de Réponse**
- **200/201** : Opérations réussies
- **403** : Accès non autorisé (tests de permissions)
- **422** : Données invalides (tests de validation)
- **404** : Ressource non trouvée

### **Structure de Réponse**
```json
{
    "success": true,
    "message": "Message de succès",
    "data": {
        // Données de la réponse
    }
}
```

### **Variables d'Environnement**
- `admin_token` : Token d'authentification admin
- `user_token` : Token d'authentification utilisateur
- `admin_user_id` : ID de l'utilisateur admin
- `user_id` : ID de l'utilisateur normal
- `reunion_test_id` : ID de la réunion créée pour les tests
- `participant_test_id` : ID du participant créé pour les tests

---

## **⚠️ Points d'Attention**

### **Ordre d'Exécution**
1. **Toujours exécuter dans l'ordre** pour éviter les erreurs de dépendances
2. **Variables d'environnement** : S'assurer qu'elles sont bien définies
3. **Nettoyage automatique** : La réunion de test est supprimée à la fin

### **Données de Test**
- **Type de réunion ID 1** : Doit exister (créé par prepare-test-data.php)
- **Utilisateurs** : admin@govtrack.com et user@govtrack.com
- **Dates** : Utilisation de dates futures pour éviter les erreurs de validation

### **Gestion des Erreurs**
- **Logs Laravel** : Vérifier `storage/logs/laravel.log` en cas d'erreur
- **Console Postman** : Affiche les messages de succès/erreur
- **Variables manquantes** : Vérifier que les tokens sont bien sauvegardés

---

## **📊 Métriques de Test**

### **Couverture**
- **Endpoints testés** : 14 endpoints principaux
- **Méthodes de service** : 7 méthodes ReunionService + 7 méthodes ReunionParticipantService
- **Scénarios de test** : CRUD complet + gestion des participants + permissions + validation

### **Fonctionnalités Validées**
- ✅ **CRUD Réunions** : Création, lecture, modification, suppression
- ✅ **Gestion des participants** : Ajout, modification, suppression, statuts
- ✅ **Filtres avancés** : Recherche, tri, pagination
- ✅ **Statistiques** : Globales et par réunion
- ✅ **Permissions** : Contrôle d'accès par rôle
- ✅ **Validation** : Vérification des données d'entrée
- ✅ **Gestion d'état** : Changement de statuts

---

## **🔄 Prochaines Étapes**

### **Après les Tests**
1. **Vérifier les logs** : `tail -f storage/logs/laravel.log`
2. **Valider la base de données** : Vérifier que les données sont correctes
3. **Passer au service suivant** : ReunionSerieService ou autre selon l'ordre des dépendances
4. **Documenter les bugs** : Noter les erreurs rencontrées

### **Services Suivants**
- **ReunionSerieService** : Gestion des séries de réunions
- **ReunionOrdreJourService** : Gestion de l'ordre du jour
- **ReunionSujetService** : Gestion des sujets de réunion
- **Etc.** selon l'ordre des dépendances

---

## **🎯 Objectif Atteint**

Une fois cette collection exécutée avec succès, le **ReunionService** sera entièrement validé et prêt pour la production ! 🚀 
