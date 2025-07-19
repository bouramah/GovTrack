<?php

namespace App\Listeners;

use App\Events\ProjetExecutionLevelUpdated;
use App\Mail\ProjetExecutionLevelUpdated as ProjetExecutionLevelUpdatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendProjetExecutionLevelUpdatedNotification implements ShouldQueue
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
    public function handle(ProjetExecutionLevelUpdated $event): void
    {
        $projet = $event->projet;
        $updater = $event->updater;
        $ancienNiveau = $event->ancienNiveau;
        $nouveauNiveau = $event->nouveauNiveau;
        $commentaire = $event->commentaire;

        // Liste des utilisateurs à notifier
        $recipients = collect();

        // 1. Tous les porteurs du projet
        if ($projet->porteurs && $projet->porteurs->count() > 0) {
            $porteurs = $projet->porteurs->filter(function ($porteur) use ($updater) {
                return $porteur->id !== $updater->id;
            });
            $recipients = $recipients->merge($porteurs);
        }

        // 2. Ordonnateur de l'instruction
        if ($projet->donneurOrdre && $projet->donneurOrdre->id !== $updater->id) {
            $recipients->push($projet->donneurOrdre);
        }

        // 3. Membres de l'équipe (responsables des tâches du projet)
        $membresEquipe = $projet->taches()
            ->with('responsables')
            ->get()
            ->flatMap(function ($tache) {
                return $tache->responsables;
            })
            ->unique('id')
            ->filter(function ($user) use ($updater) {
                return $user && $user->id !== $updater->id;
            });

        $recipients = $recipients->merge($membresEquipe)->unique('id');

        // 4. Envoyer les emails
        foreach ($recipients as $recipient) {
            if ($recipient && $recipient->email) {
                Mail::to($recipient->email)
                    ->queue(new ProjetExecutionLevelUpdatedMail(
                        $projet,
                        $updater,
                        $recipient,
                        $ancienNiveau,
                        $nouveauNiveau,
                        $commentaire
                    ));
            }
        }

        // Log pour debug
        Log::info('Notifications de mise à jour du niveau d\'exécution d\'instruction envoyées', [
            'projet_id' => $projet->id,
            'projet_titre' => $projet->titre,
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
    public function failed(ProjetExecutionLevelUpdated $event, \Throwable $exception): void
    {
        Log::error('Échec de l\'envoi des notifications de mise à jour du niveau d\'exécution d\'instruction', [
            'projet_id' => $event->projet->id,
            'updater_id' => $event->updater->id,
            'ancien_niveau' => $event->ancienNiveau,
            'nouveau_niveau' => $event->nouveauNiveau,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
