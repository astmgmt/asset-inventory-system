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
            border: 1px solid #e5e7eb;
            overflow-wrap: break-word;
            padding: 30px 30px 40px 30px;
        }
        .header {
            background-color: #f3f4f6;
            padding: 20px;
            text-align: center;
            border-radius: 6px 6px 0 0;
            margin: -30px -30px 30px -30px;
            user-select: none;
        }
        .header h2 {
            margin: 0;
            color: #2563eb; /* blue-600 */
            font-weight: 700;
            font-size: 24px;
        }
        .content p {
            font-weight: 500;
            color: #374151;
            margin-bottom: 20px;
            user-select: text;
        }
        .asset-details {
            margin-bottom: 30px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 12px;
            user-select: text;
        }
        .detail-label {
            font-weight: 700;
            width: 130px;
            color: #1f2937;
            flex-shrink: 0;
        }
        .disposal-method {
            display: inline-block;
            padding: 5px 10px;
            background-color: #fef3c7; /* amber-100 */
            color: #92400e; /* amber-800 */
            border-radius: 4px;
            font-weight: 700;
            user-select: none;
        }
        .footer {
            background-color: #f3f4f6;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-radius: 0 0 6px 6px;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Asset Disposal Notification">
        <div class="header">
            <h2>Asset Disposal Notification</h2>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            
            <p>The following asset has been disposed by <strong>{{ $disposedBy }}</strong>:</p>
            
            <div class="asset-details">
                <div class="detail-row">
                    <div class="detail-label">Asset Code:</div>
                    <div>{{ $assetCode }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Asset Name:</div>
                    <div>{{ $assetName }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Category:</div>
                    <div>{{ $category }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Location:</div>
                    <div>{{ $location }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Condition:</div>
                    <div>{{ $condition }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Disposal Method:</div>
                    <div class="disposal-method">{{ ucfirst(str_replace('_', ' ', $disposalMethod)) }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Reason:</div>
                    <div>{{ $reason }}</div>
                </div>
                @if($notes)
                <div class="detail-row">
                    <div class="detail-label">Notes:</div>
                    <div>{{ $notes }}</div>
                </div>
                @endif
                <div class="detail-row">
                    <div class="detail-label">Disposal ID:</div>
                    <div>#{{ $disposalId }}</div>
                </div>
            </div>
            
            <p>This asset has been permanently removed from the asset management system.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
