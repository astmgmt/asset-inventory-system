<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Borrow Approval - {{ $transaction->borrow_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            font-size: 11px; /* Base font size */
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .logo {
            height: 50px;
        }
        .title-container {
            text-align: center;
            flex-grow: 1;
            margin-right: 50px; /* prevents overlap with logo */
        }
        .title {
            font-size: 14px; /* Keep this larger */
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            font-size: 11px;
            color: #555;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 11px; /* Match body font size */
            font-weight: bold;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .info-pair-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        .info-pair-table td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .info-pair-table .label {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 30%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th,
        .items-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
        }
        .signature {
            width: 250px;
        }
        .flex-container {
            display: flex;
            justify-content: flex-start;
            gap: 80px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div style="display: inline-block; text-align: center; margin-bottom: 10px;">
            <img src="{{ public_path('images/logo.png') }}" style="height: 50px;" alt="Company Logo">
            <div style="font-size: 10px; color: #666; margin-top: 2px;">
                Asset Inventory System
            </div>
        </div>

        <div class="title-container">
            <p class="title">Borrower's Accountability Form</p>
            <p class="subtitle">Borrow Code: {{ $transaction->borrow_code }}</p>
            <p class="subtitle">Approval Date: {{ $approvalDate }}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Borrower & Approver Information</div>
        <table class="info-pair-table">
            <tr>
                <td class="label">Borrower Name:</td>
                <td>{{ $transaction->user->name }}</td>
                <td class="label">Approver Name:</td>
                <td>{{ $approver->name }}</td>
            </tr>
            <tr>
                <td class="label">Department:</td>
                <td>{{ $transaction->userDepartment->name ?? 'N/A' }}</td>
                <td class="label">Department:</td>
                <td>{{ $approver->department->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Requested By:</td>
                <td>{{ $transaction->requestedBy->name ?? 'N/A' }}</td>
                <td class="label">Approval Date:</td>
                <td>{{ $approvalDate }}</td>
            </tr>
            <tr>
                <td class="label">Borrow Date:</td>
                <td>{{ $transaction->borrowed_at ? $transaction->borrowed_at->format('M d, Y H:i') : 'N/A' }}</td>
                <td class="label">Remarks:</td>
                <td>{{ $transaction->remarks ?: 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Borrowed Assets</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Asset Name</th>
                    <th>Quantity</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->borrowItems as $item)
                <tr>
                    <td>{{ $item->asset->asset_code }}</td>
                    <td>{{ $item->asset->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->purpose ?: 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p class="accountability-message">
            By signing this form, the borrower acknowledges receipt of the items and agrees to use them properly for their intended purpose. They accept full responsibility for the care, safekeeping, and timely return of the assets in good condition. Any loss, damage, or misuse may result in liability for repair or replacement costs.
        </p>
    </div>

    <div class="footer">
        <div class="flex-container">
            <!-- Borrower Signature Block -->
            <div class="signature">
                <p>Borrowed or Received by:</p>
                <p style="margin-bottom: 24px;">&nbsp;&nbsp;</p>
                <p style="border-bottom: 1px solid #333; margin: 0; padding-bottom: 4px;">&nbsp;</p>
                <p style="text-align: center; margin-top: 4px; margin-bottom: 8px;">
                    {{ $transaction->user->name }}
                </p>
                <p style="border-bottom: 1px solid #333; margin: 0; padding-bottom: 4px;">&nbsp;</p>
                <p style="text-align: center; margin-top: 4px;">
                    Date Received
                </p>
            </div>

            <!-- Approver Signature Block -->
            <div class="signature">
                <p>Approved by:</p>
                <p style="margin-bottom: 24px;">&nbsp;&nbsp;</p>
                <p style="border-bottom: 1px solid #333; margin: 0; padding-bottom: 4px;">&nbsp;</p>
                <p style="text-align: center; margin-top: 4px; margin-bottom: 8px;">
                    {{ $approver->name }}
                </p>
                <p style="border-bottom: 1px solid #333; margin: 0; padding-bottom: 4px;">&nbsp;</p>
                <p style="text-align: center; margin-top: 4px;">
                    Date Approved
                </p>
            </div>
        </div>
    </div>

</body>
</html>
