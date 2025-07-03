<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Account Status Update</title>
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
        .status-container {
            margin: 30px 0;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-blocked {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
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
            content: "ℹ️";
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
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Account Status Notification">
        <div class="header" style="text-align: center;">
            <img class="logo" src="https://i.imgur.com/HF3xnxw.png" alt="Company Logo" />
            <div class="company-name">Asset Inventory System</div>
        </div>

        <h1>Account Status Update</h1>

        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            <p>Your account status has been updated:</p>

            <div class="status-container">
                @if($newStatus === 'Approved')
                <div class="status-badge status-approved">
                    ✅ Account Approved
                </div>
                <p>Your account has been approved and you can now access the system.</p>
                @elseif($newStatus === 'Blocked')
                <div class="status-badge status-blocked">
                    ⚠️ Account Blocked
                </div>
                <p>Your account has been blocked and you can no longer access the system.</p>
                @else
                <div class="status-badge status-pending">
                    ⏳ Account Pending
                </div>
                <p>Your account is now pending approval. You will be notified once your account is reviewed.</p>
                @endif
            </div>

            <div class="notice">
                <div>
                    <p><strong>Details:</strong></p>
                    <p>Previous Status: {{ ucfirst($oldStatus) }}</p>
                    <p>New Status: {{ ucfirst($newStatus) }}</p>
                    <p>Updated At: {{ $time }}</p>
                    
                    @if($newStatus === 'Blocked')
                    <p class="mt-3"><strong>If you believe this is a mistake, please contact our support team immediately.</strong></p>
                    <a href="mailto:support@assetinventory.com" class="support-link">Contact Support</a>
                    @elseif($newStatus === 'Pending')
                    <p class="mt-3"><strong>If you have any questions, please contact our support team.</strong></p>
                    <a href="mailto:support@assetinventory.com" class="support-link">Contact Support</a>
                    @endif
                </div>
            </div>
            
            <p>Best regards,<br>
            <strong>Asset Inventory System Team</strong></p>
        </div>

        <div class="footer">
            <p class="copyright">&copy; {{ date('Y') }} Asset Inventory System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>