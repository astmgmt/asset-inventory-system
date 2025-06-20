<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Software Subscription Expiration Notification</title>
<!-- Import Inter font -->
<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
<style>
    body, p, h1, h2, h3, div, table, th, td {
        margin: 0;
        padding: 0;
        border: 0;
    }
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
            Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
        background-color: #f9fafb;
        color: #1f2937;
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
        background-color: #e5e7eb;
        color: #374151;
        text-align: center;
        padding: 30px 20px;
        font-weight: 700;
        letter-spacing: 0.05em;
        user-select: none;
    }
    .header img {
        display: block;
        margin: 0 auto 8px auto;
        width: 60px;
        height: auto;
    }
    .company_name {
        font-weight: 700;
        font-size: 16px;
        margin: 0 0 20px 0;
        font-family: 'Inter', sans-serif;
    }
    .header h1 {
        font-size: 24px;
        margin: 0 0 12px 0;
    }
    .content {
        padding: 30px 30px 40px 30px;
        color: #374151;
        font-size: 16px;
    }
    .content p {
        margin-bottom: 16px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 16px;
        color: #374151;
    }
    th, td {
        padding: 12px 15px;
        border: 1px solid #d1d5db;
        text-align: left;
        vertical-align: middle;
    }
    th {
        background-color: #f3f4f6;
        font-weight: 600;
        color: #4b5563;
    }
    .badge-3m {
        background-color: #fbbf24; /* yellow-400 */
        color: #92400e; /* yellow-800 */
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
        display: inline-block;
        user-select: none;
    }
    .badge-2m {
        background-color: #fb923c; /* orange-400 */
        color: #7c2d12; /* orange-800 */
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
        display: inline-block;
        user-select: none;
    }
    .badge-1m {
        background-color: #ef4444; /* red-500 */
        color: #b91c1c; /* red-700 */
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
        display: inline-block;
        user-select: none;
    }
    .footer {
        text-align: center;
        font-size: 13px;
        color: #6b7280;
        background-color: #f9fafb;
        padding: 20px 15px;
        border-top: 1px solid #e5e7eb;
        user-select: none;
    }
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
        table {
            font-size: 14px;
        }
        th, td {
            padding: 10px 12px;
        }
        .badge-3m, .badge-2m, .badge-1m {
            font-size: 12px;
            padding: 4px 8px;
        }
        .company_name {
            margin-bottom: 1rem;
            margin-top: 0;
        }
    }
</style>
</head>
<body>
    <div class="container" role="main" aria-label="Software Subscription Expiration Notification">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="Asset Management System Logo" />
            <h4 class="company_name">Asset Inventory System</h4>
            <h1>Software Subscription Expiration Alert</h1>
        </div>

        <div class="content">
            <p>The following software subscriptions are expiring soon:</p>

            <table role="table" aria-label="Expiring Software Subscriptions">
                <thead>
                    <tr>
                        <th scope="col">Software</th>
                        <th scope="col">Code</th>
                        <th scope="col">Expiration Date</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($software as $item)
                    <tr>
                        <td>{{ $item->software_name }}</td>
                        <td>{{ $item->software_code }}</td>
                        <td>{{ $item->expiry_date->format('M d, Y') }}</td>
                        <td>
                            @if($item->expiry_status === 'warning_3m')
                                <span class="badge-3m">⚠️ 3 months left</span>
                            @elseif($item->expiry_status === 'warning_2m')
                                <span class="badge-2m">⚠️ 2 months left</span>
                            @elseif($item->expiry_status === 'warning_1m')
                                <span class="badge-1m">⚠️ 1 month left</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p>Please take appropriate action to renew subscriptions.</p>

            <p style="margin-top: 30px;">
                Best regards,<br />
                Asset Inventory System
            </p>
        </div>

        <div class="footer">
            This is an automated notification. Please do not reply to this email.
        </div>
    </div>
</body>
</html>
