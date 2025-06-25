<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Software Assignment - {{ $batch->assignment_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .logo-container {
            display: inline-block;
            text-align: center;
            margin-bottom: 10px;
        }
        .logo-container img {
            height: 50px;
        }
        .logo-container div {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }
        .title-container {
            text-align: center;
            flex-grow: 1;
            margin-right: 50px;
        }
        .title {
            font-size: 14px;
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
            font-size: 11px;
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
            margin-top: 10px;
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
        .accountability-message {
            font-size: 10px;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="{{ public_path('images/logo.png') }}" alt="Company Logo">
            <div>Asset Inventory System</div>
        </div>

        <div class="title-container">
            <p class="title">Software Assignment Form</p>
            <p class="subtitle">Assignment No: {{ $batch->assignment_no }}</p>
            <p class="subtitle">Date: {{ now()->format('M d, Y') }}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Assignment Details</div>
        <table class="info-pair-table">
            <tr>
                <td class="label">Assigned To:</td>
                <td>{{ $batch->user->name }}</td>                
                <td class="label">Assigned By:</td>
                <td>{{ $batch->assignedByUser->name }}</td>
            </tr>
            <tr>
                <td class="label">Borrower's Email:</td>
                <td>{{ $batch->user->email }}</td>
                <td class="label">Approver's Email:</td>
                <td>{{ $batch->approvedByUser->email }}</td>
            </tr>
            <tr>
                <td class="label">Approved By:</td>
                <td>{{ $batch->approvedByUser->name }}</td>                
                <td class="label">Assignment Date:</td>
                <td>{{ $batch->date_assigned->format('M d, Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Assigned Software</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Software Code</th>
                    <th>Software Name</th>
                    <th>License Key</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batch->assignmentItems as $item)
                <tr>
                    <td>{{ $item->software->software_code }}</td>
                    <td>{{ $item->software->software_name }}</td>
                    <td>{{ $item->software->license_key }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p class="accountability-message">
            By acknowledging this form, the assignee confirms receipt and use of the assigned software. The user agrees to comply with software licensing terms, avoid unauthorized duplication, and ensure the software is only used for official purposes. Violation may result in revocation of access or legal accountability.
        </p>
    </div>

    <div class="footer">
        <div class="flex-container">
            <!-- Assigned To Signature Block -->
            <div class="signature">
                <p>Received by:</p>
                <p style="margin-bottom: 24px;">&nbsp;&nbsp;</p>
                <p style="border-bottom: 1px solid #333; margin: 0; padding-bottom: 4px;">&nbsp;</p>
                <p style="text-align: center; margin-top: 4px; margin-bottom: 8px;">
                    {{ $batch->user->name }}
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
