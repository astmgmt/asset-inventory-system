<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset QR Codes - {{ $printLog->print_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        @page { margin: 20px; }
        body {
            font-family: 'Inter', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .logo {
            height: 40px;
        }
        .title-container {
            text-align: center;
            flex-grow: 1;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            font-size: 10px;
            color: #555;
        }
        .section {
            margin-bottom: 10px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .info-pair-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        .info-pair-table td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-size: 10px;
        }
        .info-pair-table .label {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 20%;
        }
        .qrcode-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .qrcode-table th,
        .qrcode-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;        /* Center horizontal text */
            vertical-align: middle;    /* Center vertical alignment */
        }
        .qrcode-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .qrcode {
            width: 2in;
            height: 2in;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .qrcode img {
            max-width: 100%;
            max-height: 100%;
        }        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="text-align: center;">
            <img src="{{ public_path('images/logo.png') }}" style="height: 40px;" alt="Company Logo">
            <div style="font-size: 10px; color: #666;">
                Asset Inventory System
            </div>
        </div>

        <div class="title-container">
            <p class="title">Asset QR Codes</p>
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
        <div class="section-title">Asset QR Codes</div>
        <table class="qrcode-table">
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Name and Model</th>
                    <th>QR Code</th>
                </tr>
            </thead>

            <tbody>
                @foreach(collect($assets)->chunk(4) as $chunk)
                    @foreach($chunk as $item)
                        <tr>
                            <td width="20%">{{ $item['asset']->asset_code }}</td>
                            <td width="50%">
                                <strong>{{ $item['asset']->name }}</strong><br>
                                Model: {{ $item['asset']->model_number }}<br>
                                Serial: {{ $item['asset']->serial_number }}<br>
                                Location: {{ $item['asset']->location_name }}
                            </td>
                            <td width="30%" class="qrcode">
                                <img src="{{ $item['qrCode'] }}" alt="QR Code">
                            </td>
                        </tr>
                    @endforeach
                    {{-- Page break after each 6-row chunk --}}
                    <tr class="page-break-row"><td colspan="3"></td></tr>
                @endforeach
            </tbody>



        </table>
    </div>    
</body>
</html>
