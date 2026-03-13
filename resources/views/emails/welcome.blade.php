@extends('emails.layout')

@section('subject', 'Welcome to CORPIUS!')

@section('hero')
<div class="hero">
    <h1>Welcome to CORPIUS! 🎉</h1>
    <p>Your account has been created successfully</p>
</div>
@endsection

@section('body')
<p>Hi <strong>{{ $user->first_name ?? $user->name }}</strong>,</p>
<p>Welcome aboard! Your CORPIUS account is ready. We're here to help you form your US business, handle taxes, and navigate immigration services — all from one place.</p>

<div class="info-card">
    <div class="row">
        <span class="label">Account Email</span>
        <span class="value">{{ $user->email }}</span>
    </div>
    <div class="row">
        <span class="label">Username</span>
        <span class="value">{{ $user->username ?? '—' }}</span>
    </div>
    <div class="row">
        <span class="label">Member Since</span>
        <span class="value">{{ $user->created_at->format('F j, Y') }}</span>
    </div>
</div>

<p>To get started, create your first order from your dashboard.</p>

<p style="text-align:center;">
    <a href="{{ config('app.url') }}/orders/create" class="btn">Create Your First Order</a>
</p>

<div class="divider"></div>
<p style="font-size:13px;color:#9aabbd;">If you did not create this account, please contact us immediately at <a href="mailto:support@corpius.net" style="color:#d4a02f;">support@corpius.net</a>.</p>
@endsection
