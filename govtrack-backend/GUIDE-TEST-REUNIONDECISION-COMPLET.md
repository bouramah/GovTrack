# Guide de Test - ReunionDecisionService

## 📋 Vue d'ensemble

Ce guide détaille l'exécution de la collection Postman pour tester toutes les méthodes du `ReunionDecisionService`.

### **Fonctionnalités testées :**
- ✅ CRUD complet des décisions
- ✅ Changement de statut d'exécution
- ✅ Statistiques des décisions
- ✅ Décisions en retard
- ✅ Validation des données
- ✅ Gestion des erreurs

## 🚀 Préparation

### **1. Variables d'environnement requises :**
```json
{
  "base_url": "http://localhost:8000",
  "token": "your_jwt_token_here",
  "reunion_test_id": "1",
  "decision_test_id": "",
  "decision_created_id": ""
}
```

### **2. Prérequis :**
- ✅ Serveur Laravel démarré
- ✅ Base de données avec données de test
- ✅ Token JWT valide
- ✅ Réunion avec ID 1 existante
- ✅ Sujets de réunion existants (ID 2, 4)

## 📝 Tests de la Collection

### **1. Lister les décisions d'une réunion**
- **Endpoint :** `GET /api/v1/decisions/{reunionId}`
- **Objectif :** Récupérer toutes les décisions d'une réunion
- **Variables définies :** `decision_test_id` (si des décisions existent)

### **2. Créer une nouvelle décision**
- **Endpoint :** `POST /api/v1/decisions/{reunionId}`
- **Payload :**
```json
{
    "reunion_sujet_id": 2,
    "texte_decision": "Approuver le budget Q4 pour le projet Infrastructure Numérique",
    "type": "DEFINITIVE",
    "responsables_ids": [1, 2],
    "date_limite": "2025-12-31",
    "priorite": "ELEVEE",
    "commentaire": "Décision importante pour la continuité du projet"
}
```
- **Variables définies :** `decision_created_id`

### **3. Mettre à jour une décision**
- **Endpoint :** `PUT /api/v1/decisions/{decisionId}`
- **Objectif :** Modifier les champs d'une décision existante
- **Changements testés :** Statut, priorité, responsables, échéance

### **4. Changer le statut d'une décision**
- **Endpoint :** `POST /api/v1/decisions/{decisionId}/statut`
- **Objectif :** Changer le statut d'exécution
- **Statuts testés :** `EN_ATTENTE` → `EN_COURS` → `TERMINEE`

### **5. Obtenir les statistiques des décisions**
- **Endpoint :** `GET /api/v1/decisions/stats?reunion_id={reunionId}`
- **Objectif :** Récupérer les statistiques détaillées
- **Métriques :** Total, par type, par statut, par priorité, en retard

### **6. Obtenir les décisions en retard**
- **Endpoint :** `GET /api/v1/decisions/en-retard`
- **Objectif :** Lister les décisions dépassées
- **Filtres :** Date limite < aujourd'hui ET statut ≠ TERMINEE

### **7. Créer une décision provisoire**
- **Endpoint :** `POST /api/v1/decisions/{reunionId}`
- **Objectif :** Tester la création de décisions provisoires
- **Type :** `PROVISOIRE`

### **8. Créer une décision sans sujet**
- **Endpoint :** `POST /api/v1/decisions/{reunionId}`
- **Objectif :** Tester les décisions générales (sans sujet spécifique)
- **Champ :** `reunion_sujet_id` = null

### **9. Test de validation - Champs manquants**
- **Endpoint :** `POST /api/v1/decisions/{reunionId}`
- **Objectif :** Vérifier la validation des champs obligatoires
- **Attendu :** Erreur 422 avec message sur `texte_decision`

### **10. Test de validation - Type invalide**
- **Endpoint :** `POST /api/v1/decisions/{reunionId}`
- **Objectif :** Vérifier la validation des ENUMs
- **Attendu :** Erreur 422 avec message sur `type`

### **11. Supprimer une décision**
- **Endpoint :** `DELETE /api/v1/decisions/{decisionId}`
- **Objectif :** Supprimer une décision créée pendant les tests

## 🔧 Corrections Appliquées

### Service ReunionDecisionService
- ✅ **Champs corrigés dans `getDecisionStats`** : `type_decision` → `type`, `statut_execution` → `statut`
- ✅ **Champ corrigé dans `changeStatutExecution`** : `statut_execution` → `statut`
- ✅ **Champ corrigé dans `getDecisionsEnRetard`** : `statut_execution` → `statut`
- ✅ **Champ corrigé dans les requêtes** : `responsables` → `responsables_ids`
- ✅ **Eager loading corrigé** : `responsables` → relations correctes

### Contrôleur ReunionDecisionController
- ✅ **Validation corrigée dans `updateDecision`** :
  - `sujet_id` → `reunion_sujet_id`
  - `type_decision` → `type`
  - `statut_execution` → `statut`
  - `responsables` → `responsables_ids`
- ✅ **ENUM corrigé dans `changeStatutExecution`** : Suppression de `ANNULEE`

### Routes API
- ✅ **Conflit de routes résolu** : Routes spécifiques (`en-retard`, `stats`) placées avant les routes avec paramètres
- ✅ **Ordre correct** : Évite que `stats` soit capturé par `{reunionId}`

### Modèle ReunionDecision
- ✅ **Structure correcte** : Tous les champs alignés avec la migration
- ✅ **ENUMs corrects** : `PROVISOIRE`, `DEFINITIVE` pour type
- ✅ **Relations correctes** : `reunion`, `sujet`, `createur`, `modificateur`

## 📊 Structure des Données

### **Champs de la table `reunion_decisions` :**
- `id` : Identifiant unique
- `reunion_id` : Référence vers la réunion
- `reunion_sujet_id` : Référence vers le sujet (nullable)
- `texte_decision` : Texte de la décision
- `type` : `PROVISOIRE` ou `DEFINITIVE`
- `responsables_ids` : Tableau JSON des IDs des responsables
- `date_limite` : Date limite d'exécution
- `statut` : `EN_ATTENTE`, `EN_COURS`, `TERMINEE`
- `priorite` : `FAIBLE`, `NORMALE`, `ELEVEE`, `CRITIQUE`
- `commentaire` : Commentaire optionnel
- `creer_par`, `modifier_par` : Références vers les utilisateurs

## 🎯 Points d'Attention

### **1. Ordre d'exécution :**
- Exécuter les tests dans l'ordre pour éviter les erreurs
- Les tests 2-8 créent des données qui sont utilisées par les tests suivants

### **2. Variables dynamiques :**
- `decision_created_id` est automatiquement défini par le test 2
- `decision_test_id` est défini si des décisions existent déjà

### **3. Validation :**
- Les tests 9-10 vérifient la validation côté serveur
- Attendre des erreurs 422 pour ces tests

### **4. Permissions :**
- Tous les endpoints nécessitent des permissions spécifiques
- Vérifier que l'utilisateur a les bonnes permissions

## 🚨 Gestion des Erreurs

### **Erreurs courantes :**
1. **401 Unauthorized** : Token invalide ou expiré
2. **403 Forbidden** : Permissions insuffisantes
3. **422 Validation Error** : Données invalides
4. **404 Not Found** : Ressource inexistante

### **Actions correctives :**
- Vérifier le token JWT
- Contrôler les permissions de l'utilisateur
- Valider le format des données envoyées
- S'assurer que les IDs référencés existent

## ✅ Résultats Attendus

### **Tests de succès (1-8, 11) :**
- Status code : 200/201
- `success: true`
- Données cohérentes retournées

### **Tests de validation (9-10) :**
- Status code : 422
- `success: false`
- Messages d'erreur détaillés

## 🎉 Conclusion

Cette collection teste exhaustivement toutes les fonctionnalités du `ReunionDecisionService` :

- ✅ **CRUD complet** : Création, lecture, mise à jour, suppression
- ✅ **Gestion des statuts** : Changement d'état d'exécution
- ✅ **Statistiques** : Métriques détaillées
- ✅ **Validation** : Contrôle des données d'entrée
- ✅ **Gestion d'erreurs** : Réponses appropriées

**Le service est maintenant prêt pour la production !** 🚀 
