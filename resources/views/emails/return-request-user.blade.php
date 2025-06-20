<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Your Return Approved: {{ $returnCode }}</title>
<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
<style>
  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
      Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
    color: #1f2937;
    line-height: 1.6;
    background-color: #f9fafb;
    margin: 0;
    padding: 20px 0;
  }
  .container {
    max-width: 600px;
    margin: 0 auto;
    background-color: #fff;
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
    font-weight: 600;
    color: #4b5563;
    margin: 0;
  }
  .card {
    padding: 0;
  }
  .title {
    font-size: 24px;
    color: #10B981; /* Tailwind green-500 */
    font-weight: 700;
    text-align: center;
    margin-bottom: 24px;
    user-select: none;
  }
  .info-item {
    text-align: center;
    margin-bottom: 20px;
    color: #374151;
    font-weight: 500;
  }
  .info-item strong {
    color: #111827;
  }
  .text-center {
    text-align: center;
    color: #4b5563;
    font-size: 15px;
    line-height: 1.5;
    margin-top: 24px;
  }
  .footer {
    margin-top: 30px;
    text-align: center;
    color: #6b7280;
    font-size: 12px;
    user-select: none;
  }
  /* Signature section not used here but preserved in case needed */
  .signature-section {
    margin-top: 30px;
    display: flex;
    justify-content: space-between;
  }
  .signature-box {
    width: 45%;
    border-top: 1px solid #d1d5db;
    padding-top: 10px;
    color: #374151;
    font-weight: 600;
  }
  @media screen and (max-width: 640px) {
    .container {
      margin: 10px;
      padding: 20px;
    }
    .title {
      font-size: 20px;
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

      <p class="text-center">
        The assets have been successfully returned to inventory.<br />
        Please see the attached PDF for the complete return approval summary.
      </p>
    </div>

    <div class="footer" role="contentinfo">
      &copy; {{ date('Y') }} Asset Management System. All rights reserved.<br />
      This is an automated message. Please do not reply directly to this email.
    </div>
  </div>
</body>
</html>
