<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Contact Message: {{ $subject }}</title>
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
      display: block;
      margin: 0 auto 10px auto;
      width: 42px;
      height: auto;
    }
    .company-name {
      text-align: center;
      color: #1a1a1a;
      font-weight: 700;
      margin: 0 0 8px 0;
      font-size: 12px;
    }
    .title {
      text-align: center;
      color: #1a1a1a;
      font-weight: 600;
      font-size: 16px;
      margin: 10px 0 20px 0;
    }
    .content {
      color: #4b5563;
      line-height: 1.5;
      padding: 0;
    }
    .info-label {
      font-weight: 700;
      color: #374151;
      margin-right: 6px;
    }
    .sender-info {
      margin-bottom: 20px;
      font-size: 15px;
      color: #1a1a1a;
      line-height: 1.6;
    }
    .subject-line {
      margin-bottom: 20px;
      font-size: 15px;
      color: #1a1a1a;
    }
    .message-box {
      background-color: #f2f2f2;
      border-radius: 6px;
      padding: 15px;
      color: #1a1a1a;
      white-space: pre-wrap;
      word-wrap: break-word;
      font-size: 15px;
      line-height: 1.5;
    }
    .footer {
      text-align: center;
      color: #6b7280;
      font-size: 14px;
      padding-top: 20px;
      border-top: 1px solid #e5e7eb;
      margin-top: 40px;
    }
    @media screen and (max-width: 640px) {
      .container {
        margin: 10px;
        width: auto !important;
      }
      .message-box {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <div class="container" role="main" aria-label="Contact Message Notification">
    <img class="logo" src="https://i.imgur.com/HF3xnxw.png" alt="Company Logo" />
    <div class="company-name">Asset Management System</div>
    <div class="title">Contact Message Notification</div>
    
    <div class="sender-info">
      <div><span class="info-label">Name:</span> {{ $senderName }}</div>
      <div><span class="info-label">From:</span> {{ $senderEmail }}</div>
    </div>
    
    <div class="content">
      <div class="subject-line">
        <span class="info-label">Subject:</span> {{ $subject }}
      </div>
      
      <div>
        <span class="info-label">Message:</span>
        <div class="message-box">
          {!! nl2br(e($messageContent)) !!}
        </div>
      </div>
    </div>
    
    <div class="footer">
      <p>&copy; {{ date('Y') }} Asset Management System. All rights reserved.</p>
      <p style="font-size: 13px; margin-top: 8px; color: #9ca3af;">
        This is an automated message. Please do not reply directly to this email.
      </p>
    </div>
  </div>
</body>
</html>
