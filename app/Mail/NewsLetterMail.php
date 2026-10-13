<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsLetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data = [];

    /**
     * Create a new message instance.
     */
    public function __construct(array $data=[])
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    final public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'News Letter Mail from ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    final public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.newsletter',
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
