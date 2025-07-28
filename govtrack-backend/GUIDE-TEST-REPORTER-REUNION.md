# Guide de Test - Reporter une Réunion

## 🎯 Fonctionnalité Implémentée

La fonctionnalité "reporter une réunion" permet de :
- Changer le statut d'une réunion vers `REPORTEE`
- Modifier les dates de début et fin
- Enregistrer la date de reprogrammation
- Notifier les participants (optionnel)

## ✅ Endpoint API

**URL :** `{{base_url}}/api/v1/reunions/{{reunion_id}}/reporter`

**Méthode :** `POST`

**Headers :**
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
Accept: application/json
```

## 📋 Payload Requis

```json
{
  "nouvelle_date_debut": "2025-08-15T14:00:00Z",
  "nouvelle_date_fin": "2025-08-15T16:00:00Z",
  "raison_report": "Conflit d'horaire avec une autre réunion importante",
  "notifier_participants": true
}
```

### Champs Obligatoires
- `nouvelle_date_debut` : Nouvelle date et heure de début (après maintenant)

### Champs Optionnels
- `nouvelle_date_fin` : Nouvelle date et heure de fin (après nouvelle_date_debut)
- `raison_report` : Raison du report (max 500 caractères)
- `notifier_participants` : Notifier les participants (boolean)

## 🧪 Tests avec cURL

### 1. Reporter une réunion planifiée
```bash
curl -X POST "{{base_url}}/api/v1/reunions/1/reporter" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nouvelle_date_debut": "2025-08-15T14:00:00Z",
    "nouvelle_date_fin": "2025-08-15T16:00:00Z",
    "raison_report": "Conflit d'\''horaire",
    "notifier_participants": true
  }'
```

### 2. Reporter une réunion en cours
```bash
curl -X POST "{{base_url}}/api/v1/reunions/2/reporter" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nouvelle_date_debut": "2025-08-20T10:00:00Z",
    "raison_report": "Urgence dans l'\''équipe"
  }'
```

### 3. Test avec date invalide (doit échouer)
```bash
curl -X POST "{{base_url}}/api/v1/reunions/1/reporter" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nouvelle_date_debut": "2025-07-01T10:00:00Z"
  }'
```

## 🔍 Réponse Attendue

### Succès (200)
```json
{
  "success": true,
  "message": "Réunion reportée avec succès",
  "data": {
    "reunion": {
      "id": 1,
      "titre": "Réunion de projet",
      "statut": "REPORTEE",
      "date_debut": "2025-08-15T14:00:00.000000Z",
      "date_fin": "2025-08-15T16:00:00.000000Z",
      "reprogrammee_le": "2025-07-28T16:45:00.000000Z",
      "modifier_par": 97,
      "date_modification": "2025-07-28T16:45:00.000000Z",
      // ... autres champs
    },
    "ancienne_date_debut": "2025-07-30T10:00:00.000000Z",
    "ancienne_date_fin": "2025-07-30T12:00:00.000000Z",
    "nouvelle_date_debut": "2025-08-15T14:00:00Z",
    "nouvelle_date_fin": "2025-08-15T16:00:00Z"
  }
}
```

### Erreur (400)
```json
{
  "success": false,
  "message": "Seules les réunions planifiées ou en cours peuvent être reportées"
}
```

## 🚨 Cas d'Erreur

### 1. Réunion déjà terminée
```json
{
  "success": false,
  "message": "Seules les réunions planifiées ou en cours peuvent être reportées"
}
```

### 2. Date invalide
```json
{
  "success": false,
  "message": "Données de validation invalides",
  "errors": {
    "nouvelle_date_debut": [
      "La nouvelle date de début doit être une date postérieure à maintenant."
    ]
  }
}
```

### 3. Permissions insuffisantes
```json
{
  "success": false,
  "message": "Vous n'avez pas les permissions pour reporter cette réunion"
}
```

## 📊 Transitions de Statut

### Statuts autorisés pour le report
- ✅ `PLANIFIEE` → `REPORTEE`
- ✅ `EN_COURS` → `REPORTEE`

### Statuts non autorisés
- ❌ `TERMINEE` → `REPORTEE` (impossible)
- ❌ `ANNULEE` → `REPORTEE` (impossible)

### Transitions depuis REPORTEE
- ✅ `REPORTEE` → `PLANIFIEE` (réactivation)
- ✅ `REPORTEE` → `ANNULEE` (annulation)

## 🔧 Vérifications

### 1. Vérifier le statut en base
```bash
curl -X GET "{{base_url}}/api/v1/reunions/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 2. Vérifier les logs
```bash
tail -f storage/logs/laravel.log | grep "Réunion reportée"
```

## 📋 Checklist de Test

- [ ] Token valide avec permission `update_reunions`
- [ ] Réunion existe et est `PLANIFIEE` ou `EN_COURS`
- [ ] `nouvelle_date_debut` est dans le futur
- [ ] `nouvelle_date_fin` est après `nouvelle_date_debut` (si fournie)
- [ ] Vérifier que le statut devient `REPORTEE`
- [ ] Vérifier que `reprogrammee_le` est mis à jour
- [ ] Vérifier que les dates sont modifiées
- [ ] Vérifier les logs de l'action

## 🎯 Tests Avancés

### 1. Test de performance
```bash
# Tester avec plusieurs appels simultanés
for i in {1..5}; do
  curl -X POST "{{base_url}}/api/v1/reunions/1/reporter" \
    -H "Authorization: Bearer YOUR_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"nouvelle_date_debut": "2025-08-15T14:00:00Z"}' &
done
wait
```

### 2. Test de validation
```bash
# Test sans nouvelle_date_debut
curl -X POST "{{base_url}}/api/v1/reunions/1/reporter" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{}'
```

### 3. Test de permissions
```bash
# Test avec un utilisateur sans permissions
curl -X POST "{{base_url}}/api/v1/reunions/1/reporter" \
  -H "Authorization: Bearer INVALID_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"nouvelle_date_debut": "2025-08-15T14:00:00Z"}'
``` 
