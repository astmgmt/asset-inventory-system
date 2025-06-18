<!DOCTYPE html>
<html>
<head>
    <title>Return Receipt - {{ $borrowCode }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .details { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Asset Return Receipt</h2>
        <p>Borrow Code: <strong>{{ $borrowCode }}</strong></p>
        <p>Date: {{ $returnDate }}</p>
    </div>

    <div class="details">
        <p>Returned by: {{ $transaction->user->name }}</p>
        <p>Department: {{ $transaction->user->department->name ?? 'N/A' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Asset Code</th>
                <th>Asset Name</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->borrowItems as $item)
            <tr>
                <td>{{ $item->asset->asset_code }}</td>
                <td>{{ $item->asset->name }}</td>
                <td>{{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>