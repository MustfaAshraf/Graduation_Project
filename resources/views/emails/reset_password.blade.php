<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .otp {
            font-size: 24px;
            font-weight: bold;
            color: #d9534f;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
        .footer {
            font-size: 12px;
            color: #888;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>Hello,</p>
        <p>You have requested to reset your password. Use the OTP code below:</p>
        <p class="otp">{{ $token }}</p>
        <p>This code will expire in **3 minutes**.</p>
        <p><strong>⚠️ Do not share this code with anyone.</strong></p>
        <p>If you didn't request a password reset, ignore this email.</p>
        <hr>
        <p class="footer">Need help? Contact our support team.</p>
    </div>
</body>
</html>
