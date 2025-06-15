<!DOCTYPE html>
<html>
<head>
    <title>Software Subscription Expiration Notification</title>
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
    <h1>Software Subscription Expiration Alert</h1>
    
    <p>The following software subscriptions are expiring soon:</p>
    
    <table>
        <thead>
            <tr>
                <th>Software</th>
                <th>Code</th>
                <th>Expiration Date</th>
                <th>Status</th>
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
    
    <p>Best regards,<br>
    Asset Management System</p>
</body>
</html>