<?php

namespace App\Listeners;

use App\Events\ParticipantManuallyRegistered;
use App\Mail\ManualRegistrationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendManualRegistrationEmail implements ShouldQueue
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
    public function handle(ParticipantManuallyRegistered $event): void
    {
        // Check if real email before sending
        if (!Str::endsWith($event->user->email, '@guest.juaraleague.com')) {
            Mail::to($event->user->email)->send(new ManualRegistrationMail(
                user: $event->user,
                tournament: $event->tournament,
                rawPassword: $event->rawPassword,
                teamName: $event->teamName
            ));
        }
    }
}
