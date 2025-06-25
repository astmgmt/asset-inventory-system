<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Asset Disposal Notification</title>
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
            text-align: center;
        }
        h3 {
            color: #7f1d1d;  
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
            color: #7f1d1d;  
            border-radius: 6px;
            padding: 24px;
            text-align: left;
            font-size: 15px;
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
        .disposal-method {
            display: inline-block;
            padding: 6px 12px;
            background-color: #fef3c7;
            color: #92400e;
            border-radius: 4px;
            font-weight: 700;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin: 30px 0;
        }
        .details-table th {
            background-color: #4b5563;
            padding: 14px;
            border: 1px solid #9ca3af;
            font-weight: 600;
            text-align: center;
            color: #ffffff;
        }
        .details-table td {
            padding: 14px;
            border: 1px solid #e5e7eb;
            text-align: left;
            background-color: #f9fafb;
            color: #1a1a1a;
        }
        .detail-label {
            font-weight: 600;
            width: 35%;
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
            .details-table th,
            .details-table td {
                padding: 10px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Asset Disposal Notification">
        <div class="header" style="text-align: center;">
            <img class="logo" src="https://i.imgur.com/HF3xnxw.png" alt="Company Logo" />
            <div class="company-name">Asset Inventory System</div>
        </div>

        <h3>⚠️ Asset Disposal Notification</h3>

        <div class="content">
            <p>Hello,</p>
            <p>The following asset has been disposed by <strong>{{ $disposedBy }}</strong>:</p>

            <div class="notice">
                <div>
                    <p>This asset has been permanently removed from the asset management system.</p>
                    <p><strong>Disposal Method:</strong> <span class="disposal-method">{{ ucfirst(str_replace('_', ' ', $disposalMethod)) }}</span></p>
                </div>
            </div>

            <table class="details-table" aria-label="Asset disposal details">
                <thead>
                    <tr>
                        <th colspan="2">Asset Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="detail-label">Asset Code</td>
                        <td>{{ $assetCode }}</td>
                    </tr>
                    <tr>
                        <td class="detail-label">Asset Name</td>
                        <td>{{ $assetName }}</td>
                    </tr>
                    <tr>
                        <td class="detail-label">Category</td>
                        <td>{{ $category }}</td>
                    </tr>
                    <tr>
                        <td class="detail-label">Location</td>
                        <td>{{ $location }}</td>
                    </tr>
                    <tr>
                        <td class="detail-label">Condition</td>
                        <td>{{ $condition }}</td>
                    </tr>
                    <tr>
                        <td class="detail-label">Reason</td>
                        <td>{{ $reason }}</td>
                    </tr>
                    @if($notes)
                    <tr>
                        <td class="detail-label">Notes</td>
                        <td>{{ $notes }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="detail-label">Disposal ID</td>
                        <td>#{{ $disposalId }}</td>
                    </tr>
                </tbody>
            </table>
            
            <p>Best regards,<br>
            <strong>Asset Inventory System Team</strong></p>
        </div>

        <div class="footer">
            <p class="copyright">&copy; {{ date('Y') }} Asset Inventory System. All rights reserved.</p>
            <p>This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>