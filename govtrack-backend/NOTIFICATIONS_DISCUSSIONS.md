# Notifications de Discussions - GovTrack

## Vue d'ensemble

Le système de notifications de discussions permet d'informer automatiquement les utilisateurs concernés lors de la création de commentaires sur les projets et les tâches. Il gère les nouveaux commentaires et les réponses aux commentaires existants.

## Types de notifications

### 1. Commentaires sur les projets

#### Nouveau commentaire sur un projet
- **Déclencheur** : Création d'un commentaire principal (sans parent_id)
- **Destinataires** :
  - Porteur du projet (sauf si c'est lui qui a posté)
  - Donneur d'ordre (sauf si c'est lui qui a posté)
  - Responsables des tâches du projet (sauf si c'est eux qui ont posté)

#### Réponse à un commentaire de projet
- **Déclencheur** : Création d'une réponse (avec parent_id)
- **Destinataires** :
  - Auteur du commentaire original (sauf si c'est lui qui a répondu)

### 2. Commentaires sur les tâches

#### Nouveau commentaire sur une tâche
- **Déclencheur** : Création d'un commentaire principal (sans parent_id)
- **Destinataires** :
  - Responsable de la tâche (sauf si c'est lui qui a posté)
  - Porteur du projet parent (sauf si c'est lui qui a posté)

#### Réponse à un commentaire de tâche
- **Déclencheur** : Création d'une réponse (avec parent_id)
- **Destinataires** :
  - Auteur du commentaire original (sauf si c'est lui qui a répondu)

## Architecture technique

### Événements

#### DiscussionProjetCreated
```php
class DiscussionProjetCreated
{
    public $discussion;    // Instance de DiscussionProjet
    public $author;        // Instance de User (auteur)
    public $isReply;       // Boolean (true si c'est une réponse)
}
```

#### DiscussionTacheCreated
```php
class DiscussionTacheCreated
{
    public $discussion;    // Instance de DiscussionTache
    public $author;        // Instance de User (auteur)
    public $isReply;       // Boolean (true si c'est une réponse)
}
```

### Classes Mailable

#### DiscussionProjetCreated (Mailable)
- **Template** : `emails.discussions.projet-created`
- **Sujet** : Différencié selon le type (nouveau commentaire ou réponse)
- **Données** : Discussion, auteur, destinataire, type, message parent

#### DiscussionTacheCreated (Mailable)
- **Template** : `emails.discussions.tache-created`
- **Sujet** : Différencié selon le type (nouveau commentaire ou réponse)
- **Données** : Discussion, auteur, destinataire, type, message parent

### Listeners

#### SendDiscussionProjetCreatedNotification
- **Queue** : Oui (ShouldQueue)
- **Logique** :
  1. Charger les relations nécessaires (projet, porteur, donneur d'ordre, tâches)
  2. Déterminer les destinataires selon le type (nouveau commentaire ou réponse)
  3. Éviter les doublons
  4. Envoyer les emails en file d'attente

#### SendDiscussionTacheCreatedNotification
- **Queue** : Oui (ShouldQueue)
- **Logique** :
  1. Charger les relations nécessaires (tâche, responsable, projet, porteur)
  2. Déterminer les destinataires selon le type (nouveau commentaire ou réponse)
  3. Éviter les doublons
  4. Envoyer les emails en file d'attente

## Templates d'email

### Template projet-created.blade.php
- **Couleur principale** : Bleu (#007bff)
- **Contenu** :
  - Informations du projet (titre, type, statut, porteur)
  - Commentaire original (si c'est une réponse)
  - Informations de l'auteur
  - Message du commentaire
  - Lien vers le projet

### Template tache-created.blade.php
- **Couleur principale** : Vert (#28a745)
- **Contenu** :
  - Informations de la tâche (titre, statut, niveau d'exécution, responsable)
  - Informations du projet parent
  - Commentaire original (si c'est une réponse)
  - Informations de l'auteur
  - Message du commentaire
  - Lien vers la tâche

## Configuration

### EventServiceProvider
Les événements et listeners sont enregistrés dans `app/Providers/EventServiceProvider.php` :

```php
protected $listen = [
    // ... autres événements ...
    
    DiscussionProjetCreated::class => [
        SendDiscussionProjetCreatedNotification::class,
    ],
    
    DiscussionTacheCreated::class => [
        SendDiscussionTacheCreatedNotification::class,
    ],
];
```

### Contrôleurs
Les événements sont déclenchés dans les contrôleurs :

#### DiscussionProjetController
```php
// Dans la méthode store()
$isReply = !empty($validated['parent_id']);
event(new DiscussionProjetCreated($discussion, $request->user(), $isReply));
```

#### DiscussionTacheController
```php
// Dans la méthode store()
$isReply = !empty($validated['parent_id']);
event(new DiscussionTacheCreated($discussion, $request->user(), $isReply));
```

## Règles métier

### Destinataires des notifications

#### Nouveaux commentaires sur projets
1. **Porteur du projet** : Toujours notifié (sauf s'il est l'auteur)
2. **Donneur d'ordre** : Toujours notifié (sauf s'il est l'auteur)
3. **Responsables des tâches** : Notifiés pour maintenir l'équipe informée

#### Réponses aux commentaires de projets
1. **Auteur du commentaire original** : Notifié pour suivre les réponses

#### Nouveaux commentaires sur tâches
1. **Responsable de la tâche** : Notifié pour être au courant des discussions
2. **Porteur du projet** : Notifié pour suivre l'évolution des tâches

#### Réponses aux commentaires de tâches
1. **Auteur du commentaire original** : Notifié pour suivre les réponses

### Évitement des doublons
- Les destinataires sont dédupliqués par ID utilisateur
- L'auteur du commentaire n'est jamais notifié
- Les notifications sont envoyées en file d'attente pour éviter le blocage

## Tests

### Script de test
Le fichier `test_notifications_discussions.php` permet de tester toutes les notifications :

```bash
cd govtrack-backend
php test_notifications_discussions.php
```

### Tests inclus
1. **Nouveau commentaire sur projet** : Vérifie les notifications au porteur et donneur d'ordre
2. **Réponse à commentaire de projet** : Vérifie la notification à l'auteur original
3. **Nouveau commentaire sur tâche** : Vérifie les notifications au responsable et porteur
4. **Réponse à commentaire de tâche** : Vérifie la notification à l'auteur original

### Vérification des emails
- Consulter les logs : `storage/logs/laravel.log`
- Vérifier la configuration SMTP dans `.env`
- Utiliser Mailtrap ou similaire pour les tests

## Utilisation

### Création d'un commentaire
```php
// Via l'API
POST /api/v1/projets/{projetId}/discussions
{
    "message": "Mon commentaire",
    "parent_id": null  // ou ID du commentaire parent pour une réponse
}
```

### Déclenchement automatique
Les notifications sont déclenchées automatiquement lors de la création de commentaires via les contrôleurs. Aucune action manuelle n'est requise.

## Maintenance

### Logs
- Les erreurs d'envoi d'email sont loggées automatiquement
- Consulter `storage/logs/laravel.log` pour le debugging

### Files d'attente
- Les emails sont envoyés en file d'attente
- Vérifier le statut des jobs : `php artisan queue:work`

### Configuration email
- Vérifier la configuration SMTP dans `.env`
- Tester l'envoi : `php artisan tinker` puis `Mail::raw('test', fn($m) => $m->to('test@example.com')->subject('test'))`

## Évolutions futures

### Mentions d'utilisateurs
- Détection des mentions `@utilisateur` dans les messages
- Notifications spécifiques aux utilisateurs mentionnés

### Notifications push
- Intégration avec les notifications push du navigateur
- Notifications en temps réel via WebSockets

### Préférences utilisateur
- Permettre aux utilisateurs de désactiver certaines notifications
- Configuration des préférences de notification par type 
