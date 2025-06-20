<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Asset Warranty Expiration Notification</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px 30px 40px 30px;
            border: 1px solid #e5e7eb;
        }
        h1 {
            font-size: 24px;
            color: #2563eb; /* blue-600 */
            font-weight: 700;
            text-align: center;
            margin-bottom: 24px;
            user-select: none;
        }
        p {
            font-weight: 500;
            color: #374151;
            user-select: text;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
            color: #374151;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #e5e7eb;
        }
        th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #1f2937;
        }
        .badge-3m {
            background-color: #facc15; /* amber-400 */
            color: #854d0e; /* amber-900 */
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            user-select: none;
        }
        .badge-2m {
            background-color: #fb923c; /* orange-400 */
            color: #ffffff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            user-select: none;
        }
        .badge-1m {
            background-color: #ef4444; /* red-500 */
            color: #ffffff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            user-select: none;
        }
        p:last-of-type {
            margin-top: 32px;
            font-weight: 600;
            color: #374151;
            user-select: text;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Asset Warranty Expiration Notification">
        <h1>Asset Warranty Expiration Alert</h1>

        <p>The following assets are expiring soon:</p>

        <table>
            <thead>
                <tr>
                    <th>Asset</th>
                    <th>Code</th>
                    <th>Expiration Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assets as $asset)
                <tr>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->asset_code }}</td>
                    <td>{{ $asset->warranty_expiration->format('M d, Y') }}</td>
                    <td>
                        @if($asset->expiry_status === 'warning_3m')
                            <span class="badge-3m">⚠️ 3 months left</span>
                        @elseif($asset->expiry_status === 'warning_2m')
                            <span class="badge-2m">⚠️ 2 months left</span>
                        @elseif($asset->expiry_status === 'warning_1m')
                            <span class="badge-1m">⚠️ 1 month left</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>Please take appropriate action to renew warranties.</p>

        <p>Best regards,<br />
        Asset Management System</p>
    </div>
</body>
</html>
