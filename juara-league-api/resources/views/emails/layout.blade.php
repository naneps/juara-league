<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Juara League</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0f172a; font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #f8fafc;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #0f172a;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <!-- Logo -->
                <table cellpadding="0" cellspacing="0" border="0" width="600" style="max-width: 600px;">
                    <tr>
                        <td align="center" style="padding-bottom: 30px;">
                            <img src="https://raw.githubusercontent.com/lucide-icons/lucide/main/icons/trophy.svg" alt="Juara League Logo" width="60" height="60" style="filter: invert(1);">
                            <h1 style="margin: 10px 0 0; color: #eab308; font-size: 24px; font-weight: 800; letter-spacing: -0.025em; text-transform: uppercase;">
                                Juara League
                            </h1>
                        </td>
                    </tr>
                    <!-- Main Card -->
                    <tr>
                        <td style="background-color: #1e293b; padding: 40px; border-radius: 16px; border: 1px solid #334155; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                            @yield('content')
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 40px 20px; color: #94a3b8; font-size: 14px; line-height: 1.5;">
                            <p style="margin: 0;">&copy; {{ date('Y') }} Juara League. All rights reserved.</p>
                            <p style="margin: 5px 0 0;">Jakarta, Indonesia | The next level of tournament management.</p>
                            <div style="margin-top: 20px;">
                                <a href="#" style="color: #6366f1; text-decoration: none; margin: 0 10px;">Privacy Policy</a>
                                <a href="#" style="color: #6366f1; text-decoration: none; margin: 0 10px;">Terms of Service</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
