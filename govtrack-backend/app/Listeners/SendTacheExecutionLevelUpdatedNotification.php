<?php

namespace App\Listeners;

use App\Events\TacheExecutionLevelUpdated;
use App\Mail\TacheExecutionLevelUpdated as TacheExecutionLevelUpdatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTacheExecutionLevelUpdatedNotification implements ShouldQueue
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
    public function handle(TacheExecutionLevelUpdated $event): void
    {
        $tache = $event->tache;
        $updater = $event->updater;
        $ancienNiveau = $event->ancienNiveau;
        $nouveauNiveau = $event->nouveauNiveau;
        $commentaire = $event->commentaire;

        // Liste des utilisateurs à notifier
        $recipients = collect();

        // 1. Porteur du projet
        if ($tache->projet->porteur && $tache->projet->porteur->id !== $updater->id) {
            $recipients->push($tache->projet->porteur);
        }

        // 2. Responsable de la tâche (si différent de celui qui met à jour)
        if ($tache->responsable && $tache->responsable->id !== $updater->id) {
            $recipients->push($tache->responsable);
        }

        // 3. Ordonnateur de l'instruction du projet (si différent de celui qui met à jour)
        if ($tache->projet->donneurOrdre && $tache->projet->donneurOrdre->id !== $updater->id) {
            $recipients->push($tache->projet->donneurOrdre);
        }

        // Dédupliquer les destinataires
        $recipients = $recipients->unique('id');

        // Envoyer les emails
        foreach ($recipients as $recipient) {
            if ($recipient && $recipient->email) {
                Mail::to($recipient->email)
                    ->queue(new TacheExecutionLevelUpdatedMail(
                        $tache,
                        $updater,
                        $recipient,
                        $ancienNiveau,
                        $nouveauNiveau,
                        $commentaire
                    ));
            }
        }

        // Log pour debug
        Log::info('Notifications de mise à jour du niveau d\'exécution de tâche envoyées', [
            'tache_id' => $tache->id,
            'tache_titre' => $tache->titre,
            'projet_id' => $tache->projet->id,
            'projet_titre' => $tache->projet->titre,
            'updater_id' => $updater->id,
            'ancien_niveau' => $ancienNiveau,
            'nouveau_niveau' => $nouveauNiveau,
            'progression' => $nouveauNiveau - $ancienNiveau,
            'recipients_count' => $recipients->count(),
            'recipients' => $recipients->pluck('email')->toArray(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(TacheExecutionLevelUpdated $event, \Throwable $exception): void
    {
        Log::error('Échec de l\'envoi des notifications de mise à jour du niveau d\'exécution de tâche', [
            'tache_id' => $event->tache->id,
            'updater_id' => $event->updater->id,
            'ancien_niveau' => $event->ancienNiveau,
            'nouveau_niveau' => $event->nouveauNiveau,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
