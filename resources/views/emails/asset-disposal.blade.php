<!DOCTYPE html>
<html>
<head>
    <title>Asset Disposal Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f3f4f6; padding: 20px; text-align: center; }
        .content { background-color: #ffffff; padding: 30px; }
        .footer { background-color: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
        .asset-details { margin-bottom: 20px; }
        .detail-row { display: flex; margin-bottom: 10px; }
        .detail-label { font-weight: bold; width: 120px; }
        .disposal-method { 
            display: inline-block; 
            padding: 5px 10px; 
            background-color: #fef3c7; 
            color: #92400e; 
            border-radius: 4px; 
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
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