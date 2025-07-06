<?php

namespace App\Events;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TacheStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tache;
    public $changer;
    public $ancienStatut;
    public $nouveauStatut;
    public $commentaire;

    /**
     * Create a new event instance.
     */
    public function __construct(Tache $tache, User $changer, string $ancienStatut, string $nouveauStatut, ?string $commentaire = null)
    {
        $this->tache = $tache;
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
            //
        ];
    }
}
