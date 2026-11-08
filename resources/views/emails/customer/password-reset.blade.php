<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your password</title>
</head>
<body style="margin:0;padding:32px 12px;background:#f4f7fc;font-family:Montserrat,Arial,Helvetica,sans-serif;color:#173566;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #e4ecf8;border-radius:14px;overflow:hidden;">
        <tr><td style="padding:28px 32px;background:#082b68;color:#ffffff;font-size:17px;font-weight:700;">{{ $siteSetting->site_name }}</td></tr>
        <tr><td style="padding:34px 32px 20px;">
            <h1 style="margin:0 0 14px;color:#092b69;font-size:24px;line-height:1.3;">Reset your password</h1>
            <p style="margin:0 0 14px;color:#526987;font-size:14px;line-height:1.7;">Hello {{ $customer->name }},</p>
            <p style="margin:0 0 22px;color:#526987;font-size:14px;line-height:1.7;">We received a request to reset the password for your {{ $siteSetting->site_name }} account. Use the button below to choose a new password.</p>
            <p style="margin:0 0 26px;"><a href="{{ $resetUrl }}" style="display:inline-block;padding:13px 22px;border-radius:8px;background:#0865f5;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">Reset Password</a></p>
            <p style="margin:0 0 8px;color:#71819a;font-size:12px;line-height:1.7;">If the button does not work, copy and paste this link into your browser:</p>
            <p style="margin:0;word-break:break-all;color:#0865f5;font-size:12px;line-height:1.7;">{{ $resetUrl }}</p>
        </td></tr>
        <tr><td style="padding:18px 32px 26px;border-top:1px solid #edf2f8;color:#8b9ab0;font-size:11px;line-height:1.6;">This password reset link expires in {{ $expiresInMinutes }} minutes. If you did not request a password reset, no action is required and your current password will remain unchanged.</td></tr>
    </table>
</body>
</html>
