# 🔐 Guide d'Authentification - API GovTrack

## 📋 Vue d'ensemble

L'API GovTrack utilise **Laravel Sanctum** pour l'authentification avec des tokens personnels. Toutes les routes sont maintenant protégées et nécessitent une authentification valide.

## 🚀 Démarrage rapide

### 1. **Importer la collection Postman**
```bash
Import: GovTrack-API-Secured.postman_collection.json
```

### 2. **Se connecter**
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "admin@govtrack.gov",
    "password": "password"
}
```

### 3. **Utiliser le token**
Le token est automatiquement sauvegardé dans Postman. Pour les autres outils :
```http
Authorization: Bearer {votre_token}
```

## 👥 Comptes de test

| Email | Rôle | Permissions | Usage |
|-------|------|-------------|-------|
| `admin@govtrack.gov` | Administrateur | **6 permissions** (toutes) | Tests complets |
| `amadou.diop@govtrack.gov` | Directeur | **4 permissions** | Tests directeur |
| `fatou.fall@govtrack.gov` | Développeur | **2 permissions** | Tests utilisateur limité |

**Mot de passe pour tous :** `password`

## 🔑 Endpoints d'authentification

### **Login**
```http
POST /api/v1/auth/login
```
**Payload :**
```json
{
    "email": "admin@govtrack.gov",
    "password": "password"
}
```

**Réponse :**
```json
{
    "message": "Connexion réussie",
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "matricule": "ADM001",
            "nom": "Admin",
            "prenom": "Super",
            "email": "admin@govtrack.gov",
            "affectation_actuelle": {
                "poste": "Directeur Général",
                "entite": "Direction des Systèmes d'Information",
                "date_debut": "2024-12-29"
            },
            "entites_dirigees": [...],
            "roles": [...],
            "permissions": [
                "create_instruction",
                "edit_instruction", 
                "validate_instruction",
                "view_all_instructions",
                "manage_users",
                "manage_entities"
            ]
        },
        "token": "2|abc123def456..."
    }
}
```

### **Profil utilisateur**
```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

### **Déconnexion**
```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

### **Rafraîchir token**
```http
POST /api/v1/auth/refresh
Authorization: Bearer {token}
```

## 🔒 Système de permissions

### **Permissions disponibles**

| Permission | Description | Qui l'a |
|------------|-------------|---------|
| `manage_users` | Gestion des utilisateurs, postes, rôles | Admin, Directeur |
| `manage_entities` | Gestion des entités et types d'entités | Admin, Directeur |
| `create_instruction` | Créer des instructions (Partie 2) | Admin, Directeur, Employé |
| `edit_instruction` | Modifier des instructions (Partie 2) | Admin, Directeur, Employé |
| `validate_instruction` | Valider des instructions (Partie 2) | Admin, Directeur |
| `view_all_instructions` | Voir toutes les instructions (Partie 2) | Admin, Directeur |

### **Protection par type d'action**

#### **Lecture (GET) - 📖 Libre**
Accessible à tous les utilisateurs authentifiés :
- `GET /type-entites`
- `GET /entites`
- `GET /postes`
- `GET /users`
- `GET /roles`
- `GET /permissions`
- Endpoints spécialisés (organigramme, historiques, etc.)

#### **Écriture (POST/PUT/DELETE) - 🔒 Protégée**

**`manage_entities` requis :**
- `POST/PUT/DELETE /type-entites`
- `POST/PUT/DELETE /entites`
- `POST /entites/{id}/affecter-chef`
- `POST /entites/{id}/terminer-mandat-chef`

**`manage_users` requis :**
- `POST/PUT/DELETE /users`
- `POST/PUT/DELETE /postes`
- `POST/PUT/DELETE /roles`
- `POST/PUT/DELETE /permissions`
- `POST /users/{id}/affecter`
- `POST /users/{id}/assign-role`
- Toutes les actions de gestion utilisateur

## 🛠️ Utilisation pratique

### **Avec curl**
```bash
# 1. Se connecter
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@govtrack.gov","password":"password"}'

# 2. Utiliser le token (remplacer TOKEN)
curl -X GET http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"
```

### **Avec JavaScript/Fetch**
```javascript
// 1. Login
const loginResponse = await fetch('/api/v1/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        email: 'admin@govtrack.gov',
        password: 'password'
    })
});

const loginData = await loginResponse.json();
const token = loginData.data.token;

// 2. Utiliser l'API
const usersResponse = await fetch('/api/v1/users', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});
```

## ❌ Gestion des erreurs

### **401 - Non authentifié**
```json
{
    "message": "Utilisateur non authentifié",
    "success": false
}
```
**Solution :** Exécuter le login pour obtenir un token valide.

### **403 - Permission insuffisante**
```json
{
    "message": "Permission insuffisante pour effectuer cette action",
    "success": false,
    "required_permission": "manage_users",
    "user_permissions": ["create_instruction", "edit_instruction"]
}
```
**Solution :** Utiliser un compte avec les permissions appropriées.

### **422 - Erreur de validation**
```json
{
    "message": "Erreur de validation",
    "success": false,
    "errors": {
        "email": ["Les informations d'identification fournies sont incorrectes."]
    }
}
```

## 🔧 Configuration développement

### **Variables d'environnement**
```env
# .env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,localhost:3000
SESSION_DRIVER=database
```

### **Headers recommandés**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

## 📊 Traçabilité

Avec l'authentification, tous les enregistrements incluent automatiquement :
- `creer_par` : Email de l'utilisateur qui a créé l'enregistrement
- `modifier_par` : Email de l'utilisateur qui a modifié l'enregistrement
- `date_creation` et `date_modification` : Horodatage automatique

## 🎯 Tests recommandés

1. **Login avec chaque compte de test**
2. **Tester les permissions** (admin vs directeur vs employé)
3. **Vérifier les erreurs 401/403**
4. **Tester la déconnexion**
5. **Vérifier la traçabilité** (champs creer_par/modifier_par)

---

🎉 **L'API GovTrack Partie 1 est maintenant complètement sécurisée et prête pour la production !** 
