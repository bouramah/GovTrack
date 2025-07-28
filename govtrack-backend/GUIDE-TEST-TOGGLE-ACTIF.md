# Guide de Test - Endpoint toggle-actif

## 🎯 Problème Identifié

L'endpoint `toggle-actif` fonctionne correctement, mais le problème vient du **payload envoyé**. Les logs montrent que vous envoyez `"actif": false` à chaque appel.

## ✅ Tests Corrects

### 1. Test avec Postman

**URL :** `{{base_url}}/api/v1/notification-configs/{{config_id}}/toggle-actif`

**Headers :**
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
Accept: application/json
```

**Body (RAW JSON) :**
```json
{
  "actif": true
}
```

**⚠️ IMPORTANT :** Utilisez `true` (boolean) et non `"true"` (string)

### 2. Test avec cURL

```bash
# Activer la configuration
curl -X POST "{{base_url}}/api/v1/notification-configs/5/toggle-actif" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"actif": true}'

# Désactiver la configuration
curl -X POST "{{base_url}}/api/v1/notification-configs/5/toggle-actif" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"actif": false}'
```

### 3. Test avec JavaScript

```javascript
// ✅ CORRECT
fetch('/api/v1/notification-configs/5/toggle-actif', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + token
  },
  body: JSON.stringify({
    actif: true  // boolean, pas "true"
  })
})

// ❌ INCORRECT
fetch('/api/v1/notification-configs/5/toggle-actif', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + token
  },
  body: JSON.stringify({
    actif: "true"  // string, pas boolean
  })
})
```

## 🔍 Vérification

### 1. Vérifier la réponse
```json
{
  "success": true,
  "message": "Statut de la configuration mis à jour avec succès",
  "data": {
    "id": 5,
    "actif": true,  // ← Doit être true si vous avez envoyé true
    // ... autres champs
  }
}
```

### 2. Vérifier en base
```bash
curl -X GET "{{base_url}}/api/v1/notification-configs/5" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## 🚨 Erreurs Communes

### 1. String au lieu de Boolean
```json
// ❌ INCORRECT
{"actif": "true"}
{"actif": "false"}

// ✅ CORRECT
{"actif": true}
{"actif": false}
```

### 2. Content-Type manquant
```bash
# ❌ INCORRECT
curl -X POST "..." -d '{"actif": true}'

# ✅ CORRECT
curl -X POST "..." -H "Content-Type: application/json" -d '{"actif": true}'
```

### 3. Token invalide
```bash
# Vérifiez votre token
curl -X GET "{{base_url}}/api/v1/auth/me" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 📋 Checklist de Test

- [ ] Token valide
- [ ] Content-Type: application/json
- [ ] Payload: `{"actif": true}` (boolean)
- [ ] Vérifier la réponse contient `"actif": true`
- [ ] Vérifier en base avec GET

## 🔧 Logs de Diagnostic

Les logs Laravel contiennent maintenant des informations détaillées :

```bash
# Voir les logs
tail -f storage/logs/laravel.log | grep "toggle-actif"
```

Les logs montrent :
- Le payload reçu
- La valeur après conversion
- Les erreurs de validation
- L'utilisateur qui fait l'appel 
