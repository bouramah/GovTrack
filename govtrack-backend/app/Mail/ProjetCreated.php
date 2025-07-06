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

class ProjetCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $projet;
    public $creator;
    public $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(Projet $projet, User $creator, User $recipient)
    {
        $this->projet = $projet;
        $this->creator = $creator;
        $this->recipient = $recipient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau projet créé : ' . $this->projet->titre,
            tags: ['projet', 'creation'],
            metadata: [
                'projet_id' => $this->projet->id,
                'creator_id' => $this->creator->id,
                'recipient_id' => $this->recipient->id,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.projets.created',
            with: [
                'projet' => $this->projet,
                'creator' => $this->creator,
                'recipient' => $this->recipient,
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
