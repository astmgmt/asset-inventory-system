<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Borrow Request Approved</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #4b5563;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .logo {
            width: 42px;
            height: auto;
        }
        .company-name {
            color: #1a1a1a;
            font-weight: 700;
            margin: 8px 0 20px 0;
        }
        h3 {
            color: #0f5132;  
            font-weight: 700;
            text-align: center;
            margin-bottom: 24px;
        }
        .content {
            color: #4b5563;
            line-height: 1.5;
        }
        .notice {
            background-color: #d1e7dd; 
            border: 1px solid #badbcc; 
            color: #0f5132;  
            border-radius: 6px;
            padding: 24px;
            text-align: left;
            font-size: 15px;
            margin: 25px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .notice::before {
            content: "✅";
            font-size: 20px;
            line-height: 1;
            margin-top: 2px;
        }
        .download-form {
            font-weight: 600;
            color: #ff4d4d;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            margin-top: 40px;
        }
        .copyright {
            margin: 16px 0;
        }
        strong {
            color: #1a1a1a;
        }
        @media screen and (max-width: 640px) {
            .container {
                margin: 10px;
                width: auto !important;
            }
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="Borrow Request Approved Notification">
        <div class="header" style="text-align: center;">
            <img class="logo" src="https://i.imgur.com/HF3xnxw.png" alt="Company Logo" />
            <div class="company-name">Asset Inventory System</div>
        </div>

        <h3>✅ Borrow Request Approved</h3>

        <div class="content">
            <p>Hello,</p>
            <p>Your borrow request (Code: <strong>{{ $borrowCode }}</strong>) has been approved.</p>

            <div class="notice">
                <div>
                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($approvalDate)->format('F j, Y g:i A') }}</p>
                    <p class="download-form">Download the Borrower's Accountability Form attached in this email.</p>
                    @if($remarks)
                    <p><strong>Remarks:</strong> {{ $remarks }}</p>
                    @endif
                </div>
            </div>            
            
            <p>Best regards,<br>
            <strong>Asset Inventory System Team</strong></p>
        </div>

        <div class="footer">
            <p class="copyright">&copy; {{ date('Y') }} Asset Inventory System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>