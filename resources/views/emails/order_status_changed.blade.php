@extends('emails.layout')

@section('subject', 'Order #{{ $order->order_number }} Status Update')

@section('hero')
<div class="hero" style="background: linear-gradient(135deg, {{ $heroBg }});">
    <h1>{{ $heroTitle }}</h1>
    <p>Order #{{ $order->order_number }}</p>
</div>
@endsection

@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>{{ $bodyText }}</p>

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
        <span class="label">New Status</span>
        <span class="value"><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></span>
    </div>
    @if($order->estimated_completion_date)
    <div class="row">
        <span class="label">Est. Completion</span>
        <span class="value">{{ \Carbon\Carbon::parse($order->estimated_completion_date)->format('F j, Y') }}</span>
    </div>
    @endif
</div>

@if($notes)
<div class="alert">
    <strong>Note from our team:</strong> {{ $notes }}
</div>
@endif

@if($order->status === 'cancelled' || $order->status === 'refunded')
<p>If you have any questions about this decision, please contact our support team.</p>
@else
<p>You can track the full progress of your order from your dashboard at any time.</p>
@endif

<p style="text-align:center;">
    <a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn">View Order Details</a>
</p>
@endsection
