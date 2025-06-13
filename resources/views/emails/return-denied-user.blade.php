<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Return Request Denied: {{ $returnCode }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; }
        .logo { height: 50px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        .title { font-size: 24px; color: #EF4444; margin-bottom: 20px; text-align: center; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .info-item { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="logo" style="height: 50px;">
            <p>Asset Management System</p>
        </div>

        <div class="card">
            <h1 class="title">❌ Return Request Denied: {{ $returnCode }}</h1>
            
            <div class="info-item">
                <p>Your return request has been reviewed and denied.</p>
                <p>Denial Date: <strong>{{ $denialDate }}</strong></p>
            </div>
            
            <div class="mt-6">
                <p class="info-label">Reason for Denial:</p>
                <p class="bg-red-50 p-4 rounded-md">{{ $remarks }}</p>
            </div>
            
            <p class="mt-6">
                Please review the reason for denial above. If you have any questions, 
                contact the asset management department for clarification.
            </p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} Asset Management System. All rights reserved.<br>
            This is an automated message. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>