# Système de Notifications par Email - Tâches

## Vue d'ensemble

Le système de notifications par email pour les tâches permet d'informer automatiquement les parties prenantes des changements importants dans la vie d'une tâche.

## Types de Notifications

### 1. Création de Tâche (`TacheCreated`)

**Déclencheur :** Création d'une nouvelle tâche
**Destinataires :**
- Porteur du projet
- Responsable de la tâche (si différent du créateur)
- Donneur d'ordre du projet (si différent du créateur)

**Contenu :**
- Informations de la tâche (titre, description, dates, etc.)
- Informations sur le projet associé
- Informations sur le créateur
- Lien vers la tâche

### 2. Changement de Statut (`TacheStatusChanged`)

**Déclencheur :** Modification du statut d'une tâche
**Destinataires :**
- Porteur du projet
- Responsable de la tâche
- Donneur d'ordre du projet

**Contenu :**
- Ancien et nouveau statut
- Commentaire associé (optionnel)
- Informations sur la personne qui a effectué le changement

### 3. Mise à Jour du Niveau d'Exécution (`TacheExecutionLevelUpdated`)

**Déclencheur :** Modification du niveau d'exécution d'une tâche
**Destinataires :**
- Porteur du projet
- Responsable de la tâche
- Donneur d'ordre du projet

**Contenu :**
- Ancien et nouveau niveau d'exécution
- Progression (différence)
- Commentaire associé (optionnel)

## Architecture Technique

### Classes Mailable

- `App\Mail\TacheCreated`
- `App\Mail\TacheStatusChanged`
- `App\Mail\TacheExecutionLevelUpdated`

### Événements

- `App\Events\TacheCreated`
- `App\Events\TacheStatusChanged`
- `App\Events\TacheExecutionLevelUpdated`

### Listeners

- `App\Listeners\SendTacheCreatedNotification`
- `App\Listeners\SendTacheStatusChangedNotification`
- `App\Listeners\SendTacheExecutionLevelUpdatedNotification`

## Templates d'Email

### Structure des Templates

Tous les templates sont basés sur une structure HTML responsive avec :
- Header avec logo et titre
- Informations de la tâche
- Détails du changement
- Informations sur le projet associé
- Bouton d'action (lien vers la tâche)
- Footer avec informations de l'application

### Localisation

Les templates utilisent les variables suivantes :
- `$tache` : Modèle de la tâche
- `$creator/changer/updater` : Utilisateur qui a effectué l'action
- `$recipient` : Destinataire de l'email
- `$appName` : Nom de l'application
- `$appUrl` : URL de l'application

## Configuration

### Variables d'Environnement

```env
MAIL_MAILER=log  # Pour les tests (stockage en log)
MAIL_MAILER=smtp # Pour la production
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@govtrack.com
MAIL_FROM_NAME="GovTrack"
```

### Configuration des Files d'Attente

Les notifications sont envoyées via les files d'attente pour éviter de bloquer l'interface utilisateur.

```bash
# Démarrer le worker des files d'attente
php artisan queue:work

# Ou en mode daemon
php artisan queue:work --daemon
```

## Logique Métier

### Détermination des Destinataires

1. **Porteur du projet** : Toujours notifié (sauf s'il est l'auteur de l'action)
2. **Responsable de la tâche** : Toujours notifié (sauf s'il est l'auteur de l'action)
3. **Donneur d'ordre du projet** : Toujours notifié (sauf s'il est l'auteur de l'action)

### Éviter les Doublons

- Un utilisateur ne reçoit qu'une seule notification par événement
- L'auteur de l'action n'est pas notifié
- Les destinataires sont dédupliqués par ID utilisateur

### Gestion des Erreurs

- Les erreurs d'envoi sont loggées
- Les notifications échouées sont retentées automatiquement
- Les listeners implémentent `ShouldQueue` pour la résilience

## Tests

### Script de Test

Utilisez le script `test_notifications_taches.php` pour tester le système :

```bash
php test_notifications_taches.php
```

### Vérification des Emails

1. **Mode Log** : Consultez `storage/logs/laravel.log`
2. **Mode Array** : Les emails sont stockés en mémoire
3. **Mode SMTP** : Configurez un serveur SMTP de test

## Personnalisation

### Ajouter de Nouvelles Notifications

1. Créer la classe Mailable
2. Créer l'événement
3. Créer le listener
4. Créer le template d'email
5. Enregistrer dans `EventServiceProvider`
6. Déclencher l'événement dans le contrôleur

### Modifier les Templates

Les templates sont dans `resources/views/emails/taches/` :
- `created.blade.php`
- `status-changed.blade.php`
- `execution-level-updated.blade.php`

### Ajouter des Destinataires

Modifiez les listeners pour ajouter de nouveaux destinataires selon vos besoins métier.

## Sécurité

### Validation des Données

- Toutes les données sont validées avant l'envoi
- Les emails sont échappés pour éviter les injections
- Les liens sont générés de manière sécurisée

### Confidentialité

- Seuls les utilisateurs autorisés reçoivent les notifications
- Les informations sensibles ne sont pas incluses dans les emails
- Les logs ne contiennent que les informations nécessaires

## Monitoring

### Logs

Les actions suivantes sont loggées :
- Envoi réussi des notifications
- Échec d'envoi des notifications
- Nombre de destinataires par notification

### Métriques

- Nombre de notifications envoyées par jour
- Taux de succès d'envoi
- Temps de traitement des files d'attente

## API Endpoints

### Création de Tâche

```http
POST /api/taches
Content-Type: application/json

{
    "titre": "Nouvelle tâche",
    "description": "Description de la tâche",
    "projet_id": 1,
    "responsable_id": 2,
    "date_debut_previsionnelle": "2024-01-15",
    "date_fin_previsionnelle": "2024-01-22"
}
```

### Changement de Statut

```http
POST /api/taches/{id}/changer-statut
Content-Type: application/json

{
    "nouveau_statut": "en_cours",
    "commentaire": "Début du travail"
}
```

### Mise à Jour du Niveau d'Exécution

```http
POST /api/taches/{id}/niveau-execution
Content-Type: application/json

{
    "niveau_execution": 75,
    "commentaire": "Progression significative"
}
```

## Règles Métier

### Niveau d'Exécution

1. **Modification uniquement en cours** : Le niveau d'exécution ne peut être modifié que lorsque la tâche est en cours
2. **Maximum 99% manuel** : Impossible de définir le niveau d'exécution à 100% manuellement
3. **Diminution autorisée** : L'utilisateur peut diminuer le niveau d'exécution si nécessaire
4. **Commentaire requis** : Un commentaire est requis pour confirmer un niveau identique

### Changement de Statut

1. **Permissions** : Seuls le responsable de la tâche ou le porteur du projet peuvent changer le statut
2. **Terminaison** : Seul le porteur du projet peut terminer une tâche
3. **Justificatif** : Un justificatif est requis pour demander la clôture
4. **Historique** : Tous les changements sont enregistrés dans l'historique

## Support

Pour toute question ou problème avec le système de notifications :

1. Consultez les logs dans `storage/logs/`
2. Vérifiez la configuration email dans `.env`
3. Testez avec le script `test_notifications_taches.php`
4. Contactez l'équipe de développement 
