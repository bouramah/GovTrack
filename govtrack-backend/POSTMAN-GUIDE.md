# 📮 Guide d'Utilisation - Collection Postman GovTrack

> **Guide complet pour importer et utiliser la collection Postman de l'API GovTrack**

## 🎯 **Vue d'Ensemble**

La collection Postman `GovTrack-API-Complete.postman_collection.json` contient **70+ endpoints** organisés et documentés pour tester l'intégralité de l'API GovTrack avec ses **3 niveaux de permissions**.

### ⭐ **Fonctionnalités de la Collection**

- 🔐 **Tests automatiques** pour valider les réponses
- 🔄 **Variables d'environnement** pour faciliter les tests
- 📊 **Scénarios complets** pour chaque niveau de permission
- 📚 **Documentation intégrée** pour chaque endpoint
- 🎯 **Exemples concrets** avec données réelles

---

## 🚀 **Import dans Postman**

### **Méthode 1 : Import Direct**

1. **Ouvrir Postman**
2. **Cliquer** sur "Import" (bouton en haut à gauche)
3. **Glisser-déposer** le fichier `GovTrack-API-Complete.postman_collection.json`
4. **Cliquer** sur "Import"

### **Méthode 2 : Import par URL**

```bash
# Si le fichier est sur un serveur
https://votre-serveur.com/GovTrack-API-Complete.postman_collection.json
```

### **Vérification de l'Import**

✅ Vous devriez voir dans Postman :
- Collection "GovTrack API - Collection Complète v2.0"
- 6 dossiers principaux organisés
- Variables d'environnement configurées

---

## 🔧 **Configuration des Variables**

### **Variables Automatiques**

| Variable | Valeur par défaut | Description |
|----------|-------------------|-------------|
| `baseUrl` | `http://127.0.0.1:8000/api/v1` | URL de base de l'API |
| `access_token` | *Auto-rempli* | Token d'authentification |
| `project_id` | `1` | ID de projet pour les tests |
| `task_id` | `1` | ID de tâche pour les tests |

### **Personnalisation (si nécessaire)**

1. **Clic droit** sur la collection
2. **Edit** → **Variables**
3. **Modifier** les valeurs selon votre environnement

---

## 🔐 **Guide d'Utilisation - Étapes**

### **Étape 1 : Choisir son Niveau de Test**

La collection propose 3 comptes avec différents niveaux d'accès :

#### 🔑 **Administrateur (Accès Complet)**
```json
{
  "email": "admin@govtrack.gov",
  "password": "password",
  "permissions": ["view_all_projects", "manage_users", "manage_entities"]
}
```

#### 🏢 **Directeur d'Entité (Accès Entité)**
```json
{
  "email": "amadou.diop@govtrack.gov",
  "password": "password",
  "permissions": ["view_my_entity_projects", "view_my_projects"]
}
```

#### 👤 **Employé (Accès Personnel)**
```json
{
  "email": "fatou.fall@govtrack.gov",
  "password": "password",
  "permissions": ["view_my_projects"]
}
```

### **Étape 2 : Authentification**

1. **Ouvrir** `🔐 AUTHENTIFICATION`
2. **Choisir** le niveau désiré (ex: "Login Admin")
3. **Cliquer** "Send"
4. **Vérifier** que le token est automatiquement stocké

**Résultat attendu :**
```bash
✅ Tests passed: "Admin login success"
🔑 Console: "Admin token stored"
```

### **Étape 3 : Tester les Permissions**

#### **Test 1 : Liste des Projets**
1. **Aller** dans `📊 PROJETS & DASHBOARD`
2. **Exécuter** "Lister Projets"
3. **Observer** les différences selon le niveau :

```json
// Admin : Tous les projets
{
  "data": [9 projets],
  "permissions": {
    "level": "all_projects",
    "can_filter_by_user": true
  }
}

// Employé : Projets personnels uniquement
{
  "data": [2-3 projets],
  "permissions": {
    "level": "my_projects",
    "can_filter_by_user": false
  }
}
```

#### **Test 2 : Tableau de Bord**
1. **Exécuter** "Tableau de Bord"
2. **Comparer** les statistiques selon le niveau

```json
// Admin : Stats globales
"permissions_info": {
  "level": "all_projects",
  "description": "Tableau de bord global"
}

// Directeur : Stats de son entité
"permissions_info": {
  "level": "entity_projects", 
  "description": "Tableau de bord de votre entité"
}
```

### **Étape 4 : Tests Avancés**

#### **Test des Filtres Restreints**
1. **Admin/Directeur** : Peut utiliser `porteur_id` et `donneur_ordre_id`
2. **Employé** : Ces filtres sont ignorés

#### **Test des Nouveaux Endpoints**
1. **Niveau d'Exécution** : `POST /projets/{id}/niveau-execution`
2. **Utilisateurs d'Entité** : `GET /entites/{id}/utilisateurs`
3. **Historique Tâches** : `GET /taches/{id}/historique-statuts`

---

## 📊 **Sections de la Collection**

### **🔐 AUTHENTIFICATION**
- Login pour chaque niveau d'utilisateur
- Récupération d'informations utilisateur
- Déconnexion simple et complète

### **📊 PROJETS & DASHBOARD**
- Tableau de bord intelligent
- Liste et détails des projets
- Création et modification
- **NOUVEAU** : Endpoint niveau d'exécution

### **✅ TÂCHES**
- Gestion complète des tâches
- Permissions granulaires
- **NOUVEAU** : Historique des changements

### **🏢 GESTION UTILISATEURS**
- Entités et organigramme
- **NOUVEAU** : Utilisateurs par entité
- Rôles et permissions

### **💬 DISCUSSIONS & FICHIERS**
- Collaboration sur projets/tâches
- Upload de pièces jointes

### **📊 RÉFÉRENTIELS**
- Types de projets avec SLA
- Types d'entités et postes

---

## 🧪 **Scénarios de Test Recommandés**

### **Scénario 1 : Test Complet Admin**
```bash
1. Login Admin
2. Tableau de Bord → Vérifier stats globales
3. Lister Projets → Vérifier accès complet
4. Créer Projet → Tester validation
5. Changer Statut → Tester transitions
6. Niveau Exécution → Tester règles métier
```

### **Scénario 2 : Comparaison Permissions**
```bash
1. Login Admin → Lister projets → Noter le nombre
2. Login Directeur → Lister projets → Comparer
3. Login Employé → Lister projets → Comparer
4. Analyser les "permissions" dans chaque réponse
```

### **Scénario 3 : Test Validation Métier**
```bash
1. Créer un projet
2. Tenter niveau exécution à 100% → Erreur attendue
3. Changer statut à "en_cours"
4. Niveau exécution à 50% → Succès
5. Tenter diminuer à 30% → Erreur attendue
```

---

## ⚠️ **Problèmes Courants & Solutions**

### **Problème 1 : Token Non Stocké**
```bash
Symptôme: Erreur 401 sur les requêtes suivantes
Solution: 
1. Vérifier que le login a réussi (status 200)
2. Relancer le login si nécessaire
3. Vérifier les tests automatiques
```

### **Problème 2 : Serveur Non Démarré**
```bash
Symptôme: Connection refused
Solution:
1. cd govtrack-backend
2. php artisan serve
3. Vérifier que le serveur écoute sur 127.0.0.1:8000
```

### **Problème 3 : Base de Données Vide**
```bash
Symptôme: Listes vides ou erreurs
Solution:
1. php artisan migrate:fresh
2. php artisan db:seed --class=UserManagementSeeder
3. php artisan db:seed --class=Partie2Seeder
4. php artisan db:seed --class=ProjectPermissionsSeeder
```

### **Problème 4 : Permissions Manquantes**
```bash
Symptôme: Erreur 403 même avec admin
Solution:
1. Vérifier que ProjectPermissionsSeeder est exécuté
2. Relancer: php artisan db:seed --class=ProjectPermissionsSeeder
```

---

## 🎯 **Tests Automatiques Inclus**

### **Validation des Réponses**
```javascript
// Exemple de test automatique
pm.test("Permissions info included", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('permissions');
    pm.expect(jsonData.permissions).to.have.property('level');
});
```

### **Stockage Automatique des Variables**
```javascript
// Auto-stockage du token
if (pm.response.code === 200) {
    const data = pm.response.json();
    pm.globals.set('access_token', data.data.token);
    console.log('🔑 Token stored');
}
```

### **Validation des Permissions**
```javascript
// Vérification du niveau d'accès
pm.test("Correct permission level", function () {
    var permissions = pm.response.json().permissions;
    pm.expect(permissions.level).to.be.oneOf([
        'all_projects', 'entity_projects', 'my_projects'
    ]);
});
```

---

## 📈 **Métriques et Monitoring**

### **Temps de Réponse**
La collection inclut des tests pour vérifier que :
- **API** répond en moins de 2 secondes
- **Base de données** optimisée pour les requêtes
- **Pagination** pour les grandes listes

### **Codes de Statut**
- `200` : Succès
- `201` : Créé avec succès
- `400` : Erreur de validation
- `401` : Non authentifié
- `403` : Permissions insuffisantes
- `404` : Ressource non trouvée

---

## 🏆 **Bonnes Pratiques**

### **Pour les Tests**
1. **Toujours** commencer par l'authentification
2. **Tester** les 3 niveaux de permissions
3. **Vérifier** les réponses avec les tests automatiques
4. **Nettoyer** les données de test après usage

### **Pour le Développement**
1. **Utiliser** les variables d'environnement
2. **Documenter** les nouveaux endpoints
3. **Ajouter** des tests automatiques
4. **Respecter** le format de réponse existant

---

## 📞 **Support**

### **En cas de Problème**
1. **Vérifier** que le serveur Laravel fonctionne
2. **Contrôler** les logs : `storage/logs/laravel.log`
3. **Tester** les endpoints individuellement
4. **Consulter** la documentation API complète

### **Ressources**
- **Documentation API** : `API-Documentation.md`
- **README** : `README.md`
- **Tests automatiques** : Dans la collection Postman

---

*Guide créé pour GovTrack v2.0 - Collection Postman complète et professionnelle*
