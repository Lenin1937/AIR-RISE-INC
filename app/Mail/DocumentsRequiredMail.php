<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentsRequiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly ?string $notes = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Action Required: Additional Information Needed — Order #' . $this->order->order_number
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.documents_required');
    }

    public function attachments(): array
    {
        return [];
    }
}
