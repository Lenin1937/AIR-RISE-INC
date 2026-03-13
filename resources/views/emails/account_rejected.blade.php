@extends('emails.layout')

@section('subject', 'Update on Your CORPIUS Account Application')

@section('hero')
<div class="hero">
    <h1>Account Application Update</h1>
    <p>We were unable to approve your account at this time</p>
</div>
@endsection

@section('body')
<p>Hi <strong>{{ $user->first_name ?? $user->name }}</strong>,</p>
<p>Thank you for applying for a CORPIUS account. After reviewing your application, we were unfortunately unable to approve it at this time.</p>

@if($reason)
<div class="info-card">
    <div class="row">
        <span class="label">Reason</span>
        <span class="value">{{ $reason }}</span>
    </div>
</div>
@endif

<p>If you believe this decision was made in error, or if you'd like to provide additional information, please contact our support team — we're happy to re-evaluate your application.</p>

<p style="text-align:center;">
    <a href="mailto:support@corpius.net" class="btn">Contact Support</a>
</p>

<div class="divider"></div>
<p style="font-size:13px;color:#9aabbd;">Email us directly at <a href="mailto:support@corpius.net" style="color:#d4a02f;">support@corpius.net</a> with your full name and the email address used to register.</p>
@endsection
