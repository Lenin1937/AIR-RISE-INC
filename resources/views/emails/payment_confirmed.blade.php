@extends('emails.layout')

@section('subject', 'Payment Confirmed — Order #{{ $order->order_number }}')

@section('hero')
<div class="hero" style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);">
    <h1>Payment Confirmed ✓</h1>
    <p>Your payment has been received and verified</p>
</div>
@endsection

@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>Great news — your payment has been confirmed and your order is now being processed by our team.</p>

<div class="info-card">
    <div class="row">
        <span class="label">Order Number</span>
        <span class="value">{{ $order->order_number }}</span>
    </div>
    <div class="row">
        <span class="label">Service</span>
        <span class="value">{{ $order->service_type }}</span>
    </div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row">
        <span class="label">Business Name</span>
        <span class="value">{{ $order->entity_name }}</span>
    </div>
    @endif
    <div class="row">
        <span class="label">Amount Paid</span>
        <span class="value" style="color:#065f46;font-size:16px;">${{ number_format($order->total_amount, 2) }} USD</span>
    </div>
    <div class="row">
        <span class="label">Payment Method</span>
        <span class="value">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
    </div>
    @if($paymentDate)
    <div class="row">
        <span class="label">Payment Date</span>
        <span class="value">{{ $paymentDate }}</span>
    </div>
    @endif
    <div class="row">
        <span class="label">Status</span>
        <span class="value"><span class="badge badge-progress">In Progress</span></span>
    </div>
</div>

<p>Our team is now actively working on your order. You'll receive updates at each milestone.</p>

<p style="text-align:center;">
    <a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn">Track Your Order</a>
</p>
@endsection
