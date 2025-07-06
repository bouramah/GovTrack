<?php

namespace App\Listeners;

use App\Events\ProjetExecutionLevelUpdated;
use App\Mail\ProjetExecutionLevelUpdated as ProjetExecutionLevelUpdatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

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

        // 1. Porteur du projet
        if ($projet->porteur && $projet->porteur->id !== $updater->id) {
            $recipients->push($projet->porteur);
        }

        // 2. Donneur d'ordre
        if ($projet->donneurOrdre && $projet->donneurOrdre->id !== $updater->id) {
            $recipients->push($projet->donneurOrdre);
        }

        // 3. Membres de l'équipe (si le projet a des tâches assignées)
        $membresEquipe = $projet->taches()
            ->with('assignations.utilisateur')
            ->get()
            ->flatMap(function ($tache) {
                return $tache->assignations->pluck('utilisateur');
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
        \Log::info('Notifications de mise à jour du niveau d\'exécution envoyées', [
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
        \Log::error('Échec de l\'envoi des notifications de mise à jour du niveau d\'exécution', [
            'projet_id' => $event->projet->id,
            'updater_id' => $event->updater->id,
            'ancien_niveau' => $event->ancienNiveau,
            'nouveau_niveau' => $event->nouveauNiveau,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
