<?php

namespace App\Mail;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TacheExecutionLevelUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $tache;
    public $updater;
    public $recipient;
    public $ancienNiveau;
    public $nouveauNiveau;
    public $commentaire;

    /**
     * Create a new message instance.
     */
    public function __construct(Tache $tache, User $updater, User $recipient, int $ancienNiveau, int $nouveauNiveau, ?string $commentaire = null)
    {
        $this->tache = $tache;
        $this->updater = $updater;
        $this->recipient = $recipient;
        $this->ancienNiveau = $ancienNiveau;
        $this->nouveauNiveau = $nouveauNiveau;
        $this->commentaire = $commentaire;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $progression = $this->nouveauNiveau - $this->ancienNiveau;
        $direction = $progression > 0 ? 'augmenté' : ($progression < 0 ? 'diminué' : 'maintenu');

        return new Envelope(
            subject: "Niveau d'exécution de tâche {$direction} : {$this->ancienNiveau}% → {$this->nouveauNiveau}%",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.taches.execution-level-updated',
            with: [
                'tache' => $this->tache,
                'updater' => $this->updater,
                'recipient' => $this->recipient,
                'ancienNiveau' => $this->ancienNiveau,
                'nouveauNiveau' => $this->nouveauNiveau,
                'commentaire' => $this->commentaire,
                'appName' => config('app.name', 'GovTrack'),
                'appUrl' => config('app.url', 'http://localhost'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
