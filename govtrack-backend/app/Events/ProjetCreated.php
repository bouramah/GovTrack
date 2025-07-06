<?php

namespace App\Events;

use App\Models\Projet;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjetCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projet;
    public $creator;

    /**
     * Create a new event instance.
     */
    public function __construct(Projet $projet, User $creator)
    {
        $this->projet = $projet;
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
            new PrivateChannel('projets'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'projet_id' => $this->projet->id,
            'projet_titre' => $this->projet->titre,
            'creator_id' => $this->creator->id,
            'creator_name' => $this->creator->prenom . ' ' . $this->creator->nom,
            'created_at' => $this->projet->created_at,
        ];
    }
}
