<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Borrow Request Denied</title>
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
            margin: 8px 0 30px 0;
        }
        h3 {            
            color: #dc2626;
            font-weight: 700;
            text-align: center;
            margin-bottom: 24px;
        }
        .content {
            color: #4b5563;
            line-height: 1.5;
        }
        .notice {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 24px;
            text-align: left;
            font-size: 15px;
            color: #7f1d1d;
            margin: 25px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .notice::before {
            content: "❌";
            font-size: 20px;
            line-height: 1;
            margin-top: 2px;
        }
        .assets-table-container {
            margin: 30px 0;
        }
        .assets-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .assets-table th {
            background-color: #4b5563;
            padding: 14px;
            border: 1px solid #9ca3af;
            font-weight: 600;
            text-align: center;
            color: #ffffff;
        }
        .assets-table td {
            padding: 14px;
            border: 1px solid #e5e7eb;
            text-align: center;
            background-color: #f9fafb;
            color: #1a1a1a;
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
            .assets-table th,
            .assets-table td {
                padding: 10px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Borrow Request Denied Notification">
        <div class="header" style="text-align: center;">
            <img class="logo" src="https://i.imgur.com/HF3xnxw.png" alt="Company Logo" />
            <div class="company-name">Asset Inventory System</div>
        </div>

        <h3>⚠️ Borrow Request Denied</h3>

        <div class="content">
            <p>Hello,</p>
            <p>Your borrow request (Code: <strong>{{ $borrowCode }}</strong>) has been denied.</p>

            <div class="notice">
                <div>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($denialDate)->format('F j, Y g:i A') }}</p>
                    <p><strong>Reason:</strong> {{ $remarks }}</p>
                </div>
            </div>

            <h2 style="font-size: 18px; color: #1a1a1a; margin: 24px 0 16px 0; text-align: center;">
                Denied Asset Details
            </h2>

            <div class="assets-table-container">
                <table class="assets-table" aria-label="Denied asset details">
                    <thead>
                        <tr>
                            <th>Asset Code</th>
                            <th>Asset Name</th>
                            <th>Quantity</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($assetDetails) > 0)
                            @foreach($assetDetails as $asset)
                                <tr>
                                    <td>{{ $asset['asset_code'] }}</td>
                                    <td>{{ $asset['name'] }}</td>
                                    <td>{{ $asset['quantity'] }}</td>
                                    <td>{{ $asset['purpose'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4">No assets found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <p>If you have any questions or wish to submit a new request, please contact the administrator.</p>
            
            <p>Best regards,<br>
            <strong>Asset Inventory System Team</strong></p>
        </div>

        <div class="footer">
            <p class="copyright">&copy; {{ date('Y') }} Asset Inventory System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>