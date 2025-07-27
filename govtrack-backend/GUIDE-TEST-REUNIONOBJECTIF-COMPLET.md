# Guide de Test - ReunionObjectifService

## 📋 Vue d'ensemble

Cette collection teste tous les endpoints du service `ReunionObjectifService` qui gère les objectifs des sujets de réunion.

## 🎯 Endpoints Testés

1. **GET** `/api/v1/objectifs/{sujetId}` - Récupérer tous les objectifs d'un sujet
2. **GET** `/api/v1/objectifs/objectif/{objectifId}` - Récupérer un objectif spécifique
3. **POST** `/api/v1/objectifs` - Créer un objectif
4. **POST** `/api/v1/objectifs/multiple` - Créer plusieurs objectifs en lot
5. **PUT** `/api/v1/objectifs/{objectifId}` - Mettre à jour un objectif
6. **DELETE** `/api/v1/objectifs/{objectifId}` - Supprimer un objectif
7. **GET** `/api/v1/objectifs/{sujetId}/stats` - Statistiques des objectifs
8. **GET** `/api/v1/objectifs/{sujetId}?filtres` - Tester avec filtres
9. **Tests d'erreur** - Validation et ressources inexistantes

## ✅ Corrections Appliquées

- **Routes corrigées** : Utilisation de `sujetId` au lieu de `reunionId`
- **Contrôleur aligné** : Toutes les méthodes utilisent maintenant `sujetId`
- **Service corrigé** : 
  - Changé `sujet_id` vers `reunion_sujet_id` dans toutes les requêtes
  - Supprimé les références à `reunionId` inutiles
  - Nettoyé les logs et messages d'erreur
- **Validation mise à jour** : 
  - Champs alignés avec la migration
  - ENUMs corrigés : `EN_COURS,ATTEINT,EN_RETARD` au lieu de `en_cours,termine,annule`
- **Base de données alignée** : 
  - Utilisation du bon nom de colonne `reunion_sujet_id`
  - Utilisation des bons timestamps : `date_creation` au lieu de `created_at`
  - Utilisation du bon nom de colonne : `taux_realisation` au lieu de `progression`

## 🚀 Préparation

### 1. Variables d'environnement
Assurez-vous d'avoir configuré :
- `base_url` : URL de votre API (ex: `http://localhost:8000`)
- `auth_token` : Token d'authentification valide
- `sujet_test_id` : ID d'un sujet existant pour les tests

### 2. Authentification
```bash
# Obtenir un token d'authentification
curl -X POST "{{base_url}}/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@govtrack.com","password":"password"}'
```

## 📝 Exécution des Tests

### Étape 1 : Récupération des objectifs existants
```bash
GET {{base_url}}/api/v1/objectifs/{{sujet_test_id}}
```
**Résultat attendu :** Liste des objectifs du sujet (peut être vide)

### Étape 2 : Création d'un objectif
```bash
POST {{base_url}}/api/v1/objectifs
{
    "reunion_sujet_id": {{sujet_test_id}},
    "titre": "Objectif Test - Amélioration des performances",
    "description": "Augmenter les performances du système de 20%",
    "cible": "20% d'amélioration",
    "taux_realisation": 0,
    "pourcentage_decaissement": 0,
    "date_objectif": "2025-08-15",
    "statut": "EN_COURS",
    "ordre": 1,
    "actif": true
}
```
**Résultat attendu :** Objectif créé avec ID généré

### Étape 3 : Création multiple d'objectifs
```bash
POST {{base_url}}/api/v1/objectifs/multiple
{
    "objectifs": [
        {
            "reunion_sujet_id": {{sujet_test_id}},
            "titre": "Objectif 1 - Formation équipe",
            "description": "Former l'équipe sur les nouvelles technologies",
            "cible": "100% de l'équipe formée",
            "taux_realisation": 0,
            "pourcentage_decaissement": 0,
            "date_objectif": "2025-08-20",
            "statut": "EN_COURS",
            "ordre": 1,
            "actif": true
        },
        {
            "reunion_sujet_id": {{sujet_test_id}},
            "titre": "Objectif 2 - Documentation",
            "description": "Compléter la documentation technique",
            "cible": "Documentation 100% complète",
            "taux_realisation": 0,
            "pourcentage_decaissement": 0,
            "date_objectif": "2025-08-25",
            "statut": "EN_COURS",
            "ordre": 2,
            "actif": true
        }
    ]
}
```
**Résultat attendu :** Tableau d'objectifs créés

### Étape 4 : Mise à jour d'un objectif
```bash
PUT {{base_url}}/api/v1/objectifs/{{objectif_test_id}}
{
    "titre": "Objectif Test - Amélioration des performances (Modifié)",
    "description": "Augmenter les performances du système de 25% - Mise à jour",
    "cible": "25% d'amélioration",
    "taux_realisation": 15,
    "pourcentage_decaissement": 30,
    "date_objectif": "2025-08-20",
    "statut": "EN_COURS",
    "ordre": 1,
    "actif": true
}
```
**Résultat attendu :** Objectif mis à jour

### Étape 5 : Statistiques des objectifs
```bash
GET {{base_url}}/api/v1/objectifs/{{sujet_test_id}}/stats
```
**Résultat attendu :** Statistiques détaillées (total, par statut, taux de réalisation)

### Étape 6 : Test avec filtres
```bash
GET {{base_url}}/api/v1/objectifs/{{sujet_test_id}}?statut=EN_COURS&actif=true
```
**Résultat attendu :** Objectifs filtrés par statut et actif

## 🔍 Points d'Attention

### Champs Requis
- `reunion_sujet_id` : Doit exister dans la table `reunion_sujets`
- `titre` : Obligatoire, max 255 caractères
- `statut` : ENUM (`EN_COURS`, `ATTEINT`, `EN_RETARD`)

### Validation
- `taux_realisation` : Entre 0 et 100
- `pourcentage_decaissement` : Entre 0 et 100
- `date_objectif` : Format YYYY-MM-DD
- `ordre` : Entier positif

### Relations
- Chaque objectif appartient à un sujet (`reunion_sujet_id`)
- Les objectifs peuvent avoir des difficultés associées
- Les statistiques sont calculées par sujet

## ⚠️ Cas d'Erreur Testés

1. **Objectif inexistant** : ID 99999 → 404
2. **Validation invalide** : Titre vide + statut invalide → 422
3. **Sujet inexistant** : `reunion_sujet_id` invalide → 422

## 📊 Résultats Attendus

### Succès
- ✅ Status codes : 200, 201
- ✅ `success: true`
- ✅ `data` contient les informations
- ✅ Variables automatiquement définies

### Erreurs
- ❌ Status codes : 404, 422, 500
- ❌ `success: false`
- ❌ `errors` contient les détails

## 🔧 Dépannage

### Erreur 404 - Sujet non trouvé
```bash
# Vérifier que le sujet existe
GET {{base_url}}/api/v1/sujets/{{sujet_test_id}}
```

### Erreur 422 - Validation
```bash
# Vérifier les champs requis
{
    "reunion_sujet_id": "ID valide",
    "titre": "Titre obligatoire",
    "statut": "EN_COURS|ATTEINT|EN_RETARD"
}
```

### Erreur 401 - Authentification
```bash
# Renouveler le token
POST {{base_url}}/api/v1/auth/login
```

## 📈 Métriques de Test

- **Tests totaux** : 10
- **Tests de succès** : 8
- **Tests d'erreur** : 2
- **Couverture** : CRUD complet + filtres + stats

## 🎯 Prochaines Étapes

Après validation de cette collection :
1. Tester `ReunionDifficulteService`
2. Tester `ReunionDecisionService`
3. Continuer avec les services restants

---

**Note :** Cette collection teste tous les aspects du service `ReunionObjectifService` et valide l'intégrité des données et des relations. 
