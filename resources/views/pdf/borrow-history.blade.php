<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>History Record - {{ $history->borrow_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            font-size: 11px; 
            margin: 0;
            padding: 20px;
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
            justify-content: space-between; 
        }
        .accountability-message {
            margin-top: 20px;
            font-size: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #eee;
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
            <p class="title">Transaction History Record</p>
            <p class="subtitle">Borrow Code: {{ $history->borrow_code }}</p>
            <p class="subtitle">Date: {{ $history->action_date->format('M d, Y H:i') }}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Transaction Information</div>
        <table class="info-pair-table">
            <tr>
                <td class="label">Borrower Name:</td>
                <td>{{ $user->name }}</td>
                <td class="label">Status:</td>
                <td>{{ $history->status }}</td>
            </tr>
            <tr>
                <td class="label">Department:</td>
                <td>{{ $user->department->name ?? 'N/A' }}</td>
                <td class="label">Date:</td>
                <td>{{ $history->action_date->format('M d, Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Borrow Code:</td>
                <td>{{ $history->borrow_code }}</td>
                <td class="label">Return Code:</td>
                <td>{{ $history->return_code ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    @if($history->borrow_data && is_array($history->borrow_data) && isset($history->borrow_data['borrow_items']))
        @php
            $borrowItems = $history->borrow_data['borrow_items'] ?? [];
        @endphp
        @if(count($borrowItems))
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
                    @foreach($borrowItems as $item)
                    @php
                        $asset = $item['asset'] ?? [];
                    @endphp
                    <tr>
                        <td>{{ $asset['asset_code'] ?? 'N/A' }}</td>
                        <td>{{ $asset['name'] ?? 'N/A' }}</td>
                        <td>{{ $item['quantity'] ?? 'N/A' }}</td>
                        <td>{{ $item['purpose'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>            
        </div>
        @endif
    @endif

    @if($history->return_data && is_array($history->return_data))
        @php
            $returnItems = $history->return_data['return_items'] ?? [];
        @endphp
    @if(count($returnItems))
        <div class="section">
            <div class="section-title">Return Details</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returnItems as $item)
                    @php
                        $borrowItem = $item['borrow_item'] ?? [];
                        $asset = $borrowItem['asset'] ?? [];
                    @endphp
                    <tr>
                        <td>{{ $asset['asset_code'] ?? 'N/A' }}</td>
                        <td>{{ $asset['name'] ?? 'N/A' }}</td>
                        <td>{{ $borrowItem['quantity'] ?? 'N/A' }}</td>
                        <td>{{ $item['status'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <p style="text-align: center; color: #888;">No return items available</p>
        @endif
    @endif

    
</body>
</html>