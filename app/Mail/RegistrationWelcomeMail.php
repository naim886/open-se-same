<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;

    /**
     * Create a new message instance.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the message envelope.
     */
    final public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Registration Welcome ' . $this->name . ' -  ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    final public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome.welcome',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    final public function attachments(): array
    {
        return [];
    }
}
