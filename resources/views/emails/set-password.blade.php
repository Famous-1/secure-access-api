<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password - GreenQuote</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }
        
        table {
            border-collapse: collapse;
        }
        
        td {
            padding: 0;
        }
        
        .container {
            width: 100%;
            max-width: 640px;
        }
        
        .logo-text {
            font-weight: 600;
            font-size: 24px;
            line-height: 24px;
            color: #004596;
            margin: 0;
            padding: 0;
        }
        
        .body-content {
            font-size: 16px;
            line-height: 24px;
            color: #4D4D4D;
        }
        
        .bold-text {
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .footer {
            font-size: 14px;
            line-height: 20px;
            color: #666666;
        }
    </style>
</head>
<body>
    <center>
        <table class="container" width="100%" cellpadding="0" cellspacing="0" bgcolor="#ffffff">
            <!-- Header -->
            <tr>
                <td align="center" style="padding: 20px 40px; border-bottom: 1px solid #B7BABF;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center">
                                <table cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <img src="https://admin.greenquote.ng/images/email-temp-logo.png" alt="logo" width="80" height="80" style="display: block;">
                                        </td>
                                        <td style="padding-left: 14px;">
                                            <p class="logo-text">Green <br/>Quote</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Main Content -->
            <tr>
                <td style="padding: 20px 40px;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td>
                                <p class="logo-text" style="margin-bottom: 30px;">Set Your Password</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="body-content">
                                <p>Dear <span class="bold-text">{{ $user->firstname }},</span></p>
                                <p>Your account has been created by the administrator. Please click on the link below to set your password.</p>
                                <p style="margin: 20px 0;">
                                 <span style="font-weight: bold; font-size: 16px;">
                                    <a href="{{ env('FRONTEND_URL') . '/set-password/' . $token . '?email=' . urlencode($user->email) }}" target="_blank" style="color: #004596; text-decoration: none; background-color: #f0f0f0; padding: 12px 24px; border-radius: 4px; display: inline-block;">Click here to set your password</a>
                                </span>
                                </p>
                                <p>This link will expire in 24 hours.</p>
                                <p>If you did not expect this email, please contact support.</p>
                                <p>Best regards,<br>The GreenQuote Team</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Footer -->
            <tr>
                <td style="padding: 20px 40px; border-top: 1px solid #B7BABF;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="footer">
                                <p style="margin: 0;">Green Quote</p>
                                <p style="margin: 5px 0;">KM 18, Lekki Epe Expressway, Agungi bus stop, Lekki, Lagos</p>
                                <p style="margin: 5px 0;">Phone: +234 803 707 9950</p>
                                <p style="margin: 5px 0;">Email: hello@greenquote.ng</p>
                                <p style="margin: 5px 0;">Website: www.greenquote.ng</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>

