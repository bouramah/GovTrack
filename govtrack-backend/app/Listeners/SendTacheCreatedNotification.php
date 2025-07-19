<?php

namespace App\Listeners;

use App\Events\TacheCreated;
use App\Mail\TacheCreated as TacheCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTacheCreatedNotification implements ShouldQueue
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
    public function handle(TacheCreated $event): void
    {
        $tache = $event->tache;
        $creator = $event->creator;

        // Liste des utilisateurs à notifier
        $recipients = collect();

        // 1. Tous les porteurs du projet
        if ($tache->projet->porteurs && $tache->projet->porteurs->count() > 0) {
            $porteurs = $tache->projet->porteurs->filter(function ($porteur) use ($creator) {
                return $porteur->id !== $creator->id;
            });
            $recipients = $recipients->merge($porteurs);
        }

        // 2. Tous les responsables de la tâche (si différents du créateur)
        if ($tache->responsables && $tache->responsables->count() > 0) {
            $responsables = $tache->responsables->filter(function ($responsable) use ($creator) {
                return $responsable->id !== $creator->id;
            });
            $recipients = $recipients->merge($responsables);
        }

        // 3. Ordonnateur de l'instruction du projet (si différent du créateur)
        if ($tache->projet->donneurOrdre && $tache->projet->donneurOrdre->id !== $creator->id) {
            $recipients->push($tache->projet->donneurOrdre);
        }

        // Dédupliquer les destinataires
        $recipients = $recipients->unique('id');

        // Envoyer les emails
        foreach ($recipients as $recipient) {
            if ($recipient && $recipient->email) {
                Mail::to($recipient->email)
                    ->queue(new TacheCreatedMail($tache, $creator, $recipient));
            }
        }

        // Log pour debug
        Log::info('Notifications de création de tâche envoyées', [
            'tache_id' => $tache->id,
            'tache_titre' => $tache->titre,
            'projet_id' => $tache->projet->id,
            'projet_titre' => $tache->projet->titre,
            'creator_id' => $creator->id,
            'recipients_count' => $recipients->count(),
            'recipients' => $recipients->pluck('email')->toArray(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(TacheCreated $event, \Throwable $exception): void
    {
        Log::error('Échec de l\'envoi des notifications de création de tâche', [
            'tache_id' => $event->tache->id,
            'creator_id' => $event->creator->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
