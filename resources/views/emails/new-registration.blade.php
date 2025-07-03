<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>New User Registration: {{ $user->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        /* Same styling as security-alert email */
        body { font-family: 'Inter', 'Segoe UI', sans-serif; line-height: 1.6; color: #4b5563; background-color: #f9fafb; margin: 0; padding: 20px 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 30px; border: 1px solid #e5e7eb; }
        .logo { width: 42px; height: auto; }
        .company-name { color: #1a1a1a; font-weight: 700; margin: 8px 0 20px 0; }
        h1 { font-size: 20px; color: #1a1a1a; font-weight: 700; text-align: center; margin-bottom: 24px; }
        .content { color: #4b5563; line-height: 1.5; text-align: justify; }
        .user-detail-container { margin: 30px 0; }
        .user-table { width: 100%; border-collapse: collapse; font-size: 14px; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .user-table th { background-color: #4b5563; padding: 14px; border: 1px solid #9ca3af; font-weight: 600; text-align: center; color: #ffffff; }
        .user-table td { padding: 14px; border: 1px solid #e5e7eb; text-align: left; background-color: #f9fafb; color: #1a1a1a; }
        .detail-label { font-weight: 600; color: #1a1a1a; }
        .notice { background-color: #e0f2fe; border: 1px solid #bae6fd; border-radius: 6px; padding: 24px; text-align: left; font-size: 15px; color: #0c4a6e; margin: 25px 0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); display: flex; align-items: flex-start; gap: 12px; }
        .notice::before { content: "ℹ️"; font-size: 20px; line-height: 1; margin-top: 2px; }
        .action-link { color: #0c4a6e; text-decoration: underline; font-weight: 600; display: inline-block; margin-top: 10px; }
        .footer { text-align: center; color: #6b7280; font-size: 14px; padding-top: 20px; border-top: 1px solid #e5e7eb; margin-top: 40px; }
        .copyright { margin: 16px 0; }
        strong { color: #1a1a1a; }
        @media screen and (max-width: 640px) {
            .container { margin: 10px; width: auto !important; }
            .user-table th, .user-table td { padding: 10px; font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="container" role="main" aria-label="New User Registration Notification">
        <div class="header" style="text-align: center;">
            <img class="logo" src="https://i.imgur.com/HF3xnxw.png" alt="Company Logo" />
            <div class="company-name">Asset Inventory System</div>
        </div>

        <h1>👤 New User Registration: {{ $user->name }}</h1>

        <div class="content">
            <p>Hello Super Admin,</p>
            <p>A new user has registered and requires approval:</p>

            <div class="user-detail-container">
                <table class="user-table" aria-label="User details">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="detail-label">Name</td>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Username</td>
                            <td>{{ $user->username }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Email</td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Contact Number</td>
                            <td>{{ $user->contact_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Address</td>
                            <td>{{ $user->address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Registered At</td>
                            <td>{{ $time }}</td>
                        </tr>
                        <tr>
                            <td class="detail-label">Status</td>
                            <td>{{ $user->status }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="notice">
                <div>
                    <p><strong>Action Required:</strong> Please review this registration in the admin panel.</p>
                    <a href="{{ url('/admin/users') }}" class="action-link">Review Registrations</a>
                </div>
            </div>
            
            <p>Best regards,<br>
            <strong>Asset Inventory System</strong></p>
        </div>

        <div class="footer">
            <p class="copyright">&copy; {{ date('Y') }} Asset Inventory System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>