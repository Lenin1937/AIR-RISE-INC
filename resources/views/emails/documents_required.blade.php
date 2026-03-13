@extends('emails.layout')
@section('subject', 'Action Required: Additional Information Needed — Order #{{ $order->order_number }}')
@section('hero')
<div class="hero" style="background:linear-gradient(135deg,#78350f 0%,#92400e 100%);">
    <h1>Additional Information Required ⚠️</h1>
    <p>Order #{{ $order->order_number }} — Action needed</p>
</div>
@endsection
@section('body')
<p>Hi <strong>{{ $order->user->first_name ?? $order->user->name }}</strong>,</p>
<p>Our team has reviewed your order and requires some additional information or documents to proceed. Please respond as soon as possible to avoid delays.</p>

<div class="info-card">
    <div class="row"><span class="label">Order Number</span><span class="value">{{ $order->order_number }}</span></div>
    <div class="row"><span class="label">Service</span><span class="value">{{ $order->service_type }}</span></div>
    @if($order->entity_name && $order->entity_name !== 'N/A')
    <div class="row"><span class="label">Business Name</span><span class="value">{{ $order->entity_name }}</span></div>
    @endif
    <div class="row"><span class="label">Status</span><span class="value"><span class="badge badge-pending">Action Required</span></span></div>
</div>

@if($notes)
<div class="alert" style="border-left-color:#ef4444;background:#fef2f2;">
    <strong>What we need from you:</strong><br>{{ $notes }}
</div>
@endif

<p>Please log in to your dashboard to upload any required documents or reply directly to this email with the requested information.</p>
<p style="text-align:center;"><a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="btn">Respond Now</a></p>

<div class="divider"></div>
<p style="font-size:13px;color:#9aabbd;">Delays in providing the required information may affect your estimated completion date.</p>
@endsection
