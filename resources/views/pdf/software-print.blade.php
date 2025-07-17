<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Software Report - {{ $printLog->print_code }}</title>
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
            padding: 6px;
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
        .col-code { width: 13%; }
        .col-name { width: 20%; }
        .col-description { width: 25%; }
        .col-license { width: 15%; }
        .col-date-acquired { width: 12%; }
        .col-expiry { width: 10%; }
        .col-added { width: 10%; }
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
            <p class="title">Software Inventory Report</p>
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
                <td class="label">Total Software:</td>
                <td>{{ count($softwares) }}</td>
                <td class="label">Printed By:</td>
                <td>{{ auth()->user()->name }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Software Details</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-code">Software Code</th>
                    <th class="col-name">Software Name</th>
                    <th class="col-description">Description</th>
                    <th class="col-license">License Key</th>
                    <th class="col-install">Date Acquired</th>
                    <th class="col-expiry">Expiry Date</th>
                    <th class="col-added">Added By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($softwares as $software)
                <tr>
                    <td>{{ $software['software_code'] }}</td>
                    <td>{{ $software['software_name'] }}</td>
                    <td>{{ $software['description'] }}</td>
                    <td>{{ $software['license_key'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($software['date_acquired'])->format('M d, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($software['expiry_date'])->format('M d, Y') }}</td>
                    <td>{{ $software['added_by'] }}</td>
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