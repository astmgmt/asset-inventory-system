<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset: {{ $asset->asset_code }}</title>
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
        .logo-container {
            display: inline-block;
            text-align: center;
        }
        .logo-container img {
            height: 50px;
        }
        .company-name {
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
            margin: 0;
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
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        .info-table th,
        .info-table td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
            text-align: left;
        }
        .info-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 30%;
        }
        .qrcode-container {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 30px;
        }
        .qrcode {
            text-align: center;
        }
        .qrcode img {
            width: 2in;
            height: 2in;
            border: 2px dashed #000; 
            padding: 4px;            
            margin: 10px;
            background-color: #fff;   
            box-sizing: border-box;  
        }
        .footer {
            margin-top: 40px;
            font-size: 10px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>

    <!-- Header with Logo and Title -->
    <div class="header">
        <div class="logo-container">
            <img src="{{ public_path('images/logo.png') }}" alt="Company Logo">
            <div class="company-name">Asset Inventory System</div>
        </div>
        <div class="title-container">
            <p class="title">Asset Information</p>
            <p class="subtitle">Asset Code: {{ $asset->asset_code }}</p>
        </div>
    </div>

    <!-- Asset Details -->
    <div class="section">
        <div class="section-title">Asset Details</div>
        <table class="info-table">
            <tr>
                <th>Asset Code</th>
                <td>{{ $asset->asset_code }}</td>
            </tr>
            <tr>
                <th>Serial Number</th>
                <td>{{ $asset->serial_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Brand</th>
                <td>{{ $asset->name }}</td>
            </tr>
            <tr>
                <th>Model</th>
                <td>{{ $asset->model_number }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $asset->description }}</td>
            </tr>
            <tr>
                <th>Category</th>
                <td>{{ $asset->category->category_name }}</td>
            </tr>
            <tr>
                <th>Condition</th>
                <td>{{ $asset->condition->condition_name }}</td>
            </tr>
            <tr>
                <th>Location</th>
                <td>{{ $asset->location->location_name }}</td>
            </tr>
            <tr>
                <th>Vendor</th>
                <td>{{ $asset->vendor->vendor_name }}</td>
            </tr>
            <tr>
                <th>Warranty Expiration</th>
                <td>{{ $asset->warranty_expiration->format('M d, Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- QR Codes -->
    <div class="section">
        <div class="section-title">QR Codes</div>
        <div class="qrcode-container">
            <div class="qrcode">
                <img src="data:image/png;base64,{{ $qrCode }}" width="2in" height="2in">
            </div>
            <div class="qrcode">
                <img src="data:image/png;base64,{{ $qrCode }}" width="2in" height="2in">
            </div>
        </div>
    </div>

</body>
</html>