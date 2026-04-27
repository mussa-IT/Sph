{{-- Premium Branded Email Layout --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('subject')</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        /* Reset styles for email clients */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        
        /* Email client specific fixes */
        #outlook a { padding: 0; }
        .ExternalClass { width: 100%; }
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div { line-height: 100%; }
        #backgroundTable { margin: 0; padding: 0; width: 100% !important; line-height: 100% !important; }
        
        /* Responsive styles */
        @media screen and (max-width: 600px) {
            .mobile-hide { display: none !important; }
            .mobile-center { text-align: center !important; }
            .mobile-padding { padding-left: 20px !important; padding-right: 20px !important; }
            .mobile-stack { display: block !important; width: 100% !important; }
            .mobile-button { width: 100% !important; max-width: 300px !important; }
            .mobile-font-size { font-size: 20px !important; }
            .mobile-padding-bottom { padding-bottom: 20px !important; }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .dark-bg { background-color: #0f172a !important; }
            .dark-text { color: #f8fafc !important; }
            .dark-secondary { background-color: #1e293b !important; }
            .dark-border { border-color: #334155 !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-font-smoothing: antialiased;">
    <!-- Preview Text -->
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
        @yield('preview')
    </div>
    
    <!-- Email Container -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="dark-bg" style="background-color: #f1f5f9;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                <!-- Main Content Table -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" class="mobile-stack" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1);" class="dark-secondary">
                    
                    {{-- Header with Logo --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%); padding: 40px 40px 30px; text-align: center;" class="mobile-padding">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        {{-- Logo Icon --}}
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="background: rgba(255,255,255,0.15); border-radius: 12px; padding: 12px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                                        <path d="M2 17l10 5 10-5"/>
                                                        <path d="M2 12l10 5 10-5"/>
                                                    </svg>
                                                </td>
                                            </tr>
                                        </table>
                                        <h1 style="margin: 16px 0 0; color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: -0.5px;">
                                            {{ config('app.name', 'Smart Project Hub') }}
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    {{-- Main Content --}}
                    <tr>
                        <td style="padding: 40px 40px 30px;" class="mobile-padding">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="dark-text" style="color: #0f172a; font-size: 16px; line-height: 1.7;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f8fafc; padding: 30px 40px; border-top: 1px solid #e2e8f0;" class="mobile-padding dark-secondary">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom: 20px;">
                                        {{-- Social Links --}}
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 0 8px;">
                                                    <a href="https://twitter.com/smartprojecthub" style="display: inline-block; width: 36px; height: 36px; background: #7c3aed; border-radius: 50%; text-align: center; line-height: 36px; text-decoration: none;">
                                                        <span style="color: #ffffff; font-size: 14px;">X</span>
                                                    </a>
                                                </td>
                                                <td style="padding: 0 8px;">
                                                    <a href="https://github.com/smartprojecthub" style="display: inline-block; width: 36px; height: 36px; background: #2563eb; border-radius: 50%; text-align: center; line-height: 36px; text-decoration: none;">
                                                        <span style="color: #ffffff; font-size: 14px;">GH</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="color: #64748b; font-size: 13px; line-height: 1.6;" class="dark-text">
                                        <p style="margin: 0 0 8px;">
                                            © {{ date('Y') }} {{ config('app.name', 'Smart Project Hub') }}. All rights reserved.
                                        </p>
                                        <p style="margin: 0;">
                                            You're receiving this email because you have an account with us.
                                        </p>
                                        <p style="margin: 16px 0 0;">
                                            <a href="{{ url('/settings') }}" style="color: #7c3aed; text-decoration: none; font-weight: 500;">Manage email preferences</a> •
                                            <a href="{{ url('/privacy') }}" style="color: #7c3aed; text-decoration: none; font-weight: 500;">Privacy Policy</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                </table>
                
                {{-- Bottom Spacing --}}
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" class="mobile-stack" style="max-width: 600px; width: 100%;">
                    <tr>
                        <td style="padding: 20px 0; text-align: center; color: #94a3b8; font-size: 12px;">
                            <p style="margin: 0;">
                                {{ config('app.name', 'Smart Project Hub') }} • AI-Powered Project Management
                            </p>
                        </td>
                    </tr>
                </table>
                
            </td>
        </tr>
    </table>
</body>
</html>
