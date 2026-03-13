@extends('emails.layout')
@section('subject', 'Payment Failed — Action Required for Order #{{ $order->order_number }}')
@section('hero')
<div class="hero" style="background:linear-gradient(135deg,#7c2d12 0%,#9a3412 100%);">
    <h1>Payment Failed — Action Required ⚠️</h1>
    <p>Order #{{ $order->order_number }}</p>
</div>
@endsection
@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>Unfortunately, we were unable to process your payment for the order below. Your order is currently on hold until payment is completed.</p>

<div class="info-card">
    <div class="row"><span class="label">Order Number</span><span class="value">{{ $order->order_number }}</span></div>
    <div class="row"><span class="label">Service</span><span class="value">{{ $order->service_type }}</span></div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row"><span class="label">Business Name</span><span class="value">{{ $order->entity_name }}</span></div>
    @endif
    <div class="row"><span class="label">Amount Due</span><span class="value" style="color:#dc2626;font-size:16px;">${{ number_format($order->total_amount, 2) }} USD</span></div>
    @if($failureReason)
    <div class="row"><span class="label">Failure Reason</span><span class="value">{{ $failureReason }}</span></div>
    @endif
    <div class="row"><span class="label">Status</span><span class="value"><span class="badge badge-cancel">Payment Failed</span></span></div>
</div>

<div class="alert" style="border-left-color:#ef4444;background:#fef2f2;">
    <strong>Common reasons for payment failure:</strong><br>
    Insufficient funds · Card declined · Incorrect card details · Bank security block
</div>

<p>Please update your payment method or retry using a different card to continue with your order.</p>
<p style="text-align:center;"><a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn" style="background:#ef4444;color:#fff;">Retry Payment Now</a></p>

<div class="divider"></div>
<p style="font-size:13px;color:#9aabbd;">If you continue to experience issues, contact us at <a href="mailto:support@corpius.net" style="color:#d4a02f;">support@corpius.net</a>.</p>
@endsection
