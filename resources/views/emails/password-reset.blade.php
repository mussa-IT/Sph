@extends('emails.layouts.branded')

@section('subject', 'Reset Your Password - ' . config('app.name', 'Smart Project Hub'))
@section('preview', 'We received a request to reset your password. Click the button below to create a new one.')

@section('content')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 24px;">
            <h2 style="margin: 0; color: #0f172a; font-size: 28px; font-weight: 700; line-height: 1.3;">
                Reset Your Password 
            </h2>
        </td>
    </tr>
    <tr>
        <td style="padding-bottom: 16px;">
            <p style="margin: 0; color: #475569; font-size: 16px; line-height: 1.7;">
                Hello {{ $user->name }},
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding-bottom: 24px;">
            <p style="margin: 0; color: #475569; font-size: 16px; line-height: 1.7;">
                We received a request to reset your password for your {{ config('app.name', 'Smart Project Hub') }} account. Click the button below to create a new password:
            </p>
        </td>
    </tr>
    
    {{-- CTA Button --}}
    <tr>
        <td style="padding: 16px 0; text-align: center;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mobile-button">
                <tr>
                    <td style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); border-radius: 12px; text-align: center;" class="mobile-button">
                        <a href="{{ $resetUrl }}" style="display: inline-block; padding: 16px 40px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 12px;">
                            Reset My Password
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    
    {{-- Expiration Notice --}}
    <tr>
        <td style="padding: 16px 20px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="vertical-align: top; padding-right: 12px;">
                        <span style="font-size: 18px;"></span>
                    </td>
                    <td>
                        <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.5;">
                            <strong>This link expires in {{ $expireMinutes }} minutes</strong> for security reasons. If you need a new link after that, you can request another reset.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    
    {{-- Alternative Link --}}
    <tr>
        <td style="padding-top: 24px;">
            <p style="margin: 0; color: #64748b; font-size: 14px; line-height: 1.6;">
                If the button doesn't work, copy and paste this link into your browser:
            </p>
            <p style="margin: 8px 0 0; word-break: break-all;">
                <a href="{{ $resetUrl }}" style="color: #7c3aed; font-size: 13px; text-decoration: underline;">{{ $resetUrl }}</a>
            </p>
        </td>
    </tr>
    
    {{-- Security Notice --}}
    <tr>
        <td style="padding-top: 24px; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0; color: #64748b; font-size: 14px; line-height: 1.6;">
                <strong style="color: #475569;">Didn't request this?</strong><br>
                If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged and your account is secure.
            </p>
        </td>
    </tr>
    
    {{-- Sign-off --}}
    <tr>
        <td style="padding-top: 24px;">
            <p style="margin: 0; color: #0f172a; font-size: 16px; line-height: 1.6;">
                Stay secure,<br>
                <strong style="color: #7c3aed;">The {{ config('app.name', 'Smart Project Hub') }} Security Team</strong>
            </p>
        </td>
    </tr>
</table>
@endsection
