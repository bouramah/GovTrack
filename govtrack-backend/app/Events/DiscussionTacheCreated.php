<?php

namespace App\Events;

use App\Models\DiscussionTache;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscussionTacheCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $discussion;
    public $author;
    public $isReply;

    /**
     * Create a new event instance.
     */
    public function __construct(DiscussionTache $discussion, User $author, bool $isReply = false)
    {
        $this->discussion = $discussion;
        $this->author = $author;
        $this->isReply = $isReply;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            //
        ];
    }
}
