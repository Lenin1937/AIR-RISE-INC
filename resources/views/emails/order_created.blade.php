@extends('emails.layout')

@section('subject', 'Order #{{ $order->order_number }} Received')

@section('hero')
<div class="hero">
    <h1>Order Received ✓</h1>
    <p>We've received your order and will begin processing shortly</p>
</div>
@endsection

@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>Thank you for placing your order with CORPIUS! Here's a summary of what we received:</p>

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
    @if($order->state)
    <div class="row">
        <span class="label">State</span>
        <span class="value">{{ $order->state }}</span>
    </div>
    @endif
    <div class="row">
        <span class="label">Payment Method</span>
        <span class="value">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
    </div>
    <div class="row">
        <span class="label">Total Amount</span>
        <span class="value">${{ number_format($order->total_amount, 2) }}</span>
    </div>
    <div class="row">
        <span class="label">Status</span>
        <span class="value"><span class="badge badge-pending">Pending</span></span>
    </div>
    <div class="row">
        <span class="label">Date</span>
        <span class="value">{{ $order->created_at->format('F j, Y, g:i A') }} UTC</span>
    </div>
</div>

<div class="alert">
    <strong>Next Step:</strong> Our team will review your order within 1 business day. You'll receive an email as soon as processing begins.
</div>

<p style="text-align:center;">
    <a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn">View Order Details</a>
</p>
@endsection
