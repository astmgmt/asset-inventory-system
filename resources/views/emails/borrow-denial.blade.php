<!DOCTYPE html>
<html>
<head>
    <title>Borrow Request Denied</title>
</head>
<body>
    <h1>Your Borrow Request Has Been Denied</h1>
    
    <p>Your borrow request (Code: {{ $borrowCode }}) has been denied.</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
        <p><strong>Denial Date:</strong> {{ $denialDate }}</p>
        <p><strong>Reason:</strong> {{ $remarks }}</p>
    </div>
    
    <p>If you have any questions, please contact the administrator.</p>
    
    <p>Best regards,<br>
    Asset Management System</p>
</body>
</html>