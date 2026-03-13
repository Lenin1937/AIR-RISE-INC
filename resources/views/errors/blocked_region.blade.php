<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Not Available in Your Region — CORPIUS</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0a0f1a;
            color: #c8d4e3;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            background: #111827;
            border: 1px solid #1e2d42;
            border-radius: 12px;
            max-width: 520px;
            width: 100%;
            padding: 3rem 2.5rem;
            text-align: center;
        }
        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            background: #1c2535;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon svg { width: 32px; height: 32px; fill: #d4a02f; }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e5ecf4;
            margin-bottom: 0.75rem;
        }
        p {
            font-size: 0.95rem;
            line-height: 1.7;
            color: #8099b4;
            margin-bottom: 1rem;
        }
        .code {
            display: inline-block;
            background: #1c2535;
            color: #d4a02f;
            font-family: monospace;
            font-size: 0.8rem;
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        a.btn {
            display: inline-block;
            background: #d4a02f;
            color: #0a0f1a;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.65rem 1.75rem;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 0.5rem;
        }
        a.btn:hover { background: #e6b440; }
        .divider {
            border: none;
            border-top: 1px solid #1e2d42;
            margin: 2rem 0 1.5rem;
        }
        .footer { font-size: 0.8rem; color: #4a5f77; }
        .footer a { color: #6b8aaa; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm-1-5h2v2h-2zm0-8h2v6h-2z"/>
            </svg>
        </div>

        <h1>Service Not Available in Your Region</h1>

        <p>
            CORPIUS does not currently provide services in your country or region.
            Access to this platform is restricted based on legal,
            regulatory, and business policy requirements.
        </p>

        <span class="code">HTTP 451 — Unavailable For Legal Reasons</span>

        <p>
            If you believe this is an error or you are using a VPN / proxy,
            please contact our support team.
        </p>

        <a href="mailto:support@corpius.net" class="btn">Contact Support</a>

        <hr class="divider">
        <p class="footer">
            &copy; {{ date('Y') }} CORPIUS &nbsp;|&nbsp;
            <a href="mailto:support@corpius.net">support@corpius.net</a>
        </p>
    </div>
</body>
</html>
