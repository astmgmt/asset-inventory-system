<!-- resources/views/emails/history-deleted.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>History Deleted: {{ $history->borrow_code }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; }
        .logo { height: 50px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        .title { font-size: 24px; color: #EF4444; margin-bottom: 20px; text-align: center; }
        .table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .info-item { margin-bottom: 10px; }
        .info-label { font-weight: 600; color: #1f2937; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="logo" style="height: 50px;">
            <p>Asset Management System</p>
        </div>

        <div class="card">
            <h1 class="title">🗑️ History Deleted: {{ $history->borrow_code }}</h1>
            
            <div class="info-item">
                <p>You've successfully deleted the following transaction from your history:</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
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
                <h3 style="margin-top: 24px; margin-bottom: 16px;">Borrowed Assets</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Asset Name</th>
                            <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Asset Code</th>
                            <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history->borrow_data['borrow_items'] as $item)
                        <tr>
                            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                                {{ $item['asset']['name'] ?? 'N/A' }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                                {{ $item['asset']['asset_code'] ?? 'N/A' }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                                {{ $item['quantity'] }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            
            @if($history->return_data)
                <h3 style="margin-top: 24px; margin-bottom: 16px;">Returned Assets</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Asset Name</th>
                            <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Asset Code</th>
                            <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Quantity</th>
                            <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history->return_data as $item)
                        <tr>
                            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                                {{ $item['borrow_item']['asset']['name'] ?? 'N/A' }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                                {{ $item['borrow_item']['asset']['asset_code'] ?? 'N/A' }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                                {{ $item['borrow_item']['quantity'] }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                                {{ $item['status'] }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            
            <p class="mt-6 text-center text-sm text-gray-600">
                This transaction has been permanently removed from your history.
            </p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} Asset Management System. All rights reserved.<br>
            This is an automated message. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>