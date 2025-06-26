<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Message: {{ $subject }}</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #4b5563; 
            background-color: #f9fafb; 
            margin: 0; 
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }
        .header h2 {
            color: #1e40af;
            margin: 10px 0 5px;
        }
        .content {
            padding: 20px 0;
        }
        .message-box {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 15px;
            margin-top: 10px;
            border-left: 4px solid #3b82f6;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 0.9em;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            margin-top: 30px;
        }
        .info-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Contact Message Notification</h2>
            <p>Asset Management System</p>
        </div>
        
        <div class="content">
            <div>
                <span class="info-label">Subject:</span>
                {{ $subject }}
            </div>
            
            <div style="margin-top: 20px;">
                <span class="info-label">Message:</span>
                <div class="message-box">
                    {!! nl2br(e($messageContent)) !!}
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Asset Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>