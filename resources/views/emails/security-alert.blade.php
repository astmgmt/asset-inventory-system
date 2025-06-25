<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Security Alert: {{ $activity->activity_name }}</title>
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
            width: 42px;
            height: auto;
        }
        .company-name {
            color: #1a1a1a;
            font-weight: 700;
            margin: 8px 0 20px 0;
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
        .activity-detail-container {
            margin: 30px 0;
        }
        .activity-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .activity-table th {
            background-color: #4b5563;
            padding: 14px;
            border: 1px solid #9ca3af;
            font-weight: 600;
            text-align: center;
            color: #ffffff;
        }
        .activity-table td {
            padding: 14px;
            border: 1px solid #e5e7eb;
            text-align: left;
            background-color: #f9fafb;
            color: #1a1a1a;
        }
        .detail-label {
            font-weight: 600;
            color: #1a1a1a;
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
        .copyright {
            margin: 16px 0;
        }
        strong {
            color: #1a1a1a;
        }
        @media screen and (max-width: 640px) {
            .container {
                margin: 10px;
                width: auto !important;
            }
            .activity-table th,
            .activity-table td {
                padding: 10px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Security Alert Notification">
        <div class="header" style="text-align: center;">
            <img class="logo" src="https://i.imgur.com/HF3xnxw.png" alt="Company Logo" />
            <div class="company-name">Asset Inventory System</div>
        </div>

        <h1>🔔 Security Alert: {{ $activity->activity_name }}</h1>

        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            <p>We detected a security-sensitive action on your account:</p>

            <div class="activity-detail-container">
                <table class="activity-table" aria-label="Activity details">
                    <thead>
                        <tr>
                            <th>Detail Type</th>
                            <th>Information</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="detail-label">Activity</td>
                            <td>{{ $activity->activity_name }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Description</td>
                            <td>{{ $activity->description }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Time</td>
                            <td>{{ $time }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">IP Address</td>
                            <td>{{ $activity->ip_address }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Device</td>
                            <td>{{ $activity->user_agent }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="notice">
                <div>
                    <p><strong>Security Tip:</strong> If you didn't perform this action, please contact our support team immediately and consider changing your password.</p>
                    @if(!empty($supportUrl))
                    <a href="{{ $supportUrl }}" class="support-link">Contact Support</a>
                    @endif
                </div>
            </div>

            <p>This is an automated security alert. Please do not reply to this email.</p>
            
            <p>Best regards,<br>
            <strong>Asset Inventory System Security Team</strong></p>
        </div>

        <div class="footer">
            <p class="copyright">&copy; {{ date('Y') }} Asset Inventory System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>