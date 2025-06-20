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
            padding: 30px 30px 40px 30px;
            border: 1px solid #e5e7eb;
        }
        h1 {
            font-size: 24px;
            color: #dc2626; /* red-600 */
            font-weight: 700;
            text-align: center;
            margin-bottom: 24px;
            user-select: none;
        }
        p {
            font-weight: 500;
            color: #374151;
            user-select: text;
            line-height: 1.5;
        }
        strong {
            color: #1f2937;
        }
        .card {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
            border: 1px solid #e5e7eb;
        }
        h2 {
            margin-top: 32px;
            margin-bottom: 16px;
            color: #111827;
            font-weight: 700;
            user-select: none;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            user-select: text;
        }
        th, td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            text-align: left;
            color: #374151;
        }
        th {
            background-color: #f9fafb;
            font-weight: 600;
            user-select: none;
        }
        td[colspan] {
            text-align: center;
            color: #6b7280;
        }
        p:last-of-type {
            margin-top: 32px;
            font-weight: 600;
            color: #374151;
            user-select: text;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Borrow Request Denied Notification">
        <h1>Your Borrow Request Has Been Denied</h1>

        <p>Your borrow request (Code: <strong>{{ $borrowCode }}</strong>) has been denied.</p>

        <div class="card">
            <p><strong>Date:</strong> {{ $denialDate }}</p>
            <p><strong>Reason:</strong> {{ $remarks }}</p>
        </div>

        <h2>Denied Asset Details</h2>
        <table role="table" aria-label="Denied Asset Details">
            <thead>
                <tr>
                    <th scope="col">Asset Code</th>
                    <th scope="col">Asset Name</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Purpose</th>
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

        <p>If you have any questions or wish to submit a new request, please contact the administrator.</p>

        <p>Best regards,<br />
        Asset Inventory System</p>
    </div>
</body>
</html>
