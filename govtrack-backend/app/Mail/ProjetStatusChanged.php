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

class ProjetStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $projet;
    public $changer;
    public $recipient;
    public $ancienStatut;
    public $nouveauStatut;
    public $commentaire;

    /**
     * Create a new message instance.
     */
    public function __construct(Projet $projet, User $changer, User $recipient, string $ancienStatut, string $nouveauStatut, ?string $commentaire = null)
    {
        $this->projet = $projet;
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
        $statuts = [
            'a_faire' => 'À faire',
            'en_cours' => 'En cours',
            'bloque' => 'Bloqué',
            'demande_de_cloture' => 'Demande de clôture',
            'termine' => 'Terminé'
        ];

        $ancienLibelle = $statuts[$this->ancienStatut] ?? $this->ancienStatut;
        $nouveauLibelle = $statuts[$this->nouveauStatut] ?? $this->nouveauStatut;

        return new Envelope(
            subject: "Statut de l'instruction modifié : {$this->projet->titre} ({$ancienLibelle} → {$nouveauLibelle})",
            tags: ['instruction', 'statut', 'modification'],
            metadata: [
                'projet_id' => $this->projet->id,
                'changer_id' => $this->changer->id,
                'recipient_id' => $this->recipient->id,
                'ancien_statut' => $this->ancienStatut,
                'nouveau_statut' => $this->nouveauStatut,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.projets.status-changed',
            with: [
                'projet' => $this->projet,
                'changer' => $this->changer,
                'recipient' => $this->recipient,
                'ancienStatut' => $this->ancienStatut,
                'nouveauStatut' => $this->nouveauStatut,
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
