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
        .header {
            display: flex;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 30px;
            user-select: none;
        }
        .header img {
            height: 48px;
            width: 48px;
            object-fit: contain;
            margin-right: 15px;
        }
        .header .company-name {
            font-weight: 700;
            font-size: 22px;
            color: #2563eb; /* blue-600 */
        }
        h1 {
            font-size: 24px;
            color: #2563eb; /* blue-600 */
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
    <div class="container" role="main" aria-label="Borrow Request Approved Notification">
        <div class="header" aria-label="Company logo and name">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="Asset Management System Logo" />
            <h4 class="company_name">Asset Inventory System</h4>
        </div>

        <h1>Your Borrow Request Has Been Approved</h1>

        <p>Your borrow request (Code: <strong>{{ $borrowCode }}</strong>) has been approved.</p>

        <div class="card">
            <p><strong>Approval Date:</strong> {{ $approvalDate }}</p>
            @if($remarks)
                <p><strong>Remarks:</strong> {{ $remarks }}</p>
            @endif
        </div>

        <p>A PDF approval document is attached for your records.</p>

        <p>Best regards,<br />
        Asset Management System</p>
    </div>
</body>
</html>
