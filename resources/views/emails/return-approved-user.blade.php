<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Your Return Approved: {{ $returnCode }}</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
      text-align: center;
      padding-bottom: 20px;
      user-select: none;
    }
    .header img {
      height: 50px;
      margin-bottom: 8px;
    }
    .header p {
      margin: 0;
      color: #4b5563;
      font-weight: 600;
    }
    .card {
      padding: 0;
    }
    .title {
      font-size: 24px;
      color: #10b981; /* green-500 */
      font-weight: 700;
      text-align: center;
      margin-bottom: 24px;
      user-select: none;
    }
    .info-item {
      margin-bottom: 10px;
      text-align: center;
      color: #374151;
      font-weight: 500;
    }
    .mt-6 {
      margin-top: 1.5rem;
      text-align: center;
      color: #374151;
    }
    .signature-section {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
      gap: 20px;
      flex-wrap: wrap;
    }
    .signature-box {
      flex: 1 1 45%;
      border-top: 1px solid #d1d5db; /* gray-300 */
      padding-top: 10px;
      color: #374151;
      font-weight: 500;
      min-width: 250px;
      user-select: none;
    }
    .signature-box p {
      margin: 8px 0;
    }
    .footer {
      margin-top: 30px;
      text-align: center;
      color: #6b7280;
      font-size: 12px;
      user-select: none;
    }
    @media screen and (max-width: 480px) {
      .signature-section {
        flex-direction: column;
      }
      .signature-box {
        width: 100%;
        min-width: auto;
      }
    }
  </style>
</head>
<body>
  <div class="container" role="main" aria-label="Return Approval Notification">
    <div class="header">
      <img src="https://i.imgur.com/HF3xnxw.png" alt="Asset Management System Logo" />
      <p>Asset Management System</p>
    </div>

    <div class="card">
      <h1 class="title">✅ Return Approved: {{ $returnCode }}</h1>

      <div class="info-item">
        <p>Your return request has been approved by <strong>{{ $approverName }}</strong>.</p>
        <p>Approval Date: <strong>{{ $approvalDate }}</strong></p>
      </div>

      <p class="mt-6">
        The assets have been successfully returned to inventory. <br />
        Please see the attached PDF for the complete return approval summary.
      </p>
    </div>

    <div class="footer" role="contentinfo">
      &copy; {{ date('Y') }} Asset Inventory System. All rights reserved.<br />
      This is an automated message. Please do not reply directly to this email.
    </div>
  </div>
</body>
</html>
