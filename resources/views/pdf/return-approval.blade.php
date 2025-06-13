<!-- resources/views/pdf/return-approval.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Return Approval - {{ $returnCode }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .details { margin-bottom: 15px; display: flex; justify-content: space-between; }
        .detail-item { width: 48%; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .signature-section { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; border-top: 1px solid #000; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RETURN APPROVAL RECEIPT</h2>
        <p>Return Code: <strong>{{ $returnCode }}</strong></p>
        <p>Approval Date: {{ $approvalDate }}</p>
    </div>

    <div class="details">
        <div class="detail-item">
            <p><strong>Returnee:</strong> {{ $returnItems->first()->returnedBy->name }}</p>
            <p><strong>Department:</strong> {{ $returnItems->first()->returnedBy->department->name ?? 'N/A' }}</p>
            <p><strong>Borrow Code:</strong> {{ $returnItems->first()->borrowItem->transaction->borrow_code }}</p>
        </div>
        <div class="detail-item">
            <!-- Use $approver instead of trying to get from relationship -->
            <p><strong>Approver:</strong> {{ $approver->name }}</p>
            <p><strong>Department:</strong> {{ $approver->department->name ?? 'N/A' }}</p>
            <p><strong>Approval Date:</strong> {{ $approvalDate }}</p>
        </div>
    </div>

    <h3>Returned Assets</h3>
    <table>
        <thead>
            <tr>
                <th>Asset Code</th>
                <th>Asset Name</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returnItems as $item)
            <tr>
                <td>{{ $item->borrowItem->asset->asset_code }}</td>
                <td>{{ $item->borrowItem->asset->name }}</td>
                <td>{{ $item->borrowItem->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="details">
        <p><strong>Remarks:</strong> {{ $returnItems->first()->remarks ?: 'None' }}</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <p>Returnee Signature:</p>
            <p>Date: ___________________</p>
        </div>
        <div class="signature-box">
            <p>Approver Signature:</p>
            <p>Date: ___________________</p>
        </div>
    </div>
</body>
</html>