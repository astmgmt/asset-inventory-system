<!DOCTYPE html>
<html>
<head>
    <title>Asset Warranty Expiration Notification</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .badge-3m { background-color: #ffc107; color: #856404; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
        .badge-2m { background-color: #fd7e14; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
        .badge-1m { background-color: #dc3545; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
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
    
    <p>Best regards,<br>
    Asset Management System</p>
</body>
</html>