<?php

namespace App\Events;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TacheExecutionLevelUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tache;
    public $updater;
    public $ancienNiveau;
    public $nouveauNiveau;
    public $commentaire;

    /**
     * Create a new event instance.
     */
    public function __construct(Tache $tache, User $updater, int $ancienNiveau, int $nouveauNiveau, ?string $commentaire = null)
    {
        $this->tache = $tache;
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
            //
        ];
    }
}
