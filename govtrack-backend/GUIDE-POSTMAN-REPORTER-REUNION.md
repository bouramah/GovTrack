# Guide d'Utilisation - Collection Postman "Reporter Réunion"

## 📋 Vue d'Ensemble

Cette collection Postman contient **15 tests** organisés en **7 sections** pour tester complètement la fonctionnalité "reporter une réunion".

## 🚀 Installation et Configuration

### 1. Importer la Collection
1. Ouvrez Postman
2. Cliquez sur **"Import"**
3. Sélectionnez le fichier `GovTrack-ReporterReunion.postman_collection.json`
4. Cliquez sur **"Import"**

### 2. Configuration de l'Environnement
1. Créez un nouvel environnement dans Postman
2. Ajoutez les variables suivantes :

| Variable | Valeur | Description |
|----------|--------|-------------|
| `base_url` | `http://localhost:8000` | URL de base de l'API |
| `auth_token` | (vide) | Token d'authentification (rempli automatiquement) |
| `reunion_id` | (vide) | ID de la réunion à tester (rempli automatiquement) |
| `reunion_en_cours_id` | (vide) | ID de la réunion en cours (rempli automatiquement) |

### 3. Sélectionner l'Environnement
- Sélectionnez votre environnement dans le menu déroulant en haut à droite

## 📂 Structure de la Collection

### 1. Authentification
- **Login** : Authentification et sauvegarde automatique du token

### 2. Préparation - Récupérer les réunions
- **Liste des réunions** : Récupère et sauvegarde automatiquement les IDs des réunions
- **Détails d'une réunion** : Affiche les détails d'une réunion spécifique

### 3. Tests de Report de Réunion
- **Reporter une réunion planifiée - Succès** : Test principal de succès
- **Reporter une réunion en cours - Succès** : Test avec réunion en cours
- **Reporter avec date invalide - Erreur** : Test de validation de date
- **Reporter sans nouvelle date - Erreur** : Test de champ obligatoire
- **Reporter avec date de fin invalide - Erreur** : Test de cohérence des dates

### 4. Tests de Permissions
- **Reporter sans token - Erreur 401** : Test d'authentification
- **Reporter avec token invalide - Erreur 401** : Test de token invalide

### 5. Tests de Validation
- **Reporter une réunion inexistante - Erreur 404** : Test de réunion inexistante
- **Reporter avec raison trop longue - Erreur** : Test de limite de caractères

### 6. Vérification Post-Report
- **Vérifier le statut après report** : Vérification de la mise à jour
- **Liste des réunions reportées** : Test du filtrage par statut

### 7. Tests de Performance
- **Test de charge - Reporter plusieurs fois** : Test de performance

## 🧪 Exécution des Tests

### Exécution Manuelle
1. **Commencez par l'authentification** :
   - Exécutez **"1. Authentification > Login"**
   - Vérifiez que le token est sauvegardé dans les variables

2. **Préparez les données** :
   - Exécutez **"2. Préparation > Liste des réunions"**
   - Vérifiez que les IDs sont sauvegardés

3. **Testez les cas de succès** :
   - Exécutez **"3. Tests de Report > Reporter une réunion planifiée - Succès"**
   - Vérifiez les assertions dans l'onglet "Test Results"

4. **Testez les cas d'erreur** :
   - Exécutez les tests d'erreur pour vérifier la validation

### Exécution Automatique
1. Cliquez sur **"Run collection"** (bouton play)
2. Sélectionnez les tests à exécuter
3. Cliquez sur **"Run GovTrack - Reporter Réunion"**

## 📊 Interprétation des Résultats

### Tests de Succès (200)
- ✅ **Status code is 200** : La requête a réussi
- ✅ **Response is successful** : L'API retourne `success: true`
- ✅ **Réunion est reportée** : Le statut devient `REPORTEE`
- ✅ **Date reprogrammée est enregistrée** : `reprogrammee_le` est mis à jour
- ✅ **Nouvelle date de début est correcte** : La date est dans le futur

### Tests d'Erreur (400)
- ✅ **Status code is 400** : La validation a échoué comme attendu
- ✅ **Erreur de validation** : Les erreurs sont retournées
- ✅ **Erreur spécifique** : L'erreur concerne le bon champ

### Tests d'Authentification (401)
- ✅ **Status code is 401** : L'authentification a échoué comme attendu

## 🔧 Personnalisation

### Modifier les Données de Test
1. **Changer les dates** : Modifiez les valeurs dans le body des requêtes
2. **Changer les raisons** : Modifiez le champ `raison_report`
3. **Tester avec d'autres réunions** : Modifiez les IDs dans les variables

### Ajouter de Nouveaux Tests
1. **Dupliquer un test existant**
2. **Modifier le payload** selon vos besoins
3. **Ajouter des assertions** dans l'onglet "Tests"

## 🚨 Dépannage

### Problèmes Courants

#### 1. Erreur 401 - Non authentifié
**Cause** : Token manquant ou expiré
**Solution** : 
- Réexécutez le test "Login"
- Vérifiez que `auth_token` est rempli dans les variables

#### 2. Erreur 404 - Réunion non trouvée
**Cause** : ID de réunion invalide
**Solution** :
- Réexécutez "Liste des réunions" pour mettre à jour les IDs
- Vérifiez que des réunions existent en base

#### 3. Erreur 500 - Erreur serveur
**Cause** : Problème côté serveur
**Solution** :
- Vérifiez les logs Laravel : `tail -f storage/logs/laravel.log`
- Vérifiez que le serveur fonctionne : `php artisan serve`

#### 4. Tests qui échouent
**Cause** : Données de test incohérentes
**Solution** :
- Vérifiez que les dates sont dans le futur
- Vérifiez que les réunions sont dans le bon statut

### Vérification des Variables
```javascript
// Dans la console Postman (View > Show Postman Console)
console.log('Token:', pm.environment.get('auth_token'));
console.log('Réunion ID:', pm.environment.get('reunion_id'));
console.log('Base URL:', pm.environment.get('base_url'));
```

## 📈 Métriques de Test

### Temps de Réponse
- **Acceptable** : < 1000ms
- **Limite** : < 2000ms
- **Critique** : > 2000ms

### Taux de Succès
- **Objectif** : 100% des tests passent
- **Acceptable** : 90% des tests passent
- **Critique** : < 80% des tests passent

## 🔄 Maintenance

### Mise à Jour de la Collection
1. **Sauvegardez** votre environnement avec les variables
2. **Importez** la nouvelle version de la collection
3. **Restaurez** votre environnement

### Ajout de Nouveaux Cas de Test
1. **Identifiez** le nouveau cas à tester
2. **Créez** un nouveau test dans la section appropriée
3. **Ajoutez** les assertions nécessaires
4. **Documentez** le test dans ce guide

## 📞 Support

En cas de problème :
1. **Vérifiez** les logs Laravel
2. **Testez** manuellement avec cURL
3. **Consultez** le guide de test détaillé
4. **Vérifiez** que l'API fonctionne correctement

---

**Collection créée le :** 28/07/2025  
**Version :** 1.0  
**Compatibilité :** Laravel 10+, Postman 10+ 
