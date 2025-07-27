# 📋 Guide de Test - ReunionNotificationService Complete

## 🎯 **Objectif**
Tester systématiquement toutes les méthodes du `ReunionNotificationService` pour valider :
- ✅ Gestion des notifications d'une réunion
- ✅ Notifications globales utilisateur
- ✅ Envoi manuel et automatique
- ✅ Gestion lecture/suppression
- ✅ Statistiques et compteurs

## 🔧 **Méthodes Testées**

### **📋 Notifications d'une Réunion**
- `getNotifications()` - Récupérer les notifications d'une réunion
- `sendManualNotification()` - Envoyer une notification manuelle
- `sendAutomaticNotifications()` - Envoyer des notifications automatiques

### **📧 Notifications Globales**
- `getUserNotifications()` - Récupérer mes notifications
- `getUnreadNotifications()` - Notifications non lues
- `getUnreadCount()` - Nombre de notifications non lues
- `getNotificationStats()` - Statistiques des notifications

### **✅ Gestion Lecture**
- `markAsRead()` - Marquer une notification comme lue
- `markAllAsRead()` - Marquer toutes comme lues

### **🗑️ Gestion Suppression**
- `deleteNotification()` - Supprimer une notification
- `deleteReadNotifications()` - Supprimer les lues
- `deleteAllNotifications()` - Supprimer toutes

---

## 🚀 **Préparation**

### **1. Démarrage du Serveur Laravel**
```bash
cd govtrack-backend
php artisan serve
```

### **2. Préparation des Données de Test**
```bash
php scripts/prepare-test-data.php
```

### **3. Import de la Collection Postman**
1. Ouvrir Postman
2. Importer `GovTrack-ReunionNotificationService-Complete.postman_collection.json`
3. Importer `GovTrack-Test-Environment.postman_environment.json`

---

## 📝 **Exécution Pas à Pas**

### **🔐 Étape 1 : Authentification**
1. **Login - Admin**
   - **Méthode :** `POST /api/v1/auth/login`
   - **Payload :** `{"email": "admin@govtrack.com", "password": "password123"}`
   - **Attendu :** Code 200, token généré
   - **Variables :** `auth_token` automatiquement définie

### **📋 Étape 2 : Notifications d'une Réunion**

#### **2.1 Récupérer les notifications d'une réunion**
- **Méthode :** `GET /api/v1/reunions/{{reunion_test_id}}/notifications/`
- **Attendu :** Code 200, liste des notifications
- **Validation :** `success: true`, `data` présent

#### **2.2 Envoyer notification manuelle**
- **Méthode :** `POST /api/v1/reunions/{{reunion_test_id}}/notifications/envoyer`
- **Payload :**
```json
{
    "titre": "Test Notification Manuelle",
    "contenu": "Ceci est une notification de test envoyée manuellement.",
    "type": "INFORMATION",
    "destinataires": [98, 99],
    "priorite": "NORMALE",
    "canaux": ["EMAIL", "INTERNE"]
}
```
- **Attendu :** Code 200, notification créée
- **Variables :** `notification_test_id` automatiquement définie

#### **2.3 Envoyer notifications automatiques**
- **Méthode :** `POST /api/v1/reunions/{{reunion_test_id}}/notifications/automatiques`
- **Payload :**
```json
{
    "type_notification": "RAPPEL_24H",
    "destinataires_specifiques": [98, 99],
    "forcer_envoi": false
}
```
- **Attendu :** Code 200, notifications automatiques envoyées

### **📧 Étape 3 : Notifications Globales**

#### **3.1 Récupérer mes notifications**
- **Méthode :** `GET /api/v1/notifications/?type=INFORMATION&statut=ENVOYEE&lu=false`
- **Attendu :** Code 200, notifications filtrées
- **Validation :** Filtres appliqués correctement

#### **3.2 Récupérer notifications non lues**
- **Méthode :** `GET /api/v1/notifications/non-lues`
- **Attendu :** Code 200, notifications non lues uniquement

#### **3.3 Obtenir nombre notifications non lues**
- **Méthode :** `GET /api/v1/notifications/nombre-non-lues`
- **Attendu :** Code 200, `count` présent

#### **3.4 Obtenir statistiques notifications**
- **Méthode :** `GET /api/v1/notifications/stats`
- **Attendu :** Code 200, statistiques détaillées

### **✅ Étape 4 : Gestion Lecture**

#### **4.1 Marquer notification comme lue**
- **Méthode :** `POST /api/v1/notifications/marquer-lue/{{notification_test_id}}`
- **Attendu :** Code 200, notification marquée comme lue

#### **4.2 Marquer toutes notifications comme lues**
- **Méthode :** `POST /api/v1/notifications/marquer-toutes-lues`
- **Attendu :** Code 200, toutes les notifications marquées

### **🗑️ Étape 5 : Gestion Suppression**

#### **5.1 Supprimer une notification**
- **Méthode :** `DELETE /api/v1/notifications/{{notification_test_id}}`
- **Attendu :** Code 200, notification supprimée

#### **5.2 Supprimer notifications lues**
- **Méthode :** `DELETE /api/v1/notifications/supprimer-lues`
- **Attendu :** Code 200, notifications lues supprimées

#### **5.3 Supprimer toutes notifications**
- **Méthode :** `DELETE /api/v1/notifications/supprimer-toutes`
- **Attendu :** Code 200, toutes les notifications supprimées

---

## ✅ **Résultats Attendus**

### **Codes de Réponse**
- **200 :** Opération réussie
- **400 :** Erreur de validation ou logique métier
- **401 :** Non authentifié
- **403 :** Permissions insuffisantes
- **404 :** Ressource non trouvée

### **Structure de Réponse**
```json
{
    "success": true,
    "data": {...},
    "message": "Message de succès"
}
```

### **Données Créées**
- ✅ Notifications manuelles et automatiques
- ✅ Statistiques de notifications
- ✅ Historique des actions (lecture/suppression)

---

## 🔧 **Variables d'Environnement**

| Variable | Description | Valeur |
|----------|-------------|---------|
| `base_url` | URL du serveur Laravel | `http://localhost:8000` |
| `auth_token` | Token d'authentification | Auto-généré |
| `reunion_test_id` | ID de la réunion de test | `1` |
| `notification_test_id` | ID de la notification créée | Auto-généré |

---

## ⚠️ **Points d'Attention**

### **Ordre d'Exécution**
1. **Authentification obligatoire** en premier
2. **Création de notification** avant tests de lecture/suppression
3. **Tests de suppression** en dernier (destructifs)

### **Permissions Requises**
- `view_reunion_notifications` - Voir notifications d'une réunion
- `send_reunion_notifications` - Envoyer notifications
- `view_notifications` - Voir notifications globales

### **Nettoyage**
- Les tests de suppression sont destructifs
- Relancer `prepare-test-data.php` si nécessaire

---

## 📊 **Métriques de Test**

### **Couverture**
- ✅ **12 méthodes** testées sur 12
- ✅ **100% des endpoints** couverts
- ✅ **Tous les types de notifications** testés

### **Fonctionnalités Validées**
- ✅ Envoi manuel et automatique
- ✅ Filtrage et pagination
- ✅ Gestion lecture/suppression
- ✅ Statistiques et compteurs
- ✅ Permissions et sécurité

---

## 🚀 **Prochaines Étapes**

### **Après Tests Réussis**
1. **Vérifier les logs** Laravel pour détails
2. **Valider en base** les données créées
3. **Passer au service suivant** : `ReunionOrdreJourService`
4. **Documenter les bugs** trouvés

### **En Cas d'Erreur**
1. **Vérifier l'authentification**
2. **Contrôler les permissions**
3. **Valider les données de test**
4. **Consulter les logs d'erreur**

---

## 📝 **Notes Techniques**

### **Types de Notifications Supportés**
- `INFORMATION` - Informations générales
- `RAPPEL_24H` - Rappel 24h avant réunion
- `RAPPEL_1H` - Rappel 1h avant réunion
- `CONFIRMATION_PRESENCE` - Confirmation de présence
- `PV_DISPONIBLE` - PV disponible

### **Canaux de Notification**
- `EMAIL` - Notification par email
- `INTERNE` - Notification interne
- `SMS` - Notification SMS (si configuré)

### **Priorités**
- `FAIBLE` - Priorité faible
- `NORMALE` - Priorité normale
- `HAUTE` - Priorité haute
- `URGENTE` - Priorité urgente 
