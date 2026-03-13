@extends('emails.layout')
@section('subject', 'Your Order Has Been Cancelled — #{{ $order->order_number }}')
@section('hero')
<div class="hero" style="background:linear-gradient(135deg,#374151 0%,#4b5563 100%);">
    <h1>Order Cancelled</h1>
    <p>Order #{{ $order->order_number }}</p>
</div>
@endsection
@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>Your order has been cancelled. Below is a summary of the order that was cancelled.</p>

<div class="info-card">
    <div class="row"><span class="label">Order Number</span><span class="value">{{ $order->order_number }}</span></div>
    <div class="row"><span class="label">Service</span><span class="value">{{ $order->service_type }}</span></div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row"><span class="label">Business Name</span><span class="value">{{ $order->entity_name }}</span></div>
    @endif
    <div class="row"><span class="label">Amount</span><span class="value">${{ number_format($order->total_amount, 2) }}</span></div>
    <div class="row"><span class="label">Status</span><span class="value"><span class="badge badge-cancel">Cancelled</span></span></div>
</div>

@if($notes)
<div class="alert"><strong>Reason:</strong> {{ $notes }}</div>
@endif

<p>If you did not request this cancellation or have any questions, please reach out to our support team.</p>
<p style="text-align:center;"><a href="mailto:support@corpius.net" class="btn">Contact Support</a></p>

<div class="divider"></div>
<p style="font-size:13px;color:#9aabbd;">If a refund is applicable, it will be processed within 5–10 business days to your original payment method.</p>
@endsection
