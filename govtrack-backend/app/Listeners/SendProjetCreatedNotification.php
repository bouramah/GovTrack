<?php

namespace App\Listeners;

use App\Events\ProjetCreated;
use App\Mail\ProjetCreated as ProjetCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendProjetCreatedNotification implements ShouldQueue
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
    public function handle(ProjetCreated $event): void
    {
        $projet = $event->projet;
        $creator = $event->creator;

        // Liste des utilisateurs à notifier
        $recipients = collect();

        // 1. Tous les porteurs du projet
        if ($projet->porteurs && $projet->porteurs->count() > 0) {
            $porteurs = $projet->porteurs->filter(function ($porteur) use ($creator) {
                return $porteur->id !== $creator->id;
            });
            $recipients = $recipients->merge($porteurs);
        }

        // 2. Ordonnateur de l'instruction
        if ($projet->donneurOrdre && $projet->donneurOrdre->id !== $creator->id) {
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
            ->filter(function ($user) use ($creator) {
                return $user && $user->id !== $creator->id;
            });

        $recipients = $recipients->merge($membresEquipe)->unique('id');

        // 4. Envoyer les emails
        foreach ($recipients as $recipient) {
            if ($recipient && $recipient->email) {
                Mail::to($recipient->email)
                    ->queue(new ProjetCreatedMail($projet, $creator, $recipient));
            }
        }

        // Log pour debug
        Log::info('Notifications de création d\'instruction envoyées', [
            'projet_id' => $projet->id,
            'projet_titre' => $projet->titre,
            'creator_id' => $creator->id,
            'recipients_count' => $recipients->count(),
            'recipients' => $recipients->pluck('email')->toArray(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(ProjetCreated $event, \Throwable $exception): void
    {
        Log::error('Échec de l\'envoi des notifications de création de projet', [
            'projet_id' => $event->projet->id,
            'creator_id' => $event->creator->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
