# Système de Notifications par Email - Projets

## Vue d'ensemble

Le système de notifications par email pour les projets permet d'informer automatiquement les parties prenantes des changements importants dans la vie d'un projet.

## Types de Notifications

### 1. Création de Projet (`ProjetCreated`)

**Déclencheur :** Création d'un nouveau projet
**Destinataires :**
- Porteur du projet
- Donneur d'ordre
- Membres de l'équipe (si des tâches sont assignées)

**Contenu :**
- Informations du projet (titre, description, dates, etc.)
- Informations sur le créateur
- Lien vers le projet

### 2. Changement de Statut (`ProjetStatusChanged`)

**Déclencheur :** Modification du statut d'un projet
**Destinataires :**
- Porteur du projet
- Donneur d'ordre
- Membres de l'équipe

**Contenu :**
- Ancien et nouveau statut
- Commentaire associé (optionnel)
- Informations sur la personne qui a effectué le changement

### 3. Mise à Jour du Niveau d'Exécution (`ProjetExecutionLevelUpdated`)

**Déclencheur :** Modification du niveau d'exécution d'un projet
**Destinataires :**
- Porteur du projet
- Donneur d'ordre
- Membres de l'équipe

**Contenu :**
- Ancien et nouveau niveau d'exécution
- Progression (différence)
- Commentaire associé (optionnel)

## Architecture Technique

### Classes Mailable

- `App\Mail\ProjetCreated`
- `App\Mail\ProjetStatusChanged`
- `App\Mail\ProjetExecutionLevelUpdated`

### Événements

- `App\Events\ProjetCreated`
- `App\Events\ProjetStatusChanged`
- `App\Events\ProjetExecutionLevelUpdated`

### Listeners

- `App\Listeners\SendProjetCreatedNotification`
- `App\Listeners\SendProjetStatusChangedNotification`
- `App\Listeners\SendProjetExecutionLevelUpdatedNotification`

## Templates d'Email

### Structure des Templates

Tous les templates sont basés sur une structure HTML responsive avec :
- Header avec logo et titre
- Informations du projet
- Détails du changement
- Bouton d'action (lien vers le projet)
- Footer avec informations de l'application

### Localisation

Les templates utilisent les variables suivantes :
- `$projet` : Modèle du projet
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
2. **Donneur d'ordre** : Toujours notifié (sauf s'il est l'auteur de l'action)
3. **Membres de l'équipe** : Notifiés si le projet a des tâches assignées

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

Utilisez le script `test_notifications_projets.php` pour tester le système :

```bash
php test_notifications_projets.php
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

Les templates sont dans `resources/views/emails/projets/` :
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

## Support

Pour toute question ou problème avec le système de notifications :

1. Consultez les logs dans `storage/logs/`
2. Vérifiez la configuration email dans `.env`
3. Testez avec le script `test_notifications_projets.php`
4. Contactez l'équipe de développement 
