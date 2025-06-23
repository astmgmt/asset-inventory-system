<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Asset QR Sticker - {{ $asset->asset_code }}</title>
    <style>
        @page {
            size: 4in 3.7in;
            margin: 0;
        }
        html, body {
            margin: 0; padding: 0;
            width: 4in; height: 3.7in;
            font-family: Arial, sans-serif;
            font-size: 10px;
            display: flex;
            justify-content: center; /* horizontal center */
            align-items: center;     /* vertical center */
            /* prevents page break issues in some PDF engines */
            box-sizing: border-box;
        }
        .sticker {
            width: 3.9in;
            height: 3.6in;
            display: table;
            table-layout: fixed;
            border: 1px dotted #ccc;
            box-sizing: border-box;
        }
        .left-column, .right-column {
            display: table-cell;
            vertical-align: top;
            padding: 0.15in;
            box-sizing: border-box;
        }
        .left-column {
            width: 2in;
        }
        .right-column {
            width: 1.9in;
            border-left: 1px solid #eee;
            text-align: center;
            vertical-align: top;
        }
        .header {
            display: inline-block;
            text-align: center;
            margin-bottom: 10px;
            width: 100%;
        }
        .header img {
            height: 50px;
        }
        .company-name {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }
        .label {
            margin-bottom: 0.1in;
            line-height: 1.3;
        }
        .label strong {
            display: inline-block;
            width: 0.7in;
        }
        .qrcode-container img {
            width: 2in;
            height: 2in;
            border: 1px solid #000;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="sticker">
        <div class="left-column">
            <div class="header">
                <img src="{{ public_path('images/logo.png') }}" alt="Company Logo" />
                <div class="company-name">Asset Inventory System</div>
            </div>
            <div class="label"><strong>Name:</strong> {{ $asset->name }}</div>
            <div class="label"><strong>Model:</strong> {{ $asset->model_number }}</div>
            <div class="label"><strong>SN:</strong> {{ $asset->serial_number }}</div>
        </div>
        <div class="right-column qrcode-container">
            <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" />
        </div>
    </div>
</body>
</html>
