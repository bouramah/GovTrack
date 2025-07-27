# 🚀 Guide d'Exécution - Tests Réunions

## ⚡ **Démarrage Rapide**

### **1. Préparation de l'Environnement**
```bash
# Démarrer le serveur Laravel
php artisan serve

# Préparer les données de test
php scripts/prepare-test-data.php
```

### **2. Configuration Postman**
- **Base URL :** `http://localhost:8000`
- **Collection :** `GovTrack-API-Complete.postman_collection.json`
- **Environnement :** Créer avec les variables suivantes

### **3. Variables d'Environnement Postman**
```json
{
  "base_url": "http://localhost:8000",
  "admin_token": "",
  "directeur_token": "",
  "chef_projet_token": "",
  "analyste_token": "",
  "reunion_id": "",
  "type_reunion_id": "",
  "serie_id": "",
  "ordre_jour_id": "",
  "sujet_id": "",
  "decision_id": "",
  "pv_id": ""
}
```

---

## 👥 **Utilisateurs de Test**

| Rôle | Email | Password | ID |
|------|-------|----------|----|
| Admin | `admin@govtrack.com` | `password123` | 97 |
| Directeur | `directeur@govtrack.com` | `password123` | 98 |
| Chef Projet | `chef-projet@govtrack.com` | `password123` | 99 |
| Analyste | `analyste@govtrack.com` | `password123` | 100 |

---

## 🔑 **Authentification**

### **Login Admin**
```bash
POST {{base_url}}/api/auth/login
Content-Type: application/json

{
  "email": "admin@govtrack.com",
  "password": "password123"
}
```

### **Récupérer le Token**
```javascript
// Dans le test Postman
pm.environment.set("admin_token", pm.response.json().token);
```

---

## 📋 **Étapes du Scénario (Temps Estimé)**

### **Phase 1 : Configuration (5 min)**
1. **Login Admin** → Récupérer token
2. **Créer Type Réunion** → Sauvegarder `type_reunion_id`
3. **Créer Série** → Sauvegarder `serie_id`

### **Phase 2 : Planification (5 min)**
4. **Créer Réunion** → Sauvegarder `reunion_id`
5. **Ajouter Participants** → Vérifier succès
6. **Créer Ordre du Jour** → Sauvegarder `ordre_jour_id`

### **Phase 3 : Préparation (10 min)**
7. **Créer Sujet** → Sauvegarder `sujet_id`
8. **Ajouter Objectifs** → Vérifier création
9. **Ajouter Difficultés** → Vérifier création

### **Phase 4 : Exécution (5 min)**
10. **Prendre Décisions** → Sauvegarder `decision_id`
11. **Créer Actions** → Vérifier assignation
12. **Démarrer Réunion** → Changer statut

### **Phase 5 : Clôture (5 min)**
13. **Rédiger PV** → Sauvegarder `pv_id`
14. **Valider PV** → Changer statut
15. **Terminer Réunion** → Clôturer

---

## ✅ **Validation des Étapes**

### **Codes de Réponse Attendus**
- **200** : Succès (GET, PUT, DELETE)
- **201** : Création réussie (POST)
- **400** : Erreur de validation
- **401** : Non authentifié
- **403** : Non autorisé
- **404** : Ressource non trouvée

### **Tests Automatiques Postman**
```javascript
// Exemple de test pour une création
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Response has ID", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.id).to.exist;
});

// Sauvegarder l'ID pour les étapes suivantes
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    pm.environment.set("reunion_id", jsonData.id);
}
```

---

## 🔧 **Dépannage**

### **Erreur 401 - Non Authentifié**
```bash
# Vérifier le token
echo $admin_token

# Refaire un login
POST {{base_url}}/api/auth/login
```

### **Erreur 422 - Validation**
```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier la structure JSON
# Utiliser un validateur JSON en ligne
```

### **Erreur 500 - Serveur**
```bash
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log

# Redémarrer le serveur
php artisan serve
```

---

## 📊 **Métriques de Performance**

### **Temps de Réponse Acceptables**
- **GET** : < 500ms
- **POST** : < 1000ms
- **PUT** : < 800ms
- **DELETE** : < 300ms

### **Tests de Charge**
```bash
# Avec Apache Bench (optionnel)
ab -n 100 -c 10 http://localhost:8000/api/reunions
```

---

## 🎯 **Points de Contrôle**

### **Avant Chaque Étape**
- [ ] Token valide
- [ ] IDs précédents sauvegardés
- [ ] Données JSON valides
- [ ] Headers corrects

### **Après Chaque Étape**
- [ ] Code de réponse correct
- [ ] ID retourné sauvegardé
- [ ] Données cohérentes
- [ ] Logs sans erreur

---

## 📝 **Documentation des Résultats**

### **Template de Rapport**
```markdown
## Test Réunions - [Date]

### ✅ Succès
- Étape 1: Type réunion créé (ID: X)
- Étape 2: Série créée (ID: Y)
- ...

### ⚠️ Problèmes
- Étape X: [Description du problème]
- Solution: [Action corrective]

### 📊 Métriques
- Temps total: XX minutes
- Taux de succès: XX%
- Erreurs: X
```

---

## 🚀 **Exécution Automatisée**

### **Script de Test (Optionnel)**
```bash
#!/bin/bash
# test-reunions.sh

echo "🧪 Démarrage des tests réunions..."

# Préparation
php scripts/prepare-test-data.php

# Tests API (avec curl ou Newman)
newman run GovTrack-API-Complete.postman_collection.json \
  --environment test-env.json \
  --reporters cli,json \
  --reporter-json-export results.json

echo "✅ Tests terminés"
```

---

## 📞 **Support**

### **En Cas de Problème**
1. Vérifier les logs Laravel
2. Contrôler la base de données
3. Valider les permissions
4. Tester les endpoints individuellement

### **Logs Importants**
```bash
# Logs Laravel
tail -f storage/logs/laravel.log

# Logs MySQL (si accessible)
tail -f /var/log/mysql/error.log
```

---

**🎯 Objectif :** Exécuter le scénario complet en moins de 30 minutes avec un taux de succès de 100% ! 
 