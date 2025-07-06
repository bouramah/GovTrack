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

class TacheStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $tache;
    public $changer;
    public $recipient;
    public $ancienStatut;
    public $nouveauStatut;
    public $commentaire;

    /**
     * Create a new message instance.
     */
    public function __construct(Tache $tache, User $changer, User $recipient, string $ancienStatut, string $nouveauStatut, ?string $commentaire = null)
    {
        $this->tache = $tache;
        $this->changer = $changer;
        $this->recipient = $recipient;
        $this->ancienStatut = $ancienStatut;
        $this->nouveauStatut = $nouveauStatut;
        $this->commentaire = $commentaire;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $ancienLibelle = Tache::STATUTS[$this->ancienStatut] ?? $this->ancienStatut;
        $nouveauLibelle = Tache::STATUTS[$this->nouveauStatut] ?? $this->nouveauStatut;

        return new Envelope(
            subject: "Statut de tâche modifié : {$ancienLibelle} → {$nouveauLibelle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.taches.status-changed',
            with: [
                'tache' => $this->tache,
                'changer' => $this->changer,
                'recipient' => $this->recipient,
                'ancienStatut' => $this->ancienStatut,
                'nouveauStatut' => $this->nouveauStatut,
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
