# Guide de Test - ReunionCalendarService

## 📋 Vue d'ensemble

Ce guide détaille comment tester le service `ReunionCalendarService` qui gère les fonctionnalités de calendrier pour les réunions. Le service permet de visualiser, filtrer et gérer les événements de réunion dans différentes vues temporelles.

## 🔧 Prérequis

### 1. **Environnement de Test**
- Serveur Laravel en cours d'exécution (`php artisan serve`)
- Base de données configurée avec les migrations appliquées
- Utilisateur authentifié avec les permissions appropriées

### 2. **Permissions Requises**
- `view_reunions` - Consulter les réunions et le calendrier

### 3. **Données de Test**
- Type de réunion existant (`type_reunion_id`)
- Utilisateur existant (`user_id`)
- Réunions créées avec différents statuts

## 🚀 Configuration Postman

### 1. **Import de la Collection**
1. Ouvrir Postman
2. Importer le fichier `GovTrack-ReunionCalendarService-Complete.postman_collection.json`
3. Créer un environnement avec les variables suivantes :

### 2. **Variables d'Environnement**
```json
{
  "base_url": "http://localhost:8000",
  "auth_token": "VOTRE_TOKEN_JWT",
  "type_reunion_id": "1",
  "user_id": "1",
  "start_date": "",
  "end_date": "",
  "date": "",
  "year_month": ""
}
```

### 3. **Authentification**
- Obtenir un token JWT via l'endpoint de login
- Définir la variable `auth_token` dans l'environnement Postman

## 📝 Tests par Endpoint

### **1. Récupérer les événements calendrier**
**Endpoint:** `GET /api/v1/calendar/events`

**Description:** Récupère les événements calendrier pour une période donnée avec filtres.

**Paramètres de requête:**
- `start_date` (required) - Date de début (YYYY-MM-DD)
- `end_date` (required) - Date de fin (YYYY-MM-DD)
- `filters[type_reunion_id]` (optional) - ID du type de réunion
- `filters[user_id]` (optional) - ID de l'utilisateur
- `filters[statut]` (optional) - Statut des réunions (PLANIFIEE, EN_COURS, TERMINEE, ANNULEE, REPORTEE)

**Réponse attendue:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Réunion Comité Exécutif",
      "start": "2025-01-15T10:00:00.000000Z",
      "end": "2025-01-15T12:00:00.000000Z",
      "allDay": false,
      "type": "reunion",
      "type_reunion": "Comité Exécutif",
      "lieu": "Salle de conférence A",
      "statut": "PLANIFIEE",
      "participants": [...],
      "serie_id": null,
      "color": "#3B82F6",
      "url": "/reunions/1"
    }
  ],
  "total": 1
}
```

### **2. Vue calendrier journalière**
**Endpoint:** `GET /api/v1/calendar/day`

**Description:** Obtenir la vue calendrier pour une journée spécifique.

**Paramètres de requête:**
- `date` (required) - Date au format YYYY-MM-DD
- `filters[type_reunion_id]` (optional) - ID du type de réunion
- `filters[user_id]` (optional) - ID de l'utilisateur
- `filters[statut]` (optional) - Statut des réunions

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "date": "2025-01-15",
    "events": [...],
    "total_events": 3,
    "repartition_par_heure": {...}
  }
}
```

### **3. Vue calendrier hebdomadaire**
**Endpoint:** `GET /api/v1/calendar/week`

**Description:** Obtenir la vue calendrier pour une semaine spécifique.

**Paramètres de requête:**
- `start_date` (required) - Date de début de la semaine (YYYY-MM-DD)
- `filters[type_reunion_id]` (optional) - ID du type de réunion
- `filters[user_id]` (optional) - ID de l'utilisateur
- `filters[statut]` (optional) - Statut des réunions

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "week_start": "2025-01-13",
    "week_end": "2025-01-19",
    "days": [
      {
        "date": "2025-01-13",
        "events": [...],
        "total_events": 2
      }
    ],
    "total_events": 15
  }
}
```

### **4. Vue calendrier mensuelle**
**Endpoint:** `GET /api/v1/calendar/month`

**Description:** Obtenir la vue calendrier pour un mois spécifique.

**Paramètres de requête:**
- `year_month` (required) - Mois au format YYYY-MM
- `filters[type_reunion_id]` (optional) - ID du type de réunion
- `filters[user_id]` (optional) - ID de l'utilisateur
- `filters[statut]` (optional) - Statut des réunions

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "year_month": "2025-01",
    "weeks": [
      {
        "week_start": "2025-01-06",
        "week_end": "2025-01-12",
        "days": [...]
      }
    ],
    "total_events": 45,
    "repartition_par_semaine": {...}
  }
}
```

### **5. Vérifier la disponibilité d'un utilisateur**
**Endpoint:** `POST /api/v1/calendar/availability/check`

**Description:** Vérifier la disponibilité d'un utilisateur pour une période donnée.

**Payload:**
```json
{
  "user_id": 1,
  "start_date": "2025-01-15",
  "end_date": "2025-01-15",
  "exclude_reunion_id": null
}
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "user_id": 1,
    "start_date": "2025-01-15",
    "end_date": "2025-01-15",
    "disponible": true,
    "conflits": [],
    "heures_disponibles": [
      {
        "debut": "09:00",
        "fin": "10:00"
      },
      {
        "debut": "14:00",
        "fin": "18:00"
      }
    ]
  }
}
```

### **6. Trouver des créneaux disponibles**
**Endpoint:** `POST /api/v1/calendar/availability/slots`

**Description:** Trouver des créneaux disponibles pour un groupe de participants.

**Payload:**
```json
{
  "participant_ids": [1, 2, 3],
  "start_date": "2025-01-15",
  "end_date": "2025-01-20",
  "duration": 60
}
```

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "participant_ids": [1, 2, 3],
    "start_date": "2025-01-15",
    "end_date": "2025-01-20",
    "duration": 60,
    "creneaux_disponibles": [
      {
        "date": "2025-01-15",
        "debut": "14:00",
        "fin": "15:00",
        "participants_disponibles": [1, 2, 3]
      },
      {
        "date": "2025-01-16",
        "debut": "10:00",
        "fin": "11:00",
        "participants_disponibles": [1, 2, 3]
      }
    ]
  }
}
```

### **7. Obtenir les statistiques calendrier**
**Endpoint:** `GET /api/v1/calendar/stats`

**Description:** Obtenir les statistiques du calendrier pour une période donnée.

**Paramètres de requête:**
- `start_date` (required) - Date de début (YYYY-MM-DD)
- `end_date` (required) - Date de fin (YYYY-MM-DD)
- `user_id` (optional) - ID de l'utilisateur pour filtrer

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "total_reunions": 25,
    "reunions_par_statut": {
      "PLANIFIEE": 10,
      "EN_COURS": 5,
      "TERMINEE": 8,
      "ANNULEE": 2
    },
    "reunions_par_type": {
      "1": 15,
      "2": 10
    },
    "duree_moyenne": 90,
    "participants_moyens": 8.5,
    "taux_occupation": 0.75
  }
}
```

### **8. Exporter les événements au format iCal**
**Endpoint:** `GET /api/v1/calendar/export/ical`

**Description:** Exporter les événements au format iCal pour importation dans d'autres calendriers.

**Paramètres de requête:**
- `start_date` (required) - Date de début (YYYY-MM-DD)
- `end_date` (required) - Date de fin (YYYY-MM-DD)
- `filters[type_reunion_id]` (optional) - ID du type de réunion
- `filters[user_id]` (optional) - ID de l'utilisateur
- `filters[statut]` (optional) - Statut des réunions

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "ical_content": "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//GovTrack//Calendar//FR\n...",
    "filename": "reunions_2025-01-15_2025-01-31.ics",
    "total_events": 15
  }
}
```

### **9. Obtenir mes événements**
**Endpoint:** `GET /api/v1/calendar/events/my`

**Description:** Obtenir les événements de l'utilisateur connecté.

**Paramètres de requête:**
- `start_date` (required) - Date de début (YYYY-MM-DD)
- `end_date` (required) - Date de fin (YYYY-MM-DD)
- `filters[type_reunion_id]` (optional) - ID du type de réunion
- `filters[statut]` (optional) - Statut des réunions

**Réponse attendue:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Réunion Comité Exécutif",
      "start": "2025-01-15T10:00:00.000000Z",
      "end": "2025-01-15T12:00:00.000000Z",
      "statut": "PLANIFIEE",
      "role": "PARTICIPANT"
    }
  ],
  "total": 1
}
```

### **10. Obtenir mes statistiques**
**Endpoint:** `GET /api/v1/calendar/stats/my`

**Description:** Obtenir les statistiques personnelles de l'utilisateur connecté.

**Paramètres de requête:**
- `start_date` (required) - Date de début (YYYY-MM-DD)
- `end_date` (required) - Date de fin (YYYY-MM-DD)

**Réponse attendue:**
```json
{
  "success": true,
  "data": {
    "total_reunions": 8,
    "reunions_par_statut": {
      "PLANIFIEE": 3,
      "EN_COURS": 2,
      "TERMINEE": 3
    },
    "duree_totale": 720,
    "taux_presence": 0.875,
    "reunions_par_role": {
      "PARTICIPANT": 6,
      "ANIMATEUR": 2
    }
  }
}
```

## 🧪 Tests Avancés

### **11. Test - Événements avec filtres multiples**
Teste la récupération d'événements avec plusieurs filtres combinés (type de réunion, utilisateur, statut).

### **12. Test - Créneaux disponibles pour plusieurs participants**
Teste la recherche de créneaux pour plusieurs participants avec une durée de 2h.

## 🔍 Validation des Données

### **Structure des Événements**
Chaque événement doit contenir :
- `id` (integer) - ID de la réunion
- `title` (string) - Titre de la réunion
- `start` (datetime) - Date et heure de début
- `end` (datetime) - Date et heure de fin
- `statut` (string) - Statut de la réunion
- `participants` (array) - Liste des participants
- `color` (string) - Couleur de l'événement

### **Filtres Disponibles**
- `type_reunion_id` - Filtrer par type de réunion
- `user_id` - Filtrer par utilisateur participant
- `statut` - Filtrer par statut (PLANIFIEE, EN_COURS, TERMINEE, ANNULEE, REPORTEE)

## ⚠️ Cas d'Erreur

### **Erreurs de Validation**
- `422` - Données invalides (dates incorrectes, filtres invalides)
- `400` - Erreur métier (période trop longue, utilisateur inexistant)
- `404` - Ressource non trouvée
- `500` - Erreur serveur

### **Messages d'Erreur Courants**
- "La date de fin doit être postérieure à la date de début"
- "La période demandée est trop longue (maximum 90 jours)"
- "Utilisateur non trouvé"
- "Type de réunion non trouvé"

## 📊 Tests de Performance

### **Scénarios de Test**
1. **Événements sur longue période** - Tester avec 3 mois de données
2. **Filtres complexes** - Tester avec plusieurs filtres combinés
3. **Nombreux participants** - Tester la recherche de créneaux avec 10+ participants
4. **Export volumineux** - Tester l'export iCal avec 100+ événements

### **Métriques à Surveiller**
- Temps de réponse des vues calendrier
- Performance des requêtes avec filtres multiples
- Utilisation mémoire lors de l'export iCal
- Temps de calcul des créneaux disponibles

## 🔧 Dépannage

### **Problèmes Courants**

1. **Token d'authentification expiré**
   - Solution: Renouveler le token via l'endpoint de login

2. **Dates invalides**
   - Solution: Vérifier le format des dates (YYYY-MM-DD)

3. **Filtres non reconnus**
   - Solution: Vérifier les valeurs des filtres (statuts en majuscules)

4. **Aucun événement retourné**
   - Solution: Vérifier que des réunions existent dans la période demandée

### **Logs de Debug**
Les erreurs sont loggées avec les informations suivantes :
- ID de l'utilisateur
- Paramètres de la requête
- Message d'erreur détaillé
- Timestamp de l'erreur

## ✅ Checklist de Validation

- [ ] Tous les endpoints répondent correctement
- [ ] Les vues calendrier (jour/semaine/mois) fonctionnent
- [ ] Les filtres sont appliqués correctement
- [ ] La vérification de disponibilité fonctionne
- [ ] La recherche de créneaux est précise
- [ ] Les statistiques sont calculées correctement
- [ ] L'export iCal génère un fichier valide
- [ ] Les événements personnels sont filtrés correctement
- [ ] Les cas d'erreur sont gérés proprement
- [ ] Les performances sont acceptables

## 📈 Améliorations Futures

1. **Notifications calendrier** - Intégrer les notifications de rappel
2. **Synchronisation externe** - Synchroniser avec Google Calendar, Outlook
3. **Vues personnalisées** - Permettre des vues calendrier personnalisées
4. **Répétition d'événements** - Gérer les événements récurrents
5. **Optimisation des requêtes** - Améliorer les performances avec du cache
6. **Filtres avancés** - Ajouter des filtres par lieu, organisateur, etc.
7. **Export multi-format** - Supporter l'export en PDF, Excel
8. **Calendrier partagé** - Permettre le partage de calendriers entre équipes 
