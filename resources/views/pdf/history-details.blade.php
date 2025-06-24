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
            font-size: 11px;
        }
        .highlighted-message {
            background-color: #f4f7fb; 
            border-left: 4px solid #007BFF; 
            padding: 12px 16px;
            border-radius: 4px;
            color: #333;
            font-size: 11px;
            line-height: 1.5;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }
        .status-good {
            background-color: #d4edda;
            color: #155724;
        }
        .status-damaged {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

    <div class="header">
        <div style="display: inline-block; text-align: center; margin-bottom: 10px;">
            <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Company Logo">
            <div style="font-size: 10px; color: #666; margin-top: 2px;">
                Asset Inventory System
            </div>
        </div>

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
                {{-- <td>{{ $borrowDate }}</td> --}}
                <td>{{ \Carbon\Carbon::parse($borrowDate)->format('M d, Y') }}</td>
                <td class="label">Return Date:</td>                
                <td>
                    @if(isset($transaction->return_data['return_date']) && $transaction->return_data['return_date'])
                        {{ \Carbon\Carbon::parse($transaction->return_data['return_date'])->format('M d, Y') }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Approved By:</td>
                <td>{{ $transaction->return_data['approved_by'] ?? 'N/A' }}</td>
                <td class="label">Return Received By:</td>
                <td>{{ $transaction->return_data['return_received_by'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Remarks:</td>
                <td colspan="3">{{ $transaction->remarks ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

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
                        <td>{{ !empty($item['purpose']) ? $item['purpose'] : 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No borrowed assets</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

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
                            <td>{{ $item['quantity'] ?? ($item['borrow_item']['quantity'] ?? 'N/A') }}</td>
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
        <div class="accountability-message highlighted-message">            
            <strong>Note:</strong>This document serves as an official record of the asset borrowing and return transaction.
        </div>
    </div>

    <div class="footer">
        <div class="flex-container">
            <div class="signature">
                <p>Returned by:</p>
                <p style="margin-bottom: 24px;">&nbsp;</p>
                <p style="border-bottom: 1px solid #333; margin: 0; padding-bottom: 4px;">&nbsp;</p>
                <p style="text-align: center; margin-top: 4px; margin-bottom: 8px;">
                    {{ $transaction->user['name'] }}
                </p>
                <p style="text-align: center; margin-top: 4px;">
                    Signature
                </p>
            </div>

            <div class="signature">
                <p>Approved by:</p>
                <p style="margin-bottom: 24px;">&nbsp;</p>
                <p style="border-bottom: 1px solid #333; margin: 0; padding-bottom: 4px;">&nbsp;</p>
                <p style="text-align: center; margin-top: 4px; margin-bottom: 8px;">
                    {{ $transaction->return_data['approved_by'] ?? 'N/A' }}
                </p>
                <p style="text-align: center; margin-top: 4px;">
                    Authorized Signature
                </p>
            </div>            
        </div>
    </div>

</body>
</html>
