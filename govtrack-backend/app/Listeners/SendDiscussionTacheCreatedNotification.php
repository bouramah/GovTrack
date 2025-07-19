<?php

namespace App\Listeners;

use App\Events\DiscussionTacheCreated;
use App\Mail\DiscussionTacheCreated as DiscussionTacheCreatedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendDiscussionTacheCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DiscussionTacheCreated $event): void
    {
        $discussion = $event->discussion;
        $author = $event->author;
        $isReply = $event->isReply;

        // Charger les relations nécessaires
        $discussion->load(['tache.responsables', 'tache.projet.porteurs', 'parent.user']);

        $recipients = collect();

        if ($isReply) {
            // Pour une réponse, notifier l'auteur du commentaire original
            if ($discussion->parent && $discussion->parent->user_id !== $author->id) {
                $recipients->push($discussion->parent->user);
            }
        } else {
            // Pour un nouveau commentaire, notifier :
            // 1. Tous les responsables de la tâche (sauf si c'est eux qui ont posté)
            if ($discussion->tache->responsables && $discussion->tache->responsables->count() > 0) {
                $responsables = $discussion->tache->responsables->filter(function ($responsable) use ($author) {
                    return $responsable->id !== $author->id;
                });
                $recipients = $recipients->merge($responsables);
            }

            // 2. Tous les porteurs du projet (sauf si c'est eux qui ont posté)
            if ($discussion->tache->projet->porteurs && $discussion->tache->projet->porteurs->count() > 0) {
                $porteurs = $discussion->tache->projet->porteurs->filter(function ($porteur) use ($author) {
                    return $porteur->id !== $author->id;
                });
                $recipients = $recipients->merge($porteurs);
            }
        }

        // Éviter les doublons
        $recipients = $recipients->unique('id');

        // Envoyer les emails
        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)
                ->queue(new DiscussionTacheCreatedMail(
                    $discussion,
                    $author,
                    $recipient,
                    $isReply,
                    $discussion->parent
                ));
        }
    }
}
