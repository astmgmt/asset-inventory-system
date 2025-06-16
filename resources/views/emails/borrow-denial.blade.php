<!DOCTYPE html>
<html>
<head>
    <title>Borrow Request Denied</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th {
            background-color: #e9ecef;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .table tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Borrow Request Has Been Denied</h1>
        
        <p>Your borrow request (Code: <strong>{{ $borrowCode }}</strong>) has been denied.</p>
        
        <div class="card">
            <p><strong>Date:</strong> {{ $denialDate }}</p>
            <p><strong>Reason:</strong> {{ $remarks }}</p>
        </div>
        
        <h2>Denied Asset Details</h2>
        <table class="table">
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
                        <td colspan="4" style="text-align: center;">No assets found</td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <p>If you have any questions or wish to submit a new request, please contact the administrator.</p>
        
        <p>Best regards,<br>
        Asset Inventory System</p>
    </div>
</body>
</html>