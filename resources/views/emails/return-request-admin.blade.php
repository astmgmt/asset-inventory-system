<!-- resources/views/emails/return-request-admin.blade.php -->
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
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .info-item { margin-bottom: 10px; }
        .info-label { font-weight: 600; color: #1f2937; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="logo" style="height: 50px;">
            <p>Asset Management System</p>
        </div>

        <div class="card">
            <h1 class="title">📬 New Return Request: {{ $returnCode }}</h1>
            
            <div class="info-item">
                <p><span class="info-label">Borrower:</span> {{ $userName }}</p>
                <p><span class="info-label">Request Date:</span> {{ $returnDate }}</p>
                <p><span class="info-label">Borrow Code:</span> {{ $borrowCode }}</p>
                <p><span class="info-label">Return Code:</span> {{ $returnCode }}</p>
                
                @if($remarks)
                <p><span class="info-label">Remarks:</span> {{ $remarks }}</p>
                @endif
            </div>
            
            <h3 class="font-semibold mt-4">Assets to Return:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($selectedBorrowItems as $item)
                        <tr>
                            <td>{{ $item->asset->asset_code }}</td>
                            <td>{{ $item->asset->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Pending Return</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <p class="text-center mt-6">
                <a href="{{ route('super-admin.return-approvals') }}" 
                   style="display: inline-block; background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">
                    Review Return Request
                </a>
            </p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} Asset Management System. All rights reserved.<br>
            This is an automated message. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>