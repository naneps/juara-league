<?php

namespace App\Events;

use App\Models\User;
use App\Models\Tournament;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantManuallyRegistered
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public Tournament $tournament,
        public string $rawPassword,
        public ?string $teamName = null
    ) {
    }
}
