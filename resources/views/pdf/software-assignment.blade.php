<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Software Assignment - {{ $assignment->reference_no }}</title>
    <link rel="stylesheet" href="{{ public_path('css/print-pdf.css') }}">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="header-text">
                <h1>Asset Management</h1>
                <h2>Software Assignment Form</h2>
            </div>
        </div>
    </div>

    <div class="assignee-info">
        <div class="section">
            <div class="section-title">Assignee Information:</div>
            <div>Name: {{ $assignment->user->name }}</div>
            <div>Contact No.: {{ $assignment->user->contact_number ?? 'N/A' }}</div>
            <div>Email: {{ $assignment->user->email }}</div>
        </div>

        <div class="section">
            <div class="section-title">Assignment Details:</div>
            <div>Assigned Date: {{ $assignment->date_assigned->format('M d, Y') }}</div>
            <div>Assigned by: {{ $assignment->admin->name }}</div>
            <div>Reference No.: {{ $assignment->reference_no }}</div>
        </div>
    </div>

    <table class="details-table">
        <thead>
            <tr>
                <th>Software Code</th>
                <th>Software Name</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Purpose</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $assignment->software->software_code }}</td>
                <td>{{ $assignment->software->software_name }}</td>
                <td>{{ $assignment->software->description }}</td>
                <td>1</td>
                <td>{{ $assignment->purpose }}</td>
                <td>{{ $assignment->remarks ?? 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        <div>Approved by:</div>
        <div class="signature-line"></div>
        <div>{{ $assignment->admin->name }}</div>
        <div>Administrator</div>
        <div>(Signature over printed name)</div>
    </div>
</body>
</html>