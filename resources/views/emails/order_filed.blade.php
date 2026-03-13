@extends('emails.layout')
@section('subject', 'Your Formation Has Been Filed — Order #{{ $order->order_number }}')
@section('hero')
<div class="hero" style="background:linear-gradient(135deg,#4c1d95 0%,#5b21b6 100%);">
    <h1>Your Formation Has Been Filed 📋</h1>
    <p>Order #{{ $order->order_number }}</p>
</div>
@endsection
@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>We have officially submitted your business formation documents to the state. Your application is now in the state's processing queue.</p>

<div class="info-card">
    <div class="row"><span class="label">Order Number</span><span class="value">{{ $order->order_number }}</span></div>
    <div class="row"><span class="label">Service</span><span class="value">{{ $order->service_type }}</span></div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row"><span class="label">Business Name</span><span class="value">{{ $order->entity_name }}</span></div>
    @endif
    @if($order->state)
    <div class="row"><span class="label">Filed In</span><span class="value">{{ $order->state }}</span></div>
    @endif
    @if($order->state_filing_date)
    <div class="row"><span class="label">Filing Date</span><span class="value">{{ \Carbon\Carbon::parse($order->state_filing_date)->format('F j, Y') }}</span></div>
    @endif
    <div class="row"><span class="label">Status</span><span class="value"><span class="badge badge-filed">Filed</span></span></div>
    @if($order->estimated_completion_date)
    <div class="row"><span class="label">Est. Completion</span><span class="value">{{ \Carbon\Carbon::parse($order->estimated_completion_date)->format('F j, Y') }}</span></div>
    @endif
</div>

@if($notes)
<div class="alert"><strong>Note from our team:</strong> {{ $notes }}</div>
@endif

<div class="alert" style="border-left-color:#7c3aed;background:#f5f3ff;">
    <strong>What happens next?</strong> State processing typically takes <strong>5–15 business days</strong> (or 1–3 business days for expedited). We'll notify you the moment your articles are approved.
</div>

<p style="text-align:center;"><a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn">Track Your Filing</a></p>
@endsection
