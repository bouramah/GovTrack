<?php

namespace App\Mail;

use App\Models\Projet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjetExecutionLevelUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $projet;
    public $updater;
    public $recipient;
    public $ancienNiveau;
    public $nouveauNiveau;
    public $commentaire;

    /**
     * Create a new message instance.
     */
    public function __construct(Projet $projet, User $updater, User $recipient, int $ancienNiveau, int $nouveauNiveau, ?string $commentaire = null)
    {
        $this->projet = $projet;
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
        $progressionText = $progression > 0 ? "+{$progression}%" : "{$progression}%";

        return new Envelope(
            subject: "Niveau d'exécution mis à jour : {$this->projet->titre} ({$this->ancienNiveau}% → {$this->nouveauNiveau}%)",
            tags: ['projet', 'execution', 'progression'],
            metadata: [
                'projet_id' => $this->projet->id,
                'updater_id' => $this->updater->id,
                'recipient_id' => $this->recipient->id,
                'ancien_niveau' => $this->ancienNiveau,
                'nouveau_niveau' => $this->nouveauNiveau,
                'progression' => $progression,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $progression = $this->nouveauNiveau - $this->ancienNiveau;
        $progressionText = $progression > 0 ? "+{$progression}%" : "{$progression}%";

        return new Content(
            view: 'emails.projets.execution-level-updated',
            with: [
                'projet' => $this->projet,
                'updater' => $this->updater,
                'recipient' => $this->recipient,
                'ancienNiveau' => $this->ancienNiveau,
                'nouveauNiveau' => $this->nouveauNiveau,
                'progression' => $progression,
                'progressionText' => $progressionText,
                'commentaire' => $this->commentaire,
                'appName' => config('app.name'),
                'appUrl' => config('app.url'),
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
