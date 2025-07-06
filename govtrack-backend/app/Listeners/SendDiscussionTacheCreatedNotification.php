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
        $discussion->load(['tache.responsable', 'tache.projet.porteur', 'parent.user']);

        $recipients = collect();

        if ($isReply) {
            // Pour une réponse, notifier l'auteur du commentaire original
            if ($discussion->parent && $discussion->parent->user_id !== $author->id) {
                $recipients->push($discussion->parent->user);
            }
        } else {
            // Pour un nouveau commentaire, notifier :
            // 1. Le responsable de la tâche (sauf si c'est lui qui a posté)
            if ($discussion->tache->responsable && $discussion->tache->responsable->id !== $author->id) {
                $recipients->push($discussion->tache->responsable);
            }

            // 2. Le porteur du projet (sauf si c'est lui qui a posté)
            if ($discussion->tache->projet->porteur && $discussion->tache->projet->porteur->id !== $author->id) {
                $recipients->push($discussion->tache->projet->porteur);
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
