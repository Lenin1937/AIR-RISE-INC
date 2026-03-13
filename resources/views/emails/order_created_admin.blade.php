@extends('emails.layout')

@section('subject', 'New Order #{{ $order->order_number }} — Admin Alert')

@section('hero')
<div class="hero" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
    <h1>New Order Received 🔔</h1>
    <p>A client has submitted a new order</p>
</div>
@endsection

@section('body')
<p>A new order has been placed and requires your attention.</p>

<div class="info-card">
    <div class="row">
        <span class="label">Order Number</span>
        <span class="value">{{ $order->order_number }}</span>
    </div>
    <div class="row">
        <span class="label">Client</span>
        <span class="value">{{ $order->user->name }} ({{ $order->user->email }})</span>
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
        <span class="label">Submitted At</span>
        <span class="value">{{ $order->created_at->format('F j, Y, g:i A') }} UTC</span>
    </div>
</div>

<p style="text-align:center;">
    <a href="{{ config('app.url') }}/admin/orders/{{ $order->id }}" class="btn">Review Order in Admin Panel</a>
</p>
@endsection
