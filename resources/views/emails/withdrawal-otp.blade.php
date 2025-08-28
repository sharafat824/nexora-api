<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
</head>

<body style="margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background-color: #f4f4f4;">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" width="600"
                    style="background-color: #ffffff; margin-top: 40px; border-radius: 10px; overflow: hidden;">
                    <tr>
                        <td
                            style="padding: 30px; text-align: center; background: linear-gradient(to right, #0ea5e9, #06b6d4); color: white;">
                            <h1 style="margin: 0; font-size: 24px;">Withdrawal Verification</h1>
                            <p style="margin-top: 8px;">Secure your transaction with OTP verification</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px; text-align: center;">
                            <p style="font-size: 16px; color: #333;">Use the following OTP code to complete your
                                withdrawal:</p>
                            <h2 style="font-size: 36px; color: #0ea5e9; margin: 20px 0;">{{ $otp }}</h2>
                            <p style="font-size: 14px; color: #666;">This code is valid for 5 minutes.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px; text-align: center; font-size: 13px; color: #999;">
                            If you did not request a withdrawal, please contact support immediately.
                        </td>
                    </tr>
                    <tr>
                        <td
                            style="background-color: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #999;">
                            &copy; {{ date('Y') }} Nexora UK. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
