<?php

namespace App\Listeners;

use App\Events\ProjetStatusChanged;
use App\Mail\ProjetStatusChanged as ProjetStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendProjetStatusChangedNotification implements ShouldQueue
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
    public function handle(ProjetStatusChanged $event): void
    {
        $projet = $event->projet;
        $changer = $event->changer;
        $ancienStatut = $event->ancienStatut;
        $nouveauStatut = $event->nouveauStatut;
        $commentaire = $event->commentaire;

        // Liste des utilisateurs à notifier
        $recipients = collect();

        // 1. Porteur du projet
        if ($projet->porteur && $projet->porteur->id !== $changer->id) {
            $recipients->push($projet->porteur);
        }

        // 2. Donneur d'ordre
        if ($projet->donneurOrdre && $projet->donneurOrdre->id !== $changer->id) {
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
            ->filter(function ($user) use ($changer) {
                return $user && $user->id !== $changer->id;
            });

        $recipients = $recipients->merge($membresEquipe)->unique('id');

        // 4. Envoyer les emails
        foreach ($recipients as $recipient) {
            if ($recipient && $recipient->email) {
                Mail::to($recipient->email)
                    ->queue(new ProjetStatusChangedMail(
                        $projet,
                        $changer,
                        $recipient,
                        $ancienStatut,
                        $nouveauStatut,
                        $commentaire
                    ));
            }
        }

        // Log pour debug
        \Log::info('Notifications de changement de statut d\'instruction envoyées', [
            'projet_id' => $projet->id,
            'projet_titre' => $projet->titre,
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
    public function failed(ProjetStatusChanged $event, \Throwable $exception): void
    {
        \Log::error('Échec de l\'envoi des notifications de changement de statut d\'instruction', [
            'projet_id' => $event->projet->id,
            'changer_id' => $event->changer->id,
            'ancien_statut' => $event->ancienStatut,
            'nouveau_statut' => $event->nouveauStatut,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
