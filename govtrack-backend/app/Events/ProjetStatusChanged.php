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

class ProjetStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projet;
    public $changer;
    public $ancienStatut;
    public $nouveauStatut;
    public $commentaire;

    /**
     * Create a new event instance.
     */
    public function __construct(Projet $projet, User $changer, string $ancienStatut, string $nouveauStatut, ?string $commentaire = null)
    {
        $this->projet = $projet;
        $this->changer = $changer;
        $this->ancienStatut = $ancienStatut;
        $this->nouveauStatut = $nouveauStatut;
        $this->commentaire = $commentaire;
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
            'changer_id' => $this->changer->id,
            'changer_name' => $this->changer->prenom . ' ' . $this->changer->nom,
            'ancien_statut' => $this->ancienStatut,
            'nouveau_statut' => $this->nouveauStatut,
            'commentaire' => $this->commentaire,
            'changed_at' => now(),
        ];
    }
}
