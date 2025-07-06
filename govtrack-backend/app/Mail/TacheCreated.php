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

class TacheCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $tache;
    public $creator;
    public $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct(Tache $tache, User $creator, User $recipient)
    {
        $this->tache = $tache;
        $this->creator = $creator;
        $this->recipient = $recipient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle tâche créée : ' . $this->tache->titre,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.taches.created',
            with: [
                'tache' => $this->tache,
                'creator' => $this->creator,
                'recipient' => $this->recipient,
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
