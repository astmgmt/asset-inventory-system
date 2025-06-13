<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Return Request: {{ $returnCode }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; }
        .logo { height: 50px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        .title { font-size: 24px; color: #2563eb; margin-bottom: 20px; text-align: center; }
        .table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .info-item { margin-bottom: 10px; }
        .info-label { font-weight: 600; color: #1f2937; }
        .action-button { 
            display: inline-block; 
            background-color: #2563eb; 
            color: #ffffff; 
            padding: 12px 24px; 
            text-decoration: none; 
            border-radius: 4px; 
            font-weight: bold;
            margin: 10px 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="logo" style="height: 50px;">
            <p>Asset Management System</p>
        </div>

        <div class="card">
            <h1 class="title">🔔 Return Request: {{ $returnCode }}</h1>
            
            <div class="info-item">
                <span class="info-label">Requested by:</span> {{ $userName }}
            </div>
            <div class="info-item">
                <span class="info-label">Request Date:</span> {{ $returnDate }}
            </div>
            <div class="info-item">
                <span class="info-label">Remarks:</span> {{ $remarks ?: 'None' }}
            </div>
            <div class="info-item">
                <span class="info-label">Original Borrow Code:</span> {{ $transaction->borrow_code }}
            </div>
            
            <h3 style="margin-top: 24px; margin-bottom: 16px;">Assets to Return</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Asset Name</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Asset Code</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->borrowItems as $item)
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                            {{ $item->asset->name }}
                        </td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                            {{ $item->asset->asset_code }}
                        </td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                            {{ $item->quantity }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('admin.return-requests') }}" class="action-button">
                    Review Return Request
                </a>
            </div>
            
            <p class="mt-6">
                Please review this return request in the admin dashboard. 
                The attached document contains the full return request details.
            </p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} Asset Management System. All rights reserved.<br>
            This is an automated message. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>