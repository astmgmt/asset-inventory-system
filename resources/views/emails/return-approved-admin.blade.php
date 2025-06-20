<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Return Approved: {{ $returnCode }}</title>
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
    .grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
      margin-bottom: 1.5rem;
      color: #374151;
      font-weight: 500;
    }
    .info-label {
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 0.25rem;
      user-select: none;
    }
    h3 {
      margin-top: 24px;
      margin-bottom: 16px;
      color: #111827;
      font-weight: 700;
      user-select: none;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
      margin-bottom: 24px;
    }
    th, td {
      padding: 12px;
      border: 1px solid #e5e7eb;
      text-align: center;
      color: #374151;
      user-select: text;
    }
    th {
      background-color: #f9fafb;
      font-weight: 600;
      user-select: none;
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
      .grid {
        grid-template-columns: 1fr;
      }
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

      <div class="grid">
        <div>
          <p class="info-label">Returnee:</p>
          <p>{{ $userName }} ({{ $userDepartment }})</p>
        </div>
        <div>
          <p class="info-label">Request Date:</p>
          <p>{{ $returnDate }}</p>
        </div>
        <div>
          <p class="info-label">Approver:</p>
          <p>{{ $approverName }} ({{ $approverDepartment }})</p>
        </div>
        <div>
          <p class="info-label">Approval Date:</p>
          <p>{{ $approvalDate }}</p>
        </div>
        <div>
          <p class="info-label">Remarks:</p>
          <p>{{ $remarks ?: 'None' }}</p>
        </div>
      </div>

      <h3>Returned Assets</h3>
      <table>
        <thead>
          <tr>
            <th>Asset Name</th>
            <th>Asset Code</th>
            <th>Quantity</th>
          </tr>
        </thead>
        <tbody>
          @foreach($returnItems as $item)
          <tr>
            <td>{{ $item->borrowItem->asset->name }}</td>
            <td>{{ $item->borrowItem->asset->asset_code }}</td>
            <td>{{ $item->borrowItem->quantity }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>

    <div class="footer" role="contentinfo">
      &copy; {{ date('Y') }} Asset Management System. All rights reserved.<br />
      This is an automated message. Please do not reply directly to this email.
    </div>
  </div>
</body>
</html>
