<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset History - {{ $transaction->borrow_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            line-height: 1.5;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .title-container {
            text-align: center;
            flex-grow: 1;
            margin: 0 20px;
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
            page-break-inside: avoid;
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
            margin-bottom: 15px;
        }
        .items-table th,
        .items-table td {
            padding: 6px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature {
            width: 250px;
        }
        .flex-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .accountability-message {
            margin-top: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border-left: 4px solid #3490dc;
            font-style: italic;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo {
            height: 50px;
        }
        .logo-text {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-good {
            background-color: #e6fffa;
            color: #0d9488;
        }
        .status-damaged {
            background-color: #fff1f2;
            color: #e11d48;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Company Logo">
        <div class="logo-text">Asset Inventory System</div>
    </div>

    <div class="header">
        <div class="title-container">
            <p class="title">Asset Borrow & Return History</p>
            <p class="subtitle">Borrow Code: {{ $transaction->borrow_code }}</p>
            @if($transaction->return_code)
                <p class="subtitle">Return Code: {{ $transaction->return_code }}</p>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Transaction Information</div>
        <table class="info-pair-table">
            <tr>
                <td class="label">Borrower Name:</td>
                <td>{{ $transaction->user['name'] }}</td>
                <td class="label">Department:</td>
                <td>{{ $transaction->user['department'] }}</td>
            </tr>
            <tr>
                <td class="label">Borrow Date:</td>
                <td>{{ $borrowDate }}</td>
                <td class="label">Return Date:</td>
                <td>{{ $returnDate }}</td>
            </tr>
            <tr>
                <td class="label">Approved By:</td>
                <td>{{ $approver->name }}</td>
                <td class="label">Return Received By:</td>
                <td>{{ $returner->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Remarks:</td>
                <td colspan="3">{{ $transaction->remarks }}</td>
            </tr>
        </table>
    </div>

    <!-- Borrow Section -->
    <div class="section">
        <div class="section-title">Borrowed Assets</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Serial #</th>
                    <th>Quantity</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaction->borrowItems as $item)
                    <tr>
                        <td>{{ $item['asset']['asset_code'] ?? 'N/A' }}</td>
                        <td>{{ $item['asset']['name'] ?? 'N/A' }}</td>
                        <td>{{ $item['asset']['model_number'] ?? 'N/A' }}</td>
                        <td>{{ $item['asset']['serial_number'] ?? 'N/A' }}</td>
                        <td>{{ $item['quantity'] ?? 'N/A' }}</td>
                        <td>{{ $item['purpose'] ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No borrowed assets</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Return Section -->
    @if(count($transaction->returnItems) > 0)
        <div class="section">
            <div class="section-title">Returned Assets</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Serial #</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->returnItems as $item)
                        <tr>
                            <td>{{ $item['asset']['asset_code'] ?? ($item['borrow_item']['asset']['asset_code'] ?? 'N/A') }}</td>
                            <td>{{ $item['asset']['name'] ?? ($item['borrow_item']['asset']['name'] ?? 'N/A') }}</td>
                            <td>{{ $item['asset']['model_number'] ?? ($item['borrow_item']['asset']['model_number'] ?? 'N/A') }}</td>
                            <td>{{ $item['asset']['serial_number'] ?? ($item['borrow_item']['asset']['serial_number'] ?? 'N/A') }}</td>
                            <td>{{ $item['quantity_returned'] ?? $item['quantity'] ?? 'N/A' }}</td>
                            <td>
                                <span class="status-badge {{ $item['status'] === 'Good' ? 'status-good' : 'status-damaged' }}">
                                    {{ $item['status'] ?? 'Returned' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="section">
        <div class="accountability-message">
            <strong>Accountability Statement:</strong><br>
            This document serves as an official record of the asset borrowing and return transaction.
            The borrower acknowledges that all items were received in the condition specified above.
            Any discrepancies or damages beyond normal wear and tear may result in liability for repair or replacement costs.
        </div>
    </div>

    <div class="footer">
        <div class="flex-container">
            <!-- Borrower Signature Block -->
            <div class="signature">
                <p>Borrowed & Returned by:</p>
                <p style="margin-bottom: 24px;">&nbsp;&nbsp;</p>
                <p style="border-bottom: 1px solid #333; margin: 0; padding-bottom: 4px;">&nbsp;</p>
                <p style="text-align: center; margin-top: 4px; margin-bottom: 8px;">
                    {{ $transaction->user['name'] }}
                </p>
                <p style="text-align: center; margin-top: 4px;">
                    Borrower's Signature
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
                <p style="text-align: center; margin-top: 4px;">
                    Authorized Signature
                </p>
            </div>
            
            <!-- Return Receiver Block -->
            @if($returner->name)
                <div class="signature">
                    <p>Received by:</p>
                    <p style="margin-bottom: 24px;">&nbsp;&nbsp;</p>
                    <p style="border-bottom: 1px solid #333; margin: 0; padding-bottom: 4px;">&nbsp;</p>
                    <p style="text-align: center; margin-top: 4px; margin-bottom: 8px;">
                        {{ $returner->name }}
                    </p>
                    <p style="text-align: center; margin-top: 4px;">
                        Receiver's Signature
                    </p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>