<?php

namespace App\Events;

use App\Models\TeamInvitation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamInvitationCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public TeamInvitation $invitation
    ) {
    }
}
