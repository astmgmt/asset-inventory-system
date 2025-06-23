<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset QR Stickers</title>
    <style>
        @page { 
            margin: 0.2in;
            size: letter;
        }
        body {
            margin: 0 auto;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 8px;
            justify-content: center;
            align-items: center;
        }
        .qr-grid-container {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0.1in;
           
        }
        .qr-row {
            display: table-row;
            page-break-inside: avoid;
        }
        .qr-cell {
            display: table-cell;
            width: 25%; /* 4 per row */
            text-align: center;
            vertical-align: top;
            page-break-inside: avoid;
            padding: 0.1in;
            box-sizing: border-box;
        }
        .qr-label {
            font-size: 8px;
            line-height: 1.2;
            margin-bottom: 5px;
            min-height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;            
        }
        .qrcode {
            width: 2in;
            height: 2in;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
            /* border: 1px solid #404040; */
        }
        .qrcode img {
            width: 100%;
            height: auto;
            max-width: 100%;
        }
        .page-break {
            page-break-after: always;
        }
        /* Optional: 3 per row layout */
        @media (max-width: 8.5in) {
            .qr-cell {
                width: 33.33%;
            }
        }
    </style>
</head>
<body>
    <div class="qr-grid-container">
        @foreach($assets as $index => $item)
            @if($index % 4 == 0)
                <div class="qr-row">
            @endif
            
            <div class="qr-cell">
                <div class="qr-label">
                    {{ $item['asset']->name }}, 
                    {{ $item['asset']->model_number }}, 
                    {{ $item['asset']->serial_number }}
                </div>
                <div class="qrcode">
                    <img src="{{ $item['qrCode'] }}" alt="QR Code">
                </div>
            </div>
            
            @if(($index + 1) % 4 == 0 || $loop->last)
                </div>
            @endif
        @endforeach
    </div>
</body>
</html>