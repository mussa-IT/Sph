@extends('emails.layouts.branded')

@section('subject', 'Reminder: ' . ($project->name ?? 'Project Update'))
@section('preview', 'You have an upcoming deadline or task to review.')

@section('content')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 24px;">
            <h2 style="margin: 0; color: #0f172a; font-size: 28px; font-weight: 700; line-height: 1.3;">
                Project Reminder ⏰
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
    <tr>
        <td style="padding-bottom: 24px;">
            <p style="margin: 0; color: #475569; font-size: 16px; line-height: 1.7;">
                This is a friendly reminder about your project:
            </p>
        </td>
    </tr>
    
    {{-- Project Card --}}
    <tr>
        <td style="padding: 20px; background: #f1f5f9; border-radius: 12px; border-left: 4px solid #7c3aed;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding-bottom: 8px;">
                        <span style="color: #7c3aed; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Project</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 12px;">
                        <h3 style="margin: 0; color: #0f172a; font-size: 20px; font-weight: 600;">
                            {{ $project->name }}
                        </h3>
                    </td>
                </tr>
                @if(!empty($messageText))
                <tr>
                    <td>
                        <p style="margin: 0; color: #475569; font-size: 15px; line-height: 1.6;">
                            {{ $messageText }}
                        </p>
                    </td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
    
    {{-- CTA Button --}}
    <tr>
        <td style="padding: 32px 0; text-align: center;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mobile-button">
                <tr>
                    <td style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); border-radius: 12px; text-align: center;" class="mobile-button">
                        <a href="{{ $projectUrl }}" style="display: inline-block; padding: 16px 40px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 12px;">
                            View Project →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    
    {{-- Sign-off --}}
    <tr>
        <td style="padding-top: 16px; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0; color: #0f172a; font-size: 16px; line-height: 1.6;">
                Stay on track,<br>
                <strong style="color: #7c3aed;">The {{ config('app.name', 'Smart Project Hub') }} Team</strong>
            </p>
        </td>
    </tr>
</table>
@endsection
