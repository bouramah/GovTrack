# 📦 Guide d'Import Postman - GovTrack API

## 🚀 Import en 3 étapes

### 1️⃣ Ouvrir Postman
- Lancez l'application Postman
- Connectez-vous ou créez un compte si nécessaire

### 2️⃣ Importer la Collection
1. **Cliquez sur "Import"** (bouton en haut à gauche)
2. **Drag & Drop** ou **sélectionnez** le fichier :
   ```
   GovTrack-API-Collection.postman_collection.json
   ```
3. **Confirmez l'import** - La collection apparaîtra dans la sidebar

### 3️⃣ Configuration (Optionnel)
- La variable `base_url` est pré-configurée : `http://localhost:8000/api/v1`
- Modifiez si votre serveur Laravel utilise un autre port

## ✅ Vérification

Après import, vous devriez voir **7 dossiers** :

```
🚀 GovTrack API - Partie 1 Gestion Utilisateurs
├── 🏢 Type Entités (5 endpoints)
├── 🏛️ Entités (12 endpoints)
├── 💼 Postes (5 endpoints)
├── 👥 Utilisateurs (10 endpoints)
├── 🛡️ Rôles (8 endpoints)
├── 🔐 Permissions (7 endpoints)
└── 🧪 Tests Spéciaux (5 endpoints)
```

**Total : 52 endpoints prêts à tester !** 🎉

## 🧪 Premier Test

1. **Assurez-vous que Laravel tourne :**
   ```bash
   php artisan serve
   ```

2. **Testez l'endpoint de base :**
   - Ouvrir : `🏢 Type Entités > Lister tous les types d'entités`
   - Cliquer **"Send"**
   - ✅ Vous devriez voir 3 types : Direction, Service, Division

3. **🎯 Test Organigramme (nouveau) :**
   - Ouvrir : `🏛️ Entités > Organigramme complet de l'organisation`
   - Cliquer **"Send"**
   - ✅ Vous obtenez la structure hiérarchique complète avec chefs et effectifs
   - 📊 **Parfait pour interfaces graphiques !**

## 📚 Utilisez la Documentation

Consultez `API-Documentation.md` pour :
- 📋 Vue d'ensemble complète
- 🔍 Détails de chaque endpoint
- 💡 Exemples d'utilisation
- 🛡️ Comptes de test
- ⚠️ Notes importantes

---

**🎯 Vous êtes prêt à tester l'API GovTrack complète !** 🚀 
