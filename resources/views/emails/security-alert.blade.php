<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Security Alert: {{ $activity->activity_name }}</title>
<!-- Import Inter font -->
<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
<style>
    /* Reset some basic elements */
    body, p, h1, h2, h3, div {
        margin: 0;
        padding: 0;
    }
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
            Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
        background-color: #f9fafb; /* light gray for email clients */
        color: #1f2937; /* dark gray text */
        line-height: 1.5;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
        padding: 20px 0;
    }
    .container {
        max-width: 600px;
        background-color: #ffffff;
        margin: 0 auto;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .header {
        background-color: #e5e7eb; /* gray-200 */
        color: #374151; /* gray-700 */
        text-align: center;
        padding: 30px 20px;
        font-weight: 700;
        letter-spacing: 0.05em;
        user-select: none;
    }
    .header img {
        display: block;
        margin: 0 auto 8px auto; /* center horizontally and 8px bottom margin */
        width: 60px;
        height: auto;
    }
    .company_name {
        font-weight: 700;
        font-size: 16px;
        margin: 0 0 20px 0; /* 20px bottom margin creates spacing before heading */
        font-family: 'Inter', sans-serif;
    }
    .header h1 {
        font-size: 24px;
        margin: 0 0 12px 0; /* space below heading */
    }
    .alert-badge {
        display: inline-block;
        background-color: #d1d5db; /* gray-300 */
        color: #4b5563; /* gray-600 */
        font-weight: 600;
        font-size: 14px;
        padding: 6px 18px;
        border-radius: 20px;
        letter-spacing: 0.02em;
        user-select: none;
    }
    .content {
        padding: 30px 30px 40px 30px;
        color: #374151; /* gray-700 */
        font-size: 16px;
    }
    .content p {
        margin-bottom: 16px;
    }
    .activity-detail {
        background-color: #f3f4f6; /* gray-100 */
        border-radius: 6px;
        padding: 16px 20px;
        margin-bottom: 18px;
        border: 1px solid #d1d5db; /* gray-300 */
    }
    .detail-label {
        font-weight: 600;
        color: #4b5563; /* gray-600 */
        margin-bottom: 6px;
        font-size: 14px;
    }
    .security-tip {
        background-color: #fef3c7; /* yellow-100 */
        border-left: 4px solid #f59e0b; /* yellow-500 */
        padding: 16px 20px;
        border-radius: 0 6px 6px 0;
        font-weight: 600;
        color: #92400e; /* yellow-800 */
        margin: 24px 0 0 0;
        font-size: 15px;
        line-height: 1.4;
    }
    .footer {
        text-align: center;
        font-size: 13px;
        color: #6b7280; /* gray-500 */
        background-color: #f9fafb;
        padding: 20px 15px;
        border-top: 1px solid #e5e7eb;
        user-select: none;
    }
    /* Responsive tweaks */
    @media screen and (max-width: 640px) {
        .container {
            margin: 10px;
            width: auto !important;
        }
        .header h1 {
            font-size: 20px;
        }
        .content {
            padding: 20px 20px 30px 20px;
            font-size: 15px;
        }
        .activity-detail {
            padding: 12px 16px;
        }
        .security-tip {
            padding: 14px 16px;
            font-size: 14px;
        }
        .company_name {
            margin-bottom: 1rem;
            margin-top: 0;
        }
    }
</style>
</head>
<body>
    <div class="container" role="main" aria-label="Security Alert Email">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="Asset Management System Logo" />
            <h4 class="company_name">Asset Inventory System</h4>
            <h1>Security Alert</h1>
            <div class="alert-badge">{{ $activity->activity_name }}</div>
        </div>

        <div class="content">
            <p>Hello {{ $user->name }},</p>

            <p>We detected a security-sensitive action on your account:</p>

            <div class="activity-detail" role="group" aria-labelledby="activity-label">
                <div class="detail-label" id="activity-label">Activity:</div>
                <div>{{ $activity->activity_name }}</div>
            </div>

            <div class="activity-detail" role="group" aria-labelledby="description-label">
                <div class="detail-label" id="description-label">Description:</div>
                <div>{{ $activity->description }}</div>
            </div>

            <div class="activity-detail" role="group" aria-labelledby="time-label">
                <div class="detail-label" id="time-label">Time:</div>
                <div>{{ $time }}</div>
            </div>

            <div class="activity-detail" role="group" aria-labelledby="ip-label">
                <div class="detail-label" id="ip-label">IP Address:</div>
                <div>{{ $activity->ip_address }}</div>
            </div>

            <div class="activity-detail" role="group" aria-labelledby="device-label">
                <div class="detail-label" id="device-label">Device:</div>
                <div>{{ $activity->user_agent }}</div>
            </div>

            <div class="security-tip" role="alert" aria-live="polite">
                <strong>Security Tip:</strong> If you didn't perform this action, please contact our support team immediately and consider changing your password.
            </div>

            <p style="margin-top: 30px;">
                Best regards,<br />
                Asset Inventory System Security Team
            </p>
        </div>

        <div class="footer">
            This is an automated security alert. Please do not reply to this email.
        </div>
    </div>
</body>
</html>
