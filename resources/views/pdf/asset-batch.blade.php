<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Batch Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
    <style>
        /* ... existing styles ... */
    </style>
</head>
<body>
    @foreach($assets as $index => $asset)
        <div class="page">
            <!-- Header with Logo and Title -->
            <div class="header">
                <div class="logo-container">
                    <img src="{{ public_path('images/logo.png') }}" alt="Company Logo">
                    <div class="company-name">Asset Inventory System</div>
                </div>
                <div class="title-container">
                    <p class="title">Asset Information</p>
                    <p class="subtitle">Asset Code: {{ $asset['asset_code'] }}</p>
                </div>
            </div>

            <!-- Asset Details -->
            <div class="section">
                <div class="section-title">Asset Details</div>
                <table class="info-table">
                    <tr>
                        <th>Asset Code</th>
                        <td>{{ $asset['asset_code'] }}</td>
                    </tr>
                    <tr>
                        <th>Serial Number</th>
                        <td>{{ $asset['serial_number'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Brand</th>
                        <td>{{ $asset['name'] }}</td>
                    </tr>
                    <tr>
                        <th>Model</th>
                        <td>{{ $asset['model_number'] }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $asset['description'] }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>{{ $asset['category']['category_name'] }}</td>
                    </tr>
                    <tr>
                        <th>Condition</th>
                        <td>{{ $asset['condition']['condition_name'] }}</td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>{{ $asset['location']['location_name'] }}</td>
                    </tr>
                    <tr>
                        <th>Vendor</th>
                        <td>{{ $asset['vendor']['vendor_name'] }}</td>
                    </tr>
                    <tr>
                        <th>Warranty Expiration</th>
                        <td>{{ \Carbon\Carbon::parse($asset['warranty_expiration'])->format('M d, Y') }}</td>
                    </tr>
                </table>
            </div>

            <!-- QR Codes -->
            <div class="section">
                <div class="section-title">QR Codes</div>
                <div class="qrcode-container">
                    <div class="qrcode">
                        <img src="data:image/png;base64,{{ base64_encode(app()->call('App\Http\Controllers\SuperAdmin\AssetPdfController@generateQrCode', [
                            'assetCode' => $asset['asset_code'],
                            'name' => $asset['name'],
                            'modelNumber' => $asset['model_number'],
                            'serialNumber' => $asset['serial_number'] ?? null,
                            'locationName' => $asset['location']['location_name'],
                            'description' => $asset['description']
                        ])) }}">
                    </div>
                    <div class="qrcode">
                        <img src="data:image/png;base64,{{ base64_encode(app()->call('App\Http\Controllers\SuperAdmin\AssetPdfController@generateQrCode', [
                            'assetCode' => $asset['asset_code'],
                            'name' => $asset['name'],
                            'modelNumber' => $asset['model_number'],
                            'serialNumber' => $asset['serial_number'] ?? null,
                            'locationName' => $asset['location']['location_name'],
                            'description' => $asset['description']
                        ])) }}">
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="footer">
                Generated on {{ now()->format('M d, Y') }} | Page {{ $index + 1 }} of {{ count($assets) }}
            </div>
        </div>
    @endforeach
</body>
</html>