@extends('emails.layouts.branded')

@section('subject', 'Welcome to ' . config('app.name', 'Smart Project Hub') . '!')
@section('preview', 'Your account is ready. Start building smarter projects with AI.')

@section('content')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td style="padding-bottom: 24px;">
            <h2 style="margin: 0; color: #0f172a; font-size: 28px; font-weight: 700; line-height: 1.3;">
                Welcome to {{ config('app.name', 'Smart Project Hub') }}, {{ $user->name }}! 🎉
            </h2>
        </td>
    </tr>
    <tr>
        <td style="padding-bottom: 24px;">
            <p style="margin: 0; color: #475569; font-size: 16px; line-height: 1.7;">
                Your account is ready! You can now plan projects, manage tasks, and keep budgets on track in one powerful workspace powered by AI.
            </p>
        </td>
    </tr>
    
    {{-- Feature Highlights --}}
    <tr>
        <td style="padding: 24px 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
            <h3 style="margin: 0 0 16px; color: #0f172a; font-size: 18px; font-weight: 600;">
                What you can do right away:
            </h3>
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding-bottom: 12px;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding-right: 12px; vertical-align: top;">
                                    <span style="display: inline-block; width: 24px; height: 24px; background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); border-radius: 6px; text-align: center; line-height: 24px; color: #ffffff; font-size: 14px;">1</span>
                                </td>
                                <td style="color: #475569; font-size: 15px; line-height: 1.6;">
                                    <strong style="color: #0f172a;">Create your first project workspace</strong> with our AI-powered builder
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 12px;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding-right: 12px; vertical-align: top;">
                                    <span style="display: inline-block; width: 24px; height: 24px; background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); border-radius: 6px; text-align: center; line-height: 24px; color: #ffffff; font-size: 14px;">2</span>
                                </td>
                                <td style="color: #475569; font-size: 15px; line-height: 1.6;">
                                    <strong style="color: #0f172a;">Add tasks and assign priorities</strong> to keep your team aligned
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 12px;">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding-right: 12px; vertical-align: top;">
                                    <span style="display: inline-block; width: 24px; height: 24px; background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); border-radius: 6px; text-align: center; line-height: 24px; color: #ffffff; font-size: 14px;">3</span>
                                </td>
                                <td style="color: #475569; font-size: 15px; line-height: 1.6;">
                                    <strong style="color: #0f172a;">Track budgets and key milestones</strong> with real-time insights
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding-right: 12px; vertical-align: top;">
                                    <span style="display: inline-block; width: 24px; height: 24px; background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); border-radius: 6px; text-align: center; line-height: 24px; color: #ffffff; font-size: 14px;">4</span>
                                </td>
                                <td style="color: #475569; font-size: 15px; line-height: 1.6;">
                                    <strong style="color: #0f172a;">Use AI tools to move faster</strong> - just describe your idea and let AI build the plan
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    
    {{-- CTA Button --}}
    <tr>
        <td style="padding: 32px 0; text-align: center;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="mobile-button">
                <tr>
                    <td style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); border-radius: 12px; text-align: center;" class="mobile-button">
                        <a href="{{ $dashboardUrl ?? url('/dashboard') }}" style="display: inline-block; padding: 16px 40px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 12px;">
                            Open Your Dashboard →
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    
    {{-- Help Section --}}
    <tr>
        <td style="padding-top: 24px; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0; color: #64748b; font-size: 14px; line-height: 1.6;">
                Need help getting started? Simply reply to this email and our team will be happy to guide you through your first project.
            </p>
        </td>
    </tr>
    
    {{-- Sign-off --}}
    <tr>
        <td style="padding-top: 24px;">
            <p style="margin: 0; color: #0f172a; font-size: 16px; line-height: 1.6;">
                Welcome aboard!<br>
                <strong style="color: #7c3aed;">The {{ config('app.name', 'Smart Project Hub') }} Team</strong>
            </p>
        </td>
    </tr>
</table>
@endsection
