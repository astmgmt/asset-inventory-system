<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 0;
            size: 4in 3.7in;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 288pt;   /* 4in */
            height: 266.4pt; /* 3.7in */
            font-family: sans-serif;
            position: relative;
        }

        .left-column, .right-column {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }

        .left-column {
            left: 20pt;
            width: 120pt;        
            word-wrap: break-word;  
            overflow-wrap: break-word;
            overflow: hidden;      /* hide overflow */
        }

        .right-column {
            right: 20pt;
            width: 144pt;
            height: 144pt;
            text-align: right;            
            margin-left: 10pt;
        }

       .header {
            margin-bottom: 10pt;
        }

        .logo-container {
            display: inline-block;
            text-align: center;
            width: auto;
            min-width: 100pt; /* Or match to company name width */
        }

        .logo-container img {
            max-width: 40pt;
            display: block;
            margin: 0 auto;
        }

        .company-name {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 5pt;
        }

        .spacer {
            height: 10pt;
        }

        .label {
            font-size: 8pt;
            margin-bottom: 4pt;
        }

        .qrcode-container img {
            width: 144pt;
            height: 144pt;
            object-fit: contain;
        }
    </style>
</head>
<body>

    <div class="left-column">
        <div class="header">
            <div class="logo-container">
                <img src="{{ public_path('images/logo.png') }}" alt="Company Logo" />
            </div>
            <div class="company-name">Asset Inventory System</div>
        </div>

        <div class="spacer"></div>
        <div class="label"><strong>Brand:</strong> {{ $asset->name }}</div>
        <div class="label"><strong>Model:</strong> {{ $asset->model_number }}</div>
        <div class="label"><strong>SN:</strong> {{ $asset->serial_number }}</div>
        <div class="label">
            <strong>Date:</strong> {{ $asset->created_at->format('M j, Y') }}
        </div>

    </div>

    <div class="right-column qrcode-container">
        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" />
    </div>

</body>
</html>
