<?php

namespace App\Listeners;

use App\Events\DiscussionProjetCreated;
use App\Mail\DiscussionProjetCreated as DiscussionProjetCreatedMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendDiscussionProjetCreatedNotification implements ShouldQueue
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
    public function handle(DiscussionProjetCreated $event): void
    {
        $discussion = $event->discussion;
        $author = $event->author;
        $isReply = $event->isReply;

        // Charger les relations nécessaires
        $discussion->load(['projet.porteurs', 'projet.donneurOrdre', 'projet.taches.responsables', 'parent.user']);

        $recipients = collect();

        if ($isReply) {
            // Pour une réponse, notifier l'auteur du commentaire original
            if ($discussion->parent && $discussion->parent->user_id !== $author->id) {
                $recipients->push($discussion->parent->user);
            }
        } else {
            // Pour un nouveau commentaire, notifier :
            // 1. Tous les porteurs du projet (sauf si c'est eux qui ont posté)
            if ($discussion->projet->porteurs && $discussion->projet->porteurs->count() > 0) {
                $porteurs = $discussion->projet->porteurs->filter(function ($porteur) use ($author) {
                    return $porteur->id !== $author->id;
                });
                $recipients = $recipients->merge($porteurs);
            }

            // 2. Le donneur d'ordre (sauf si c'est lui qui a posté)
            if ($discussion->projet->donneurOrdre && $discussion->projet->donneurOrdre->id !== $author->id) {
                $recipients->push($discussion->projet->donneurOrdre);
            }

            // 3. L'équipe (tous les responsables des tâches du projet)
            $discussion->projet->taches->each(function ($tache) use ($recipients, $author) {
                if ($tache->responsables && $tache->responsables->count() > 0) {
                    $responsables = $tache->responsables->filter(function ($responsable) use ($author) {
                        return $responsable->id !== $author->id;
                    });
                    $recipients = $recipients->merge($responsables);
                }
            });
        }

        // Éviter les doublons
        $recipients = $recipients->unique('id');

        // Envoyer les emails
        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)
                ->queue(new DiscussionProjetCreatedMail(
                    $discussion,
                    $author,
                    $recipient,
                    $isReply,
                    $discussion->parent
                ));
        }
    }
}
