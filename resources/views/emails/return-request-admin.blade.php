<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Return Request: {{ $returnCode }}</title>
</head>
<body>
    <h1>Return Request Notification</h1>
    
    <p><strong>Return Code:</strong> {{ $returnCode }}</p>
    <p><strong>Borrower:</strong> {{ $userName }}</p>
    <p><strong>Department:</strong> {{ $department }}</p>
    <p><strong>Borrow Code:</strong> {{ $borrowCode }}</p>
    <p><strong>Request Date:</strong> {{ $returnDate }}</p>
    
    @if($remarks)
        <p><strong>Remarks:</strong> {{ $remarks }}</p>
    @endif
    
    <h2>Assets to Return:</h2>
    <ul>
        @foreach($assetsList as $asset)
        <li>
            {{ $asset['code'] }} - {{ $asset['name'] }} (Qty: {{ $asset['quantity'] }})
        </li>
        @endforeach
    </ul>
    
    <p>This is an automated notification. Please do not reply.</p>
</body>
</html>