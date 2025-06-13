<!DOCTYPE html>
<html>
<head>
    <title>Borrow Request Approved</title>
</head>
<body>
    <h1>Your Borrow Request Has Been Approved</h1>
    
    <p>Your borrow request (Code: {{ $borrowCode }}) has been approved.</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
        <p><strong>Approval Date:</strong> {{ $approvalDate }}</p>
        @if($remarks)
            <p><strong>Remarks:</strong> {{ $remarks }}</p>
        @endif
    </div>
    
    <p>A PDF approval document is attached for your records.</p>
    
    <p>Best regards,<br>
    Asset Management System</p>
</body>
</html>