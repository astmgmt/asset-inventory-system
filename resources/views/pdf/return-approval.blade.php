<!DOCTYPE html>
<html>
<head>
    <title>Return Approval - {{ $returnCode }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; }
        .subtitle { font-size: 18px; color: #555; }
        .details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .signature { margin-top: 50px; }
        .signature-line { width: 300px; border-top: 1px solid #000; margin: 40px 0 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Return Approval</div>
        <div class="subtitle">Return Code: {{ $returnCode }}</div>
        <div>Approval Date: {{ $approvalDate }}</div>
    </div>
    
    <div class="borrower-info">
        <p><strong>Borrower:</strong> {{ $transaction->user->name }}</p>
        <p><strong>Department:</strong> {{ $transaction->user->department->name ?? 'N/A' }}</p>
        <p><strong>Borrow Code:</strong> {{ $transaction->borrow_code }}</p>
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
    
    <div class="approver-info">
        <p><strong>Approved By:</strong> {{ $approver->name }}</p>
        <p><strong>Approver Department:</strong> {{ $approver->department->name ?? 'N/A' }}</p>
    </div>
    
    <div class="signature">
        <div class="signature-line"></div>
        <p>Approver Signature</p>
    </div>
</body>
</html>