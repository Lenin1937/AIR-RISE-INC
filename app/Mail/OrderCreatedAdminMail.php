<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCreatedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly ?string $screenshotPath = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ADMIN] New Order #' . $this->order->order_number . ' — ' . $this->order->service_type
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order_created_admin');
    }

    public function attachments(): array
    {
        if ($this->screenshotPath && file_exists($this->screenshotPath)) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromPath($this->screenshotPath)
                    ->as('payment_screenshot.' . pathinfo($this->screenshotPath, PATHINFO_EXTENSION))
                    ->withMime(mime_content_type($this->screenshotPath) ?: 'image/png'),
            ];
        }
        return [];
    }
}
