<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Return Receipt - {{ $returnCode }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .subtitle { font-size: 18px; color: #555; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 18px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 5px; margin-bottom: 10px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px; border: 1px solid #ddd; }
        .info-table .label { font-weight: bold; width: 30%; background-color: #f5f5f5; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th, .items-table td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .items-table th { background-color: #f5f5f5; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 14px; color: #777; }
        .signature { margin-top: 50px; border-top: 1px solid #333; width: 300px; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Asset Return Receipt</div>
        <div class="subtitle">Return Code: {{ $returnCode }}</div>
        <div class="subtitle">Date: {{ $returnDate }}</div>
    </div>
    
    <div class="section">
        <div class="section-title">User Information</div>
        <table class="info-table">
            <tr>
                <td class="label">Name:</td>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td class="label">Department:</td>
                <td>{{ $user->department->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">Returned Assets</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Asset Name</th>
                    <th>Quantity</th>
                    <th>Borrow Code</th>
                </tr>
            </thead>
            <tbody>
                @foreach($returnItems as $item)
                <tr>
                    <td>{{ $item->borrowItem->asset->asset_code }}</td>
                    <td>{{ $item->borrowItem->asset->name }}</td>
                    <td>{{ $item->borrowItem->quantity }}</td>
                    <td>{{ $item->borrowItem->transaction->borrow_code }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <p>This document serves as proof of asset return.</p>
        <p>Generated on {{ now()->format('M d, Y H:i') }}</p>
        
        <div class="signature">
            <p>User Signature: _________________________</p>
        </div>
    </div>
</body>
</html>