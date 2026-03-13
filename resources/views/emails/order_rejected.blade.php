@extends('emails.layout')
@section('subject', 'Your Order Has Been Rejected — #{{ $order->order_number }}')
@section('hero')
<div class="hero" style="background:linear-gradient(135deg,#7f1d1d 0%,#991b1b 100%);">
    <h1>Order Rejected</h1>
    <p>Order #{{ $order->order_number }}</p>
</div>
@endsection
@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>We regret to inform you that your order has been rejected after internal review. We understand this is not the news you were hoping for.</p>

<div class="info-card">
    <div class="row"><span class="label">Order Number</span><span class="value">{{ $order->order_number }}</span></div>
    <div class="row"><span class="label">Service</span><span class="value">{{ $order->service_type }}</span></div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row"><span class="label">Business Name</span><span class="value">{{ $order->entity_name }}</span></div>
    @endif
    <div class="row"><span class="label">Status</span><span class="value"><span class="badge badge-cancel">Rejected</span></span></div>
</div>

@if($notes)
<div class="alert" style="border-left-color:#ef4444;background:#fef2f2;">
    <strong>Reason for rejection:</strong><br>{{ $notes }}
</div>
@endif

<p>If you believe this rejection was made in error, or if you'd like to discuss your options, please contact our support team at <a href="mailto:support@corpius.net" style="color:#d4a02f;">support@corpius.net</a>.</p>

<p style="text-align:center;">
    <a href="mailto:support@corpius.net" class="btn" style="background:#ef4444;color:#fff;">Contact Support</a>
</p>

<div class="divider"></div>
<p style="font-size:13px;color:#9aabbd;">If a refund is applicable, it will be processed within 5–10 business days.</p>
@endsection
