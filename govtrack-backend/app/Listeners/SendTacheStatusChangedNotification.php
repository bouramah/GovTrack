<?php

namespace App\Listeners;

use App\Events\TacheStatusChanged;
use App\Mail\TacheStatusChanged as TacheStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTacheStatusChangedNotification implements ShouldQueue
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
    public function handle(TacheStatusChanged $event): void
    {
        $tache = $event->tache;
        $changer = $event->changer;
        $ancienStatut = $event->ancienStatut;
        $nouveauStatut = $event->nouveauStatut;
        $commentaire = $event->commentaire;

        // Liste des utilisateurs à notifier
        $recipients = collect();

        // 1. Porteur du projet
        if ($tache->projet->porteur && $tache->projet->porteur->id !== $changer->id) {
            $recipients->push($tache->projet->porteur);
        }

        // 2. Responsable de la tâche (si différent de celui qui change)
        if ($tache->responsable && $tache->responsable->id !== $changer->id) {
            $recipients->push($tache->responsable);
        }

        // 3. Ordonnateur de l'instruction du projet (si différent de celui qui change)
        if ($tache->projet->donneurOrdre && $tache->projet->donneurOrdre->id !== $changer->id) {
            $recipients->push($tache->projet->donneurOrdre);
        }

        // Dédupliquer les destinataires
        $recipients = $recipients->unique('id');

        // Envoyer les emails
        foreach ($recipients as $recipient) {
            if ($recipient && $recipient->email) {
                Mail::to($recipient->email)
                    ->queue(new TacheStatusChangedMail(
                        $tache,
                        $changer,
                        $recipient,
                        $ancienStatut,
                        $nouveauStatut,
                        $commentaire
                    ));
            }
        }

        // Log pour debug
        Log::info('Notifications de changement de statut de tâche envoyées', [
            'tache_id' => $tache->id,
            'tache_titre' => $tache->titre,
            'projet_id' => $tache->projet->id,
            'projet_titre' => $tache->projet->titre,
            'changer_id' => $changer->id,
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => $nouveauStatut,
            'recipients_count' => $recipients->count(),
            'recipients' => $recipients->pluck('email')->toArray(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(TacheStatusChanged $event, \Throwable $exception): void
    {
        Log::error('Échec de l\'envoi des notifications de changement de statut de tâche', [
            'tache_id' => $event->tache->id,
            'changer_id' => $event->changer->id,
            'ancien_statut' => $event->ancienStatut,
            'nouveau_statut' => $event->nouveauStatut,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
