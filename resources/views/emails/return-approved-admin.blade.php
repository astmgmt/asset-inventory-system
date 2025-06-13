<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Return Approved: {{ $returnCode }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; }
        .logo { height: 50px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        .title { font-size: 24px; color: #10B981; margin-bottom: 20px; text-align: center; }
        .table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .info-item { margin-bottom: 10px; }
        .info-label { font-weight: 600; color: #1f2937; }
        .signature-section { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="logo" style="height: 50px;">
            <p>Asset Management System</p>
        </div>

        <div class="card">
            <h1 class="title">✅ Return Approved: {{ $returnCode }}</h1>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
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
            
            <h3 style="margin-top: 24px; margin-bottom: 16px;">Returned Assets</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Asset Name</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Asset Code</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returnItems as $item)
                    <tr>
                        <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                            {{ $item->borrowItem->asset->name }}
                        </td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                            {{ $item->borrowItem->asset->asset_code }}
                        </td>
                        <td style="padding: 12px; border: 1px solid #e5e7eb; text-align: center;">
                            {{ $item->borrowItem->quantity }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="signature-section">
                <div class="signature-box">
                    <p class="info-label">Returnee Signature:</p>
                    <p>_________________________</p>
                    <p>Date: ___________________</p>
                </div>
                <div class="signature-box">
                    <p class="info-label">Approver Signature:</p>
                    <p>_________________________</p>
                    <p>Date: ___________________</p>
                </div>
            </div>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} Asset Management System. All rights reserved.<br>
            This is an automated message. Please do not reply directly to this email.
        </div>
    </div>
</body>
</html>