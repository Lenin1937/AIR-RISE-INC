<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $heroTitle;
    public readonly string $heroBg;
    public readonly string $bodyText;
    public readonly string $statusLabel;
    public readonly string $badgeClass;
    public readonly ?string $notes;

    public function __construct(public readonly Order $order, ?string $notes = null)
    {
        $this->notes = $notes;

        [$this->heroTitle, $this->heroBg, $this->bodyText, $this->statusLabel, $this->badgeClass]
            = $this->resolveStatusMeta($order->status);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order #' . $this->order->order_number . ' — ' . $this->statusLabel
        );
    }

    public function content(): Content
    {
        $view = match ($this->order->status) {
            'in_progress'  => 'emails.order_in_progress',
            'under_review' => 'emails.order_under_review',
            'approved'     => 'emails.order_approved',
            'filed'        => 'emails.order_filed',
            'completed'    => 'emails.order_completed',
            'cancelled'    => 'emails.order_cancelled',
            'refunded'     => 'emails.order_cancelled',
            default        => 'emails.order_status_changed',
        };

        return new Content(view: $view);
    }

    public function attachments(): array
    {
        return [];
    }

    private function resolveStatusMeta(string $status): array
    {
        return match ($status) {
            'in_progress' => [
                'Your Order Is Being Processed 🔄',
                '#0c3559 0%, #144272 100%',
                'Our team has started working on your order. We\'ll keep you updated at every step.',
                'In Progress',
                'badge-progress',
            ],
            'under_review' => [
                'Your Order Is Under Review 🔍',
                '#1e3a5f 0%, #1a3a5c 100%',
                'Your order is currently under internal review. This usually takes 1–2 business days.',
                'Under Review',
                'badge-progress',
            ],
            'approved' => [
                'Your Order Has Been Approved ✅',
                '#064e3b 0%, #065f46 100%',
                'Congratulations! Your order has been approved and we are now proceeding with filing.',
                'Approved',
                'badge-approved',
            ],
            'filed' => [
                'Your Formation Has Been Filed 📋',
                '#4c1d95 0%, #5b21b6 100%',
                'Your business formation documents have been filed with the state. Processing typically takes 5–15 business days.',
                'Filed',
                'badge-filed',
            ],
            'completed' => [
                'Your Order Is Complete 🎉',
                '#064e3b 0%, #047857 100%',
                'Your order has been fully completed! All documents are ready. Welcome to the world of US business!',
                'Completed',
                'badge-complete',
            ],
            'cancelled' => [
                'Order Cancelled',
                '#7f1d1d 0%, #991b1b 100%',
                'Your order has been cancelled. If you believe this is a mistake, please contact our support team.',
                'Cancelled',
                'badge-cancel',
            ],
            'refunded' => [
                'Order Refunded',
                '#7f1d1d 0%, #991b1b 100%',
                'A refund has been issued for your order. Please allow 5–10 business days for it to appear in your account.',
                'Refunded',
                'badge-cancel',
            ],
            default => [
                'Order Status Update',
                '#0b1e33 0%, #12294a 100%',
                'There has been an update to your order.',
                ucfirst(str_replace('_', ' ', $status)),
                'badge-pending',
            ],
        };
    }
}
