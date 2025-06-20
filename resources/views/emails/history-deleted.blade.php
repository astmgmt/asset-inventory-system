<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>History Deleted: {{ $history->borrow_code }}</title>
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
        .header {
            text-align: center;
            padding-bottom: 20px;
            user-select: none;
        }
        .header img {
            height: 50px;
            margin-bottom: 8px;
        }
        .header p {
            margin: 0;
            color: #4b5563;
            font-weight: 600;
        }
        .card {
            padding: 0;
            color: #374151;
        }
        .title {
            font-size: 24px;
            color: #EF4444; /* red-500 */
            font-weight: 700;
            text-align: center;
            margin-bottom: 24px;
            user-select: none;
        }
        .info-item {
            margin-bottom: 12px;
            font-weight: 500;
            user-select: text;
        }
        .info-label {
            font-weight: 600;
            color: #1f2937;
            margin-right: 6px;
            user-select: none;
        }
        h3 {
            margin-top: 24px;
            margin-bottom: 16px;
            color: #111827;
            font-weight: 700;
            user-select: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-bottom: 24px;
            user-select: text;
        }
        th, td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            text-align: center;
            color: #374151;
        }
        th {
            background-color: #f9fafb;
            font-weight: 600;
            user-select: none;
        }
        p.mt-6 {
            margin-top: 24px;
            color: #4b5563;
            font-weight: 500;
            text-align: center;
            user-select: text;
            font-size: 0.875rem; /* 14px */
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            user-select: none;
        }
        @media screen and (max-width: 480px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="History Deleted Notification">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="Asset Management System Logo" />
            <p>Asset Management System</p>
        </div>

        <div class="card">
            <h1 class="title">🗑️ History Deleted: {{ $history->borrow_code }}</h1>

            <div class="info-item">
                <p>You've successfully deleted the following transaction from your history:</p>
            </div>

            <div class="info-item" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div>
                    <p class="info-label">Borrow Code:</p>
                    <p>{{ $history->borrow_code ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="info-label">Return Code:</p>
                    <p>{{ $history->return_code ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="info-label">Status:</p>
                    <p>{{ $history->status }}</p>
                </div>
                <div>
                    <p class="info-label">Action Date:</p>
                    <p>{{ $history->action_date->format('M d, Y H:i') }}</p>
                </div>
            </div>

            @if($history->borrow_data)
                <h3>Borrowed Assets</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Asset Name</th>
                            <th>Asset Code</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history->borrow_data['borrow_items'] as $item)
                        <tr>
                            <td>{{ $item['asset']['name'] ?? 'N/A' }}</td>
                            <td>{{ $item['asset']['asset_code'] ?? 'N/A' }}</td>
                            <td>{{ $item['quantity'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if($history->return_data)
                <h3>Returned Assets</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Asset Name</th>
                            <th>Asset Code</th>
                            <th>Quantity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history->return_data as $item)
                        <tr>
                            <td>{{ $item['borrow_item']['asset']['name'] ?? 'N/A' }}</td>
                            <td>{{ $item['borrow_item']['asset']['asset_code'] ?? 'N/A' }}</td>
                            <td>{{ $item['borrow_item']['quantity'] }}</td>
                            <td>{{ $item['status'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <p class="mt-6">
                This transaction has been permanently removed from your history.
            </p>
        </div>

        <div class="footer" role="contentinfo">
            &copy; {{ date('Y') }} Asset Management System. All rights reserved.<br />
            This is an automated message. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>
