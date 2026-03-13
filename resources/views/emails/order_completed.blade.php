@extends('emails.layout')
@section('subject', 'Congratulations! Your Order Is Complete 🎉 — #{{ $order->order_number }}')
@section('hero')
<div class="hero" style="background:linear-gradient(135deg,#064e3b 0%,#047857 100%);">
    <h1>Your Order Is Complete! 🎉</h1>
    <p>Welcome to the world of US business ownership</p>
</div>
@endsection
@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>Congratulations! Your order has been fully completed. Everything is in order and your documents are ready.</p>

<div class="info-card">
    <div class="row"><span class="label">Order Number</span><span class="value">{{ $order->order_number }}</span></div>
    <div class="row"><span class="label">Service</span><span class="value">{{ $order->service_type }}</span></div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row"><span class="label">Business Name</span><span class="value">{{ $order->entity_name }}</span></div>
    @endif
    @if($order->state)
    <div class="row"><span class="label">State</span><span class="value">{{ $order->state }}</span></div>
    @endif
    <div class="row"><span class="label">Status</span><span class="value"><span class="badge badge-complete">Completed</span></span></div>
    <div class="row"><span class="label">Completed On</span><span class="value">{{ now()->format('F j, Y') }}</span></div>
</div>

@if($notes)
<div class="alert"><strong>Note from our team:</strong> {{ $notes }}</div>
@endif

<p>You can download all your official documents directly from your dashboard. If you need any further assistance, our team is always here to help.</p>
<p style="text-align:center;"><a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn">Download Your Documents</a></p>

<div class="divider"></div>
<p style="font-size:14px;text-align:center;color:#4a5568;">Need help with your next steps? Explore <a href="{{ config('app.url') }}" style="color:#d4a02f;">CORPIUS services</a> for EIN, registered agents, and compliance support.</p>
@endsection
