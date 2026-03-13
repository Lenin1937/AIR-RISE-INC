<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your CORPIUS Verification Code</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #0b1e33; padding: 32px; text-align: center; }
        .header img { height: 40px; }
        .body { padding: 40px 36px; }
        .body h2 { margin: 0 0 8px; font-size: 22px; color: #0b1e33; }
        .body p { margin: 0 0 20px; color: #5a6474; font-size: 15px; line-height: 1.6; }
        .otp-box { background: #f4f6f9; border: 2px dashed #d4a02f; border-radius: 10px; padding: 24px; text-align: center; margin: 24px 0; }
        .otp-code { font-size: 42px; font-weight: 800; letter-spacing: 12px; color: #0b1e33; }
        .otp-note { font-size: 13px; color: #8a96a3; margin-top: 8px; }
        .footer { background: #f4f6f9; padding: 20px 36px; text-align: center; font-size: 12px; color: #8a96a3; }
        .footer a { color: #d4a02f; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ config('app.url') }}/logo.png" alt="CORPIUS" />
        </div>
        <div class="body">
            <h2>Verify your email address</h2>
            @if($recipientName)
                <p>Hi {{ $recipientName }},</p>
            @endif
            <p>Use the following 6-digit verification code to continue creating your CORPIUS account. This code expires in <strong>10 minutes</strong>.</p>

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-note">One-time code · expires in 10 minutes</div>
            </div>

            <p>If you did not request this code, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} CORPIUS. All rights reserved.<br>
            <a href="{{ config('app.url') }}">corpius.com</a>
        </div>
    </div>
</body>
</html>
