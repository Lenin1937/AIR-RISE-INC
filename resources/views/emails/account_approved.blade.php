@extends('emails.layout')

@section('subject', 'Your CORPIUS Account Has Been Approved!')

@section('hero')
<div class="hero">
    <h1>Account Approved ✓</h1>
    <p>You're all set to start using CORPIUS</p>
</div>
@endsection

@section('body')
<p>Hi <strong>{{ $user->first_name ?? $user->name }}</strong>,</p>
<p>Great news! Your CORPIUS account has been reviewed and <strong>approved</strong>. You now have full access to our platform and can start placing orders for US company formation, tax services, and immigration support.</p>

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
        <span class="label">Approved On</span>
        <span class="value">{{ ($user->approved_at ?? now())->format('F j, Y') }}</span>
    </div>
</div>

<p>Log in to your dashboard and create your first order to get started.</p>

<p style="text-align:center;">
    <a href="{{ config('app.url') }}/orders/create" class="btn">Start Your First Order</a>
</p>

<div class="divider"></div>
<p style="font-size:13px;color:#9aabbd;">Questions? Reach us any time at <a href="mailto:support@corpius.net" style="color:#d4a02f;">support@corpius.net</a>.</p>
@endsection
