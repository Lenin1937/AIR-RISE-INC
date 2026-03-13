@extends('emails.layout')
@section('subject', 'Your Order Has Been Approved — #{{ $order->order_number }}')
@section('hero')
<div class="hero" style="background:linear-gradient(135deg,#064e3b 0%,#065f46 100%);">
    <h1>Your Order Has Been Approved ✅</h1>
    <p>Order #{{ $order->order_number }}</p>
</div>
@endsection
@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>Excellent news! Your order has been reviewed and approved by our team. We are now proceeding with the state filing process.</p>

<div class="info-card">
    <div class="row"><span class="label">Order Number</span><span class="value">{{ $order->order_number }}</span></div>
    <div class="row"><span class="label">Service</span><span class="value">{{ $order->service_type }}</span></div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row"><span class="label">Business Name</span><span class="value">{{ $order->entity_name }}</span></div>
    @endif
    @if($order->state)
    <div class="row"><span class="label">State</span><span class="value">{{ $order->state }}</span></div>
    @endif
    <div class="row"><span class="label">Status</span><span class="value"><span class="badge badge-approved">Approved</span></span></div>
    @if($order->estimated_completion_date)
    <div class="row"><span class="label">Est. Completion</span><span class="value">{{ \Carbon\Carbon::parse($order->estimated_completion_date)->format('F j, Y') }}</span></div>
    @endif
</div>

@if($notes)
<div class="alert"><strong>Note from our team:</strong> {{ $notes }}</div>
@endif

<p>We will notify you as soon as your documents have been filed with the state. You're one step closer to launching your US business!</p>
<p style="text-align:center;"><a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn">View Order Progress</a></p>
@endsection
