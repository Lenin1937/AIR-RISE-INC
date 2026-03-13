@extends('emails.layout')
@section('subject', 'Your Order Is Under Review — #{{ $order->order_number }}')
@section('hero')
<div class="hero" style="background:linear-gradient(135deg,#1e3a5f 0%,#1a3a5c 100%);">
    <h1>Your Order Is Under Review 🔍</h1>
    <p>Order #{{ $order->order_number }}</p>
</div>
@endsection
@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>Your order is currently being reviewed by our compliance and legal team. This step ensures everything is accurate and in order before we proceed with filing.</p>

<div class="info-card">
    <div class="row"><span class="label">Order Number</span><span class="value">{{ $order->order_number }}</span></div>
    <div class="row"><span class="label">Service</span><span class="value">{{ $order->service_type }}</span></div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row"><span class="label">Business Name</span><span class="value">{{ $order->entity_name }}</span></div>
    @endif
    <div class="row"><span class="label">Status</span><span class="value"><span class="badge badge-progress">Under Review</span></span></div>
    @if($order->estimated_completion_date)
    <div class="row"><span class="label">Est. Completion</span><span class="value">{{ \Carbon\Carbon::parse($order->estimated_completion_date)->format('F j, Y') }}</span></div>
    @endif
</div>

@if($notes)
<div class="alert"><strong>Note from our team:</strong> {{ $notes }}</div>
@endif

<p>Review typically takes <strong>1–2 business days</strong>. You'll receive an email as soon as the review is complete.</p>
<p style="text-align:center;"><a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn">View Order Details</a></p>
@endsection
