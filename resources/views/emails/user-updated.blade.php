<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Account Information Updated</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #4b5563;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        
        
        .logo {
            width: 42px; /* Optional: control logo size */
            height: auto;
        }
        
        .company-name {
            color: #1a1a1a;
        }

        h1 {
            font-size: 20px;
            color: #1a1a1a;
            font-weight: 700;
            text-align: center;
            margin-bottom: 24px;
        }
        .content {
            color: #4b5563;
            line-height: 1.5;
            text-align: justify;
        }
        .changes-table-container {
            margin: 30px 0;
        }
        .changes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .changes-table th {
            background-color: #4b5563;
            padding: 14px;
            border: 1px solid #9ca3af;
            font-weight: 600;
            text-align: center;
            color: #ffffff;
        }
        .changes-table td {
            padding: 14px;
            border: 1px solid #e5e7eb;
            text-align: center;
            background-color: #f9fafb;
            color: #1a1a1a;
        }
        .old-value {
            color: #6b7280;
        }
        .new-value {
            font-weight: 500;
        }
        .notice {
            background-color: #e0f2fe;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 24px;
            text-align: left;
            font-size: 15px;
            color: #0c4a6e;
            margin: 25px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .notice::before {
            content: "⚠️";
            font-size: 20px;
            line-height: 1;
            margin-top: 2px;
        }
        .support-link {
            color: #0c4a6e;
            text-decoration: underline;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            margin-top: 40px;
        }
        p .copyright {
            margin: 16px 0;
           
        }
        .copyright {
            padding: 10px;
            margin-top: 10px;
        }

        strong {
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Account Update Notification">
        <div class="header" style="text-align: center;">
            <img class="logo" src="https://i.imgur.com/HF3xnxw.png" alt="Company Logo" />
            <div class="company-name">Asset Inventory System</div>
        </div>

        <h1 style="text-align: center;">🔔 Account Information Updated</h1>

        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            <p>We wanted to inform you that your account information has been updated by an administrator. Below is a summary of the changes:</p>

            <div class="changes-table-container">
                <table class="changes-table" aria-label="Account changes summary">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Old Value</th>
                            <th>New Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($changes as $field => $change)
                        <tr>
                            <td>{{ ucfirst($field) }}</td>
                            <td class="old-value">{{ $change['old'] }}</td>
                            <td class="new-value">{{ $change['new'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="notice">
                <div>
                    <p>If you didn't request these changes, please contact our support team immediately.</p>
                    @if(!empty($supportUrl))
                    <a href="{{ $supportUrl }}" class="support-link">Contact Support</a>
                    @endif
                </div>
            </div>

            <p>Thank you for your attention to this matter.</p>
            
            <p>Best regards,<br>
            <strong>Asset Inventory System Team</strong></p>
        </div>

        <div class="footer">
            <p class="copyright">&copy; {{ date('Y') }} Asset Inventory System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
