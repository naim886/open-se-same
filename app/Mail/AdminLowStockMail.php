<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminLowStockMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Product $product, public int $threshold)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Low stock: ' . $this->product->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.inventory.low-stock');
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
