<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject', 'CORPIUS')</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6f9; margin: 0; padding: 0; color: #333; }
        .container { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #0b1e33; padding: 28px 36px; text-align: center; }
        .header img { height: 38px; }
        .hero { background: linear-gradient(135deg, #0b1e33 0%, #12294a 100%); padding: 24px 36px 28px; text-align: center; }
        .hero h1 { margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; line-height: 1.3; }
        .hero p { margin: 8px 0 0; font-size: 14px; color: #9ab0c8; }
        .body { padding: 32px 36px; }
        .body p { margin: 0 0 16px; color: #4a5568; font-size: 15px; line-height: 1.7; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px 24px; margin: 20px 0; }
        .info-card .row { display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid #edf2f7; font-size: 14px; }
        .info-card .row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-card .label { color: #718096; font-weight: 500; min-width: 160px; }
        .info-card .value { color: #1a202c; font-weight: 600; text-align: right; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-progress { background: #dbeafe; color: #1e40af; }
        .badge-complete { background: #d1fae5; color: #065f46; }
        .badge-cancel   { background: #fee2e2; color: #991b1b; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-filed    { background: #ede9fe; color: #5b21b6; }
        .btn { display: inline-block; background: #d4a02f; color: #0b1e33; font-size: 14px; font-weight: 700; padding: 13px 28px; border-radius: 8px; text-decoration: none; margin: 8px 0; }
        .divider { height: 1px; background: #edf2f7; margin: 24px 0; }
        .alert { border-left: 4px solid #d4a02f; background: #fffbeb; padding: 14px 18px; border-radius: 0 8px 8px 0; margin: 20px 0; font-size: 14px; color: #78350f; }
        .footer { background: #f8fafc; border-top: 1px solid #edf2f7; padding: 20px 36px; text-align: center; font-size: 12px; color: #9aabbd; line-height: 1.7; }
        .footer a { color: #d4a02f; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="{{ config('app.url') }}/images/logo.png" alt="CORPIUS" onerror="this.style.display='none'" />
    </div>
    @yield('hero')
    <div class="body">
        @yield('body')
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} CORPIUS · <a href="{{ config('app.url') }}">corpius.net</a><br>
        Questions? Email us at <a href="mailto:support@corpius.net">support@corpius.net</a>
    </div>
</div>
</body>
</html>
