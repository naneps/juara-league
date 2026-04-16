<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Tournament;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManualRegistrationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public Tournament $tournament,
        public string $rawPassword,
        public ?string $teamName = null
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been registered for: " . $this->tournament->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $loginUrl = "{$frontendUrl}/login";

        return new Content(
            view: 'emails.manual_registration',
            with: [
                'user' => $this->user,
                'tournament' => $this->tournament,
                'teamName' => $this->teamName,
                'rawPassword' => $this->rawPassword,
                'loginUrl' => $loginUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
