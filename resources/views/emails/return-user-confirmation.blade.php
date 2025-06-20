<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Your Assets Returned: {{ $returnCode }}</title>
<!-- Import Inter font -->
<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
<style>
    body, p, h1, h2, h3, div {
        margin: 0;
        padding: 0;
    }
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
            Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
        background-color: #f9fafb;
        color: #1f2937;
        line-height: 1.5;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
        padding: 20px 0;
    }
    .container {
        max-width: 600px;
        background-color: #ffffff;
        margin: 0 auto;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
    .header {
        background-color: #e5e7eb;
        color: #374151;
        text-align: center;
        padding: 30px 20px;
        font-weight: 700;
        letter-spacing: 0.05em;
        user-select: none;
    }
    .header img {
        display: block;
        margin: 0 auto 8px auto;
        width: 60px;
        height: auto;
    }
    .company_name {
        font-weight: 700;
        font-size: 16px;
        margin: 0 0 20px 0;
        font-family: 'Inter', sans-serif;
    }
    .content {
        padding: 30px 30px 40px 30px;
        color: #374151;
        font-size: 16px;
        text-align: center;
    }
    .title {
        font-size: 24px;
        color: #10b981; /* emerald-500 */
        font-weight: 700;
        margin-bottom: 20px;
    }
    .info-item {
        margin-bottom: 16px;
        color: #374151;
        font-size: 16px;
    }
    .info-item strong {
        font-weight: 600;
    }
    .footer {
        text-align: center;
        font-size: 13px;
        color: #6b7280;
        background-color: #f9fafb;
        padding: 20px 15px;
        border-top: 1px solid #e5e7eb;
        user-select: none;
    }
    /* Responsive */
    @media screen and (max-width: 640px) {
        .container {
            margin: 10px;
            width: auto !important;
        }
        .title {
            font-size: 20px;
        }
        .content {
            padding: 20px 20px 30px 20px;
            font-size: 15px;
        }
    }
</style>
</head>
<body>
    <div class="container" role="main" aria-label="Asset Return Confirmation Email">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="Asset Management System Logo" />
            <h4 class="company_name">Asset Inventory System</h4>
        </div>

        <div class="content">
            <h1 class="title">✅ Return Confirmed: {{ $returnCode }}</h1>

            <div class="info-item">
                <p>Your assets have been successfully returned to our inventory.</p>
                <p>Return Date: <strong>{{ $returnDate }}</strong></p>
            </div>

            <p style="margin-top: 30px;">
                Thank you for using our asset management system.<br />
                The attached receipt confirms your return transaction.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Asset Management System. All rights reserved.<br />
            This is an automated message. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>
