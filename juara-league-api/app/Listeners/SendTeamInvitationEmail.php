<?php

namespace App\Listeners;

use App\Events\TeamInvitationCreated;
use App\Mail\TeamInvitationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendTeamInvitationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TeamInvitationCreated $event): void
    {
        Mail::to($event->invitation->email)->send(new TeamInvitationMail($event->invitation));
    }
}
