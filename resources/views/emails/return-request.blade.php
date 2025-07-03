<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Return Receipt - {{ $returnCode }}</title>

<link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
<style>
    body, p, h1, h2, h3, div, table, th, td {
        margin: 0;
        padding: 0;
        border: 0;
        font-weight: normal;
    }
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
            Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
        background-color: #f9fafb;
        color: #1f2937;
        line-height: 1.5;
        padding: 20px 0;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    }
    .container {
        max-width: 600px;
        background-color: #ffffff;
        margin: 0 auto;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid #e5e7eb;
        padding: 30px 30px 40px 30px;
    }
    .header {
        text-align: center;
        padding-bottom: 20px;
        user-select: none;
    }
    .header .title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .header .subtitle {
        font-size: 16px;
        color: #4b5563;
        margin-bottom: 4px;
        font-weight: 600;
    }
    .section {
        margin-bottom: 30px;
    }
    .section-title {
        font-size: 18px;
        font-weight: 700;
        border-bottom: 2px solid #374151;
        padding-bottom: 6px;
        margin-bottom: 16px;
        color: #374151;
        user-select: none;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    .info-table td {
        padding: 12px 10px;
        border: 1px solid #d1d5db;
        vertical-align: top;
    }
    .info-table .label {
        font-weight: 600;
        background-color: #f3f4f6;
        width: 30%;
        color: #4b5563;
        user-select: none;
    }
    .items-table th, .items-table td {
        padding: 12px 10px;
        border: 1px solid #d1d5db;
        text-align: left;
        vertical-align: middle;
    }
    .items-table th {
        background-color: #f3f4f6;
        font-weight: 700;
        color: #374151;
        user-select: none;
    }
    .footer {
        text-align: center;
        font-size: 14px;
        color: #6b7280;
        user-select: none;
    }
    .signature {
        margin-top: 40px;
        border-top: 1px solid #374151;
        width: 300px;
        padding-top: 12px;
        margin-left: auto;
        margin-right: auto;
        color: #374151;
        font-weight: 600;
    }
    /* Responsive */
    @media screen and (max-width: 640px) {
        .container {
            margin: 10px;
            width: auto !important;
            padding: 20px;
        }
        .header .title {
            font-size: 20px;
        }
        .section-title {
            font-size: 16px;
        }
        .info-table td, .items-table th, .items-table td {
            padding: 8px 6px;
        }
        .signature {
            width: 100%;
        }
    }
</style>
</head>
<body>
    <div class="container" role="main" aria-label="Asset Return Receipt">
        <div class="header">
            <div class="title">Asset Return Receipt</div>
            <div class="subtitle">Return Code: {{ $returnCode }}</div>
            <div class="subtitle">Date: {{ $returnDate }}</div>
        </div>

        <div class="section" aria-labelledby="user-info-label">
            <div class="section-title" id="user-info-label">User Information</div>
            <table class="info-table" role="table" aria-describedby="user-info-label">
                <tbody>
                    <tr>
                        <td class="label">Name:</td>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td class="label">Department:</td>
                        <td>{{ $user->department->name ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section" aria-labelledby="returned-assets-label">
            <div class="section-title" id="returned-assets-label">Returned Assets</div>
            <table class="items-table" role="table" aria-describedby="returned-assets-label">
                <thead>
                    <tr>
                        <th scope="col">Asset Code</th>
                        <th scope="col">Asset Name</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Borrow Code</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returnItems as $item)
                    <tr>
                        <td>{{ $item->borrowItem->asset->asset_code }}</td>
                        <td>{{ $item->borrowItem->asset->name }}</td>
                        <td>{{ $item->borrowItem->quantity }}</td>
                        <td>{{ $item->borrowItem->transaction->borrow_code }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>        
    </div>
</body>
</html>
