<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Return Request: {{ $returnCode }}</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
    .header {
      text-align: center;
      padding-bottom: 20px;
    }
    .header img {
      height: 50px;
      margin-bottom: 8px;
    }
    .company-name {
      color: #1a1a1a;
      font-weight: 700;
      margin: 0;
      text-align: center;
    }
    .title {
      color: #0c5460; 
      font-weight: 700;
      text-align: center;
      margin-bottom: 24px;
      font-size: 16px;
    }

    .info-card {
      background-color: #e6f7fa; /* lighter bg-info */
      color: #0c5460; /* Bootstrap text-info contrast */
      border-radius: 8px;
      padding: 20px;
      margin: 20px 0;
      border: 1px solid #bee5eb;
    }

    .info-item {
      margin-bottom: 12px;
      padding-bottom: 8px;
      border-bottom: 1px solid #bde5ea;
    }

    .info-label {
      font-weight: 600;
      display: block;
      margin-bottom: 4px;
    }

    .assets-table-container {
      margin: 30px 0;
    }

    .assets-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    .assets-table th {
      background-color: #4b5563;
      padding: 12px;
      border: 1px solid #9ca3af;
      font-weight: 600;
      text-align: center;
      color: #ffffff;
    }

    .assets-table td {
      padding: 12px;
      border: 1px solid #e5e7eb;
      text-align: center;
      background-color: #f9fafb;
      color: #1a1a1a;
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

    @media screen and (max-width: 640px) {
      .container {
        margin: 10px;
        padding: 20px;
      }
      .assets-table th,
      .assets-table td {
        padding: 8px;
        font-size: 13px;
      }
    }
  </style>
</head>
<body>
  <div class="container" role="main" aria-label="Return Request Notification">
    <div class="header">
      <img src="https://i.imgur.com/HF3xnxw.png" alt="Company Logo" />
      <div class="company-name">Asset Management System</div>
    </div>

    <h3 class="title">🔔 Return Request: {{ $returnCode }}</h3>

    <!-- Card-styled info section -->
    <div class="info-card">
      <div class="info-item">
        <span class="info-label">Requested By:</span>
        {{ $userName }} ({{ $department }})
      </div>
      <div class="info-item">
        <span class="info-label">Borrow Code:</span>
        {{ $borrowCode }}
      </div>
      <div class="info-item">
        <span class="info-label">Request Date:</span>
        {{ $returnDate }}
      </div>
      <div class="info-item">
        <span class="info-label">Remarks:</span>
        {{ $remarks ?: 'None' }}
      </div>
    </div>

    <div class="assets-table-container">
      <table class="assets-table" aria-label="Assets to return">
        <thead>
          <tr>
            <th>Asset Code</th>
            <th>Asset Name</th>
            <th>Model</th>
            <th>Serial #</th>
            <th>Quantity</th>
          </tr>
        </thead>
        <tbody>
          @foreach($assetsList as $item)
          <tr>
            <td>{{ $item['code'] }}</td>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['model'] }}</td>
            <td>{{ $item['serial'] }}</td>
            <td>{{ $item['quantity'] }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="footer">
      <p class="copyright">&copy; {{ date('Y') }} Asset Management System. All rights reserved.</p>
      <p>This is an automated message. Please do not reply directly to this email.</p>
    </div>
  </div>
</body>
</html>
