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

class ProjetExecutionLevelUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $projet;
    public $updater;
    public $ancienNiveau;
    public $nouveauNiveau;
    public $commentaire;

    /**
     * Create a new event instance.
     */
    public function __construct(Projet $projet, User $updater, int $ancienNiveau, int $nouveauNiveau, ?string $commentaire = null)
    {
        $this->projet = $projet;
        $this->updater = $updater;
        $this->ancienNiveau = $ancienNiveau;
        $this->nouveauNiveau = $nouveauNiveau;
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
        $progression = $this->nouveauNiveau - $this->ancienNiveau;

        return [
            'projet_id' => $this->projet->id,
            'projet_titre' => $this->projet->titre,
            'updater_id' => $this->updater->id,
            'updater_name' => $this->updater->prenom . ' ' . $this->updater->nom,
            'ancien_niveau' => $this->ancienNiveau,
            'nouveau_niveau' => $this->nouveauNiveau,
            'progression' => $progression,
            'commentaire' => $this->commentaire,
            'updated_at' => now(),
        ];
    }
}
