<?php

namespace App\Events;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TacheCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tache;
    public $creator;

    /**
     * Create a new event instance.
     */
    public function __construct(Tache $tache, User $creator)
    {
        $this->tache = $tache;
        $this->creator = $creator;
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
