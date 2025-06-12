<!DOCTYPE html>
<html>
<head>
    <title>Asset Return Request</title>
</head>
<body>
    <h1>Asset Return Request: {{ $returnCode }}</h1>
    
    <p>A user has submitted an asset return request:</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
        <p><strong>User:</strong> {{ $userName }}</p>
        <p><strong>Return Date:</strong> {{ $returnDate }}</p>
        <p><strong>Remarks:</strong> {{ $remarks ?: 'No remarks provided' }}</p>
    </div>
    
    <p>Please review this return request in the admin portal.</p>
    
    <p>A PDF receipt is attached for your records.</p>
    
    <p>Best regards,<br>
    Asset Management System</p>
</body>
</html>