@extends('emails.layout')
@section('subject', 'Your Order Is Now In Progress — #{{ $order->order_number }}')
@section('hero')
<div class="hero" style="background:linear-gradient(135deg,#0c3559 0%,#144272 100%);">
    <h1>We've Started Working on Your Order 🔄</h1>
    <p>Order #{{ $order->order_number }}</p>
</div>
@endsection
@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>Great news — our team has officially started processing your order. We're dedicated to completing it as efficiently as possible.</p>

<div class="info-card">
    <div class="row"><span class="label">Order Number</span><span class="value">{{ $order->order_number }}</span></div>
    <div class="row"><span class="label">Service</span><span class="value">{{ $order->service_type }}</span></div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row"><span class="label">Business Name</span><span class="value">{{ $order->entity_name }}</span></div>
    @endif
    @if($order->state)
    <div class="row"><span class="label">State</span><span class="value">{{ $order->state }}</span></div>
    @endif
    <div class="row"><span class="label">Status</span><span class="value"><span class="badge badge-progress">In Progress</span></span></div>
    @if($order->estimated_completion_date)
    <div class="row"><span class="label">Est. Completion</span><span class="value">{{ \Carbon\Carbon::parse($order->estimated_completion_date)->format('F j, Y') }}</span></div>
    @endif
</div>

@if($notes)
<div class="alert"><strong>Message from our team:</strong> {{ $notes }}</div>
@endif

<p>You can track your order status at any time from your dashboard. We'll email you at every major milestone.</p>
<p style="text-align:center;"><a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn">Track Your Order</a></p>
@endsection
