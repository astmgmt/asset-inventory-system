<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Report - {{ $printLog->print_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        @page { margin: 20px; }
        body {
            font-family: 'Inter', sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .logo {
            height: 40px;
        }
        .title-container {
            text-align: center;
            flex-grow: 1;
        }
        .title {
            font-size: 12px;
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            font-size: 9px;
            color: #555;
        }
        .section {
            margin-bottom: 10px;
        }
        .section-title {
            font-size: 9px;
            font-weight: bold;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .info-pair-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        .info-pair-table td {
            padding: 4px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-size: 8px;
        }
        .info-pair-table .label {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 20%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            table-layout: fixed;
        }
        .items-table th,
        .items-table td {
            padding: 4px;
            border: 1px solid #ddd;
            word-wrap: break-word;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .items-table thead th {
            text-align: center;
        }
        .items-table tbody td {
            text-align: center;
        }

       .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
            font-size: 9px;
        }

        .signature-block {
            width: 48%;
            text-align: left;
        }

        .signature-line {
            width: 30%;
            border-bottom: 1px solid #333;
            height: 16px;
            margin: 8px 0;
        }

        .signature-block p {
            margin: 2px 0;
        }

        
        /* Column Widths */
        .col-asset-code { width: 10%; }
        .col-name { width: 16%; }
        .col-serial { width: 10%; }
        .col-model { width: 10%; }
        .col-category { width: 10%; }
        .col-condition { width: 8%; }
        .col-location { width: 10%; }
        .col-vendor { width: 10%; }
        .col-date-acquired { width: 8%; }
        .col-warranty { width: 8%; }        
    </style>
</head>
<body>

    <div class="header">
        <div style="text-align: center;">
            <img src="{{ public_path('images/logo.png') }}" style="height: 40px;" alt="Company Logo">
            <div style="font-size: 8px; color: #666;">
                Asset Inventory System
            </div>
        </div>

        <div class="title-container">
            <p class="title">Asset Inventory Report</p>
            <p class="subtitle">Print Code: {{ $printLog->print_code }}</p>
            <p class="subtitle">Printed At: {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Report Period</div>
        <table class="info-pair-table">
            <tr>
                <td class="label">Date From:</td>
                <td>{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}</td>
                <td class="label">Date To:</td>
                <td>{{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td class="label">Total Assets:</td>
                <td>{{ count($assets) }}</td>
                <td class="label">Printed By:</td>
                <td>{{ auth()->user()->name }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Asset Details</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-asset-code">Asset Code</th>
                    <th class="col-name">Brand</th>
                    <th class="col-model">Model</th>
                    <th class="col-serial">Serial No.</th>                    
                    <th class="col-category">Category</th>
                    <th class="col-condition">Condition</th>
                    <th class="col-location">Location</th>
                    <th class="col-vendor">Vendor</th>
                    <th class="col-date-acquired">Date Acquired</th>
                    <th class="col-warranty">Warranty Exp.</th>
                    
                </tr>
            </thead>            
            <tbody>
                @foreach($assets as $asset)
                    @php
                        $category = is_string($asset['category']) ? json_decode($asset['category'], true) : $asset['category'];
                        $condition = is_string($asset['condition']) ? json_decode($asset['condition'], true) : $asset['condition'];
                        $location = is_string($asset['location']) ? json_decode($asset['location'], true) : $asset['location'];
                        $vendor = is_string($asset['vendor']) ? json_decode($asset['vendor'], true) : $asset['vendor'];
                    @endphp
                    <tr>
                        <td>{{ $asset['asset_code'] }}</td>
                        <td>{{ $asset['name'] }}</td>
                        <td>{{ $asset['model_number'] }}</td>
                        <td>{{ $asset['serial_number'] }}</td>                        
                        <td class="wrap-text">{{ $category['category_name'] ?? $asset['category'] }}</td>
                        <td class="wrap-text">{{ $condition['condition_name'] ?? $asset['condition'] }}</td>
                        <td class="wrap-text">{{ $location['location_name'] ?? $asset['location'] }}</td>
                        <td class="wrap-text">{{ $vendor['vendor_name'] ?? $asset['vendor'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($asset['date_acquired'])->format('M d, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($asset['warranty_expiration'])->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div class="signature-block">
            <p>Prepared by:</p>
            <div class="signature-line"></div>
            <p>{{ auth()->user()->name }}</p>
            <p>Date: __________________</p>
        </div>
    </div>
</body>
</html>
