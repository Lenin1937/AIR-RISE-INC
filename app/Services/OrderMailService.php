<?php

namespace App\Services;

use App\Mail\DocumentsRequiredMail;
use App\Mail\OrderCreatedAdminMail;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderStatusChangedMail;
use App\Mail\PaymentConfirmedMail;
use App\Mail\PaymentFailedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderMailService
{
    private const ADMIN_EMAIL = 'support@corpius.net';

    /** Sent when a client places a new order (manual / bank-transfer payment). */
    public function sendOrderCreated(Order $order): void
    {
        $this->sendSafe(fn () => Mail::to($order->user->email)->queue(new OrderCreatedMail($order)));
        $this->sendSafe(fn () => Mail::to(self::ADMIN_EMAIL)->queue(new OrderCreatedAdminMail($order)));
    }

    /** Sent after payment is verified (Stripe success, processPayment, cryptoSuccess). */
    public function sendPaymentConfirmed(Order $order, ?string $paymentDate = null): void
    {
        $this->sendSafe(fn () => Mail::to($order->user->email)->queue(new PaymentConfirmedMail($order, $paymentDate)));
        $this->sendSafe(fn () => Mail::to(self::ADMIN_EMAIL)->queue(new OrderCreatedAdminMail($order)));
    }

    /** Sent when client submits USDC crypto payment with screenshot. Admin gets screenshot attached. */
    public function sendCryptoPaymentReceived(Order $order, ?string $screenshotPath = null): void
    {
        // Notify client
        $this->sendSafe(fn () => Mail::to($order->user->email)->queue(new PaymentConfirmedMail($order, now()->format('F j, Y, g:i A') . ' UTC')));
        // Notify admin with screenshot
        $this->sendSafe(fn () => Mail::to(self::ADMIN_EMAIL)->send(new OrderCreatedAdminMail($order, $screenshotPath)));
    }

    /** Sent when payment fails (Stripe webhook, declined card). */
    public function sendPaymentFailed(Order $order, ?string $failureReason = null): void
    {
        $this->sendSafe(fn () => Mail::to($order->user->email)->queue(new PaymentFailedMail($order, $failureReason)));
    }

    /** Sent when admin requests additional documents/information from the client. */
    public function sendDocumentsRequired(Order $order, ?string $notes = null): void
    {
        $this->sendSafe(fn () => Mail::to($order->user->email)->queue(new DocumentsRequiredMail($order, $notes)));
    }

    /**
     * Sent whenever an admin changes the order status.
     * Covers: in_progress, under_review, approved, filed, completed, cancelled, refunded, rejected.
     */
    public function sendStatusChanged(Order $order, ?string $notes = null): void
    {
        $notifiable = [
            'in_progress', 'under_review', 'approved', 'filed',
            'completed', 'cancelled', 'refunded',
        ];
        if (! in_array($order->status, $notifiable)) {
            return;
        }
        $this->sendSafe(fn () => Mail::to($order->user->email)->queue(new OrderStatusChangedMail($order, $notes)));
    }

    private function sendSafe(callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::error('OrderMailService: failed to queue email', ['error' => $e->getMessage()]);
        }
    }
}

