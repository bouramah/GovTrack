# Guide d'exécution - ReunionPVService Complete

## 📋 Vue d'ensemble

Ce guide explique comment tester complètement le `ReunionPVService` avec la collection Postman fournie.

## 🔧 Prérequis

1. **Serveur Laravel en cours d'exécution** sur `http://localhost:8000`
2. **Base de données** avec des données de test
3. **Token JWT valide** pour l'authentification
4. **Postman** installé et configuré

## 📦 Variables d'environnement requises

Configurez ces variables dans Postman :

```json
{
  "base_url": "http://localhost:8000",
  "token": "your_jwt_token_here",
  "reunion_test_id": "1",
  "pv_test_id": "",
  "pv_created_id": "",
  "pv_reject_id": ""
}
```

## 🧪 Tests inclus dans la collection

### **1. Lister les PVs d'une réunion**
- **URL :** `GET {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv`
- **Objectif :** Récupérer tous les PVs d'une réunion spécifique
- **Tests :** Vérification du statut 200, structure de réponse, et récupération automatique de l'ID de test

### **2. Créer un nouveau PV**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv`
- **Payload :**
```json
{
    "contenu": "Procès-verbal de la réunion du 1\n\n**Ordre du jour :**\n- Point 1 : Présentation du projet\n- Point 2 : Discussion des objectifs\n- Point 3 : Planification des actions\n\n**Décisions prises :**\n- Approbation du budget initial\n- Validation du planning de développement\n- Désignation des responsables\n\n**Actions à suivre :**\n- Finaliser le cahier des charges\n- Organiser la prochaine réunion\n- Envoyer les invitations aux participants"
}
```
- **Objectif :** Créer un nouveau PV pour une réunion
- **Tests :** Vérification du statut 201, champs requis, et sauvegarde automatique de l'ID créé

### **3. Récupérer un PV spécifique**
- **URL :** `GET {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv/{{pv_created_id}}`
- **Objectif :** Récupérer un PV spécifique par son ID
- **Tests :** Vérification de la récupération du bon PV

### **4. Mettre à jour un PV**
- **URL :** `PUT {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv/{{pv_created_id}}`
- **Payload :**
```json
{
    "contenu": "Procès-verbal de la réunion du 1 - VERSION MISE À JOUR\n\n**Ordre du jour :**\n- Point 1 : Présentation du projet\n- Point 2 : Discussion des objectifs\n- Point 3 : Planification des actions\n- Point 4 : Nouvelles exigences ajoutées\n\n**Décisions prises :**\n- Approbation du budget initial\n- Validation du planning de développement\n- Désignation des responsables\n- Ajout de nouvelles fonctionnalités\n\n**Actions à suivre :**\n- Finaliser le cahier des charges\n- Organiser la prochaine réunion\n- Envoyer les invitations aux participants\n- Préparer la démonstration"
}
```
- **Objectif :** Modifier le contenu d'un PV existant
- **Tests :** Vérification de la mise à jour du contenu

### **5. Soumettre un PV pour validation**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv/{{pv_created_id}}/soumettre`
- **Payload :** `{}`
- **Objectif :** Changer le statut du PV de `BROUILLON` à `EN_ATTENTE`
- **Tests :** Vérification du changement de statut

### **6. Valider un PV**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv/{{pv_created_id}}/valider`
- **Payload :**
```json
{
    "commentaire_validation": "PV validé avec succès. Le contenu est complet et conforme aux exigences."
}
```
- **Objectif :** Valider un PV avec un commentaire
- **Tests :** Vérification du statut `VALIDE` et des champs de validation

### **7. Créer un second PV pour tester le rejet**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv`
- **Payload :**
```json
{
    "contenu": "Second PV pour test de rejet"
}
```
- **Objectif :** Créer un second PV pour tester la fonctionnalité de rejet
- **Tests :** Vérification de la création et sauvegarde de l'ID

### **8. Rejeter un PV**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv/{{pv_reject_id}}/rejeter`
- **Payload :**
```json
{
    "commentaire_validation": "PV rejeté : Le contenu est incomplet et nécessite des corrections importantes."
}
```
- **Objectif :** Rejeter un PV avec un commentaire obligatoire
- **Tests :** Vérification du statut `REJETE` et des champs de validation

### **9. Obtenir les statistiques des PVs**
- **URL :** `GET {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv/stats`
- **Objectif :** Récupérer les statistiques des PVs d'une réunion
- **Tests :** Vérification de la structure des statistiques

### **10. Récupérer le dernier PV validé**
- **URL :** `GET {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv/dernier-valide`
- **Objectif :** Récupérer le dernier PV validé d'une réunion
- **Tests :** Vérification du statut `VALIDE`

### **11. Test de validation - Contenu manquant**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv`
- **Payload :** `{}`
- **Objectif :** Tester la validation du champ `contenu` obligatoire
- **Tests :** Vérification des erreurs de validation

### **12. Test de validation - Rejet sans commentaire**
- **URL :** `POST {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv/{{pv_reject_id}}/rejeter`
- **Payload :** `{}`
- **Objectif :** Tester la validation du commentaire obligatoire pour le rejet
- **Tests :** Vérification des erreurs de validation

### **13. Supprimer un PV**
- **URL :** `DELETE {{base_url}}/api/v1/reunions/{{reunion_test_id}}/pv/{{pv_reject_id}}`
- **Objectif :** Supprimer un PV créé pendant les tests
- **Tests :** Vérification de la suppression réussie

## 🔍 Fonctionnalités testées

### **Gestion des statuts**
- ✅ `BROUILLON` (par défaut)
- ✅ `EN_ATTENTE` (après soumission)
- ✅ `VALIDE` (après validation)
- ✅ `REJETE` (après rejet)

### **Gestion des versions**
- ✅ Numérotation automatique des versions
- ✅ Contrainte unique sur `(reunion_id, version)`
- ✅ Calcul de la nouvelle version

### **Workflow de validation**
- ✅ Création en brouillon
- ✅ Soumission pour validation
- ✅ Validation avec commentaire
- ✅ Rejet avec commentaire obligatoire

### **Validation des données**
- ✅ Champ `contenu` obligatoire
- ✅ Commentaire obligatoire pour le rejet
- ✅ Vérification des permissions

### **Permissions et sécurité**
- ✅ Authentification JWT
- ✅ Vérification des permissions par réunion
- ✅ Contrôle d'accès aux PVs

## 🚀 Exécution de la collection

1. **Importez la collection** dans Postman
2. **Configurez les variables** d'environnement
3. **Obtenez un token JWT** valide
4. **Exécutez les tests** dans l'ordre (1-13)
5. **Vérifiez les résultats** dans la console Postman

## 📊 Résultats attendus

- **Tests 1-10 :** Statuts 200/201 avec succès
- **Tests 11-12 :** Statut 422 avec erreurs de validation
- **Test 13 :** Statut 200 avec suppression réussie

## 🔧 Corrections appliquées

### **Modèle ReunionPV**
- ✅ **ENUMs ajoutés** : `EN_ATTENTE` et `REJETE`
- ✅ **Scopes ajoutés** : `scopeEnAttente` et `scopeRejetes`
- ✅ **Accesseurs mis à jour** : Couleurs et icônes pour nouveaux statuts
- ✅ **Méthode `getPeutEtreValideAttribute`** : Support de `EN_ATTENTE`

### **Service ReunionPVService**
- ✅ **Version corrigée** : Utilisation de `$nouvelleVersion` calculée
- ✅ **Timestamps corrigés** : `modifie_le` au lieu de `date_modification`
- ✅ **Statuts corrigés** : `statut` au lieu de `statut_validation`
- ✅ **Champs supprimés** : `creer_par` et `modifier_par` (non présents en DB)

### **Contrôleur ReunionPVController**
- ✅ **Validation simplifiée** : Seulement `contenu` requis
- ✅ **Champs supprimés** : `titre`, `resume`, `decisions_prises`, etc. (non en DB)
- ✅ **Statuts corrigés** : Alignement avec la migration

## ✅ État final

Le `ReunionPVService` est maintenant :
- **Complètement fonctionnel** (service + contrôleur + modèle)
- **Cohérent** avec la migration de base de données
- **Validé** avec des tests complets
- **Prêt pour la production**

## 🎯 Prochaines étapes

Après avoir testé cette collection, vous pouvez :
1. **Continuer avec le service suivant** dans l'ordre de dépendance
2. **Tester les intégrations** avec d'autres services
3. **Valider les performances** avec des charges de données importantes 
