@extends('emails.layouts.branded')

@section('subject', $headline ?? 'Notification from ' . config('app.name', 'Smart Project Hub'))
@section('preview', strip_tags($body ?? 'You have a new notification.'))

@section('content')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 24px;">
            <h2 style="margin: 0; color: #0f172a; font-size: 28px; font-weight: 700; line-height: 1.3;">
                {{ $headline ?? 'Notification' }} 🔔
            </h2>
        </td>
    </tr>
    <tr>
        <td style="padding-bottom: 16px;">
            <p style="margin: 0; color: #475569; font-size: 16px; line-height: 1.7;">
                Hi {{ $user->name ?? 'there' }},
            </p>
        </td>
    </tr>
    
    {{-- Message Body --}}
    <tr>
        <td style="padding: 24px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
            <p style="margin: 0; color: #475569; font-size: 16px; line-height: 1.7;">
                {{ $body }}
            </p>
        </td>
    </tr>
    
    {{-- CTA Button (if provided) --}}
    @if(!empty($actionText) && !empty($actionUrl))
    <tr>
        <td style="padding: 32px 0; text-align: center;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mobile-button">
                <tr>
                    <td style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); border-radius: 12px; text-align: center;" class="mobile-button">
                        <a href="{{ $actionUrl }}" style="display: inline-block; padding: 16px 40px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 12px;">
                            {{ $actionText }} →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    @endif
    
    {{-- Sign-off --}}
    <tr>
        <td style="padding-top: 24px; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0; color: #0f172a; font-size: 16px; line-height: 1.6;">
                Thanks,<br>
                <strong style="color: #7c3aed;">{{ config('app.name', 'Smart Project Hub') }} Notifications</strong>
            </p>
        </td>
    </tr>
</table>
@endsection
