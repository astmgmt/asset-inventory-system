<?php

namespace App\Services;

class EmailTemplates
{
    /**
     * Generate HTML for borrow request notification
     *
     * @param string $borrowCode
     * @param string $requesterName
     * @param string $requesterEmail
     * @param string $department
     * @param string $remarks
     * @param array $requestedAssets
     * @return string HTML content
     */
    public static function borrowRequest($borrowCode, $requesterName, $requesterEmail, $department, $remarks, $requestedAssets)
    {
        $appName = config('app.name');
        $appUrl = config('app.url');
        $year = date('Y');
        
        $assetsHtml = '';
        foreach ($requestedAssets as $asset) {
            $assetsHtml .= "<tr>
                <td style='padding: 12px; border: 1px solid #e5e7eb; text-align: center; color: #1f2937;'>
                    {$asset['name']}
                </td>
                <td style='padding: 12px; border: 1px solid #e5e7eb; text-align: center; color: #4b5563;'>
                    {$asset['code']}
                </td>
                <td style='padding: 12px; border: 1px solid #e5e7eb; text-align: center; color: #111827;'>
                    {$asset['quantity']}
                </td>
                <td style='padding: 12px; border: 1px solid #e5e7eb; text-align: center; color: #111827;'>
                    ".($asset['purpose'] ?? 'N/A')."
                </td>
            </tr>";
        }

        $remarksText = $remarks ?: 'No remarks provided';        
        $appName = "Asset Management"; //config('app.name');

        $logoUrl = asset('images/logo.png');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Borrow Request $borrowCode - $appName</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; font-weight: bold; font-size: 1rem;}
        .logo { height: 50px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        .title { font-size: 24px; color: #2563eb; margin-bottom: 20px; text-align: center; }
        .table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
        .button { display: inline-block; background: #2563eb; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .info-item { margin-bottom: 10px; }
        .info-label { font-weight: 600; color: #1f2937; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://i.imgur.com/HF3xnxw.png" alt="logo" style="height: 50px;">
            <p>$appName</p>
        </div>

        
        <div class="card">
            <h1 class="title">🔔 New Borrow Request</h1>
            
            <div class="info-item">
                <span class="info-label">Requested by:</span> $requesterName ($requesterEmail)
            </div>
            <div class="info-item">
                <span class="info-label">Department:</span> $department
            </div>
            <div class="info-item">
                <span class="info-label">Remarks:</span> {$remarksText}
            </div>
            
            <h3 style="margin-top: 24px; margin-bottom: 16px;">Requested Assets</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center; font-weight: 600;">Asset Name</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center; font-weight: 600;">Asset Code</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center; font-weight: 600;">Quantity</th>
                        <th style="padding: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; text-align: center; font-weight: 600;">Purpose</th>
                    </tr>
                </thead>
                <tbody>
                    $assetsHtml
                </tbody>
            </table>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="http://asset-management.test/borrow/requests" 
                    style="display: inline-block; 
                        background-color: #2563eb; 
                        color: #ffffff; 
                        padding: 12px 24px; 
                        text-decoration: none; 
                        border-radius: 4px; 
                        font-weight: bold;"
                >
                    Review Request
                </a>
            </div>
        </div>
        
        <div class="footer">
            &copy; $year $appName. All rights reserved.<br>
            This is an automated message, please do not reply directly to this email.
        </div>
    </div>
</body>
</html>
HTML;
    }
}