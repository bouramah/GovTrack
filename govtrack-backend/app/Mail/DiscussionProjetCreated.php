<?php

namespace App\Mail;

use App\Models\DiscussionProjet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscussionProjetCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $discussion;
    public $author;
    public $recipient;
    public $isReply;
    public $parentMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(DiscussionProjet $discussion, User $author, User $recipient, bool $isReply = false, ?DiscussionProjet $parentMessage = null)
    {
        $this->discussion = $discussion;
        $this->author = $author;
        $this->recipient = $recipient;
        $this->isReply = $isReply;
        $this->parentMessage = $parentMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isReply
            ? 'Réponse à votre commentaire sur l\'instruction : ' . $this->discussion->projet->titre
            : 'Nouveau commentaire sur l\'instruction : ' . $this->discussion->projet->titre;

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.discussions.projet-created',
            with: [
                'discussion' => $this->discussion,
                'author' => $this->author,
                'recipient' => $this->recipient,
                'isReply' => $this->isReply,
                'parentMessage' => $this->parentMessage,
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
