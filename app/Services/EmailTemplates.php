<?php

namespace App\Services;

class EmailTemplates
{
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
        $appName = "Asset Management"; 

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
                            <a href="https://assetinventorysystem.online/approve/requests" 
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

    public static function assetAssignment($borrowCode, $userName, $assignedAssets, $approvalDate)
    {
        return '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8" />
            <title>Asset Assignment Notification</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet" />
            <style>
                body {
                    font-family: \'Inter\', \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #1f2937;
                    background-color: #f9fafb;
                    margin: 0;
                    padding: 20px 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    padding: 30px 30px 40px 30px;
                    border: 1px solid #e5e7eb;
                }
                .header {
                    display: flex;
                    align-items: center;
                    padding-bottom: 20px;
                    border-bottom: 1px solid #e5e7eb;
                    margin-bottom: 30px;
                    user-select: none;
                }
                .header img {
                    height: 48px;
                    width: 48px;
                    object-fit: contain;
                    margin-right: 15px;
                }
                .header .company-name {
                    font-weight: 700;
                    font-size: 22px;
                    color: #2563eb;
                }
                h1 {
                    font-size: 24px;
                    color: #2563eb;
                    font-weight: 700;
                    text-align: center;
                    margin-bottom: 24px;
                    user-select: none;
                }
                p {
                    font-weight: 500;
                    color: #374151;
                    user-select: text;
                    line-height: 1.5;
                }
                strong {
                    color: #1f2937;
                }
                .card {
                    background-color: #ffffff;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                    box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
                    border: 1px solid #e5e7eb;
                }
                p:last-of-type {
                    margin-top: 32px;
                    font-weight: 600;
                    color: #374151;
                    user-select: text;
                    line-height: 1.5;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                }
                th, td {
                    padding: 10px;
                    text-align: left;
                    border-bottom: 1px solid #e5e7eb;
                }
                th {
                    background-color: #f8fafc;
                    font-weight: 600;
                }
            </style>
        </head>
        
        <body>
                <div class="container" role="main" aria-label="Asset Assignment Notification">
                <div class="header" aria-label="Company logo and name">
                    <img src="https://i.imgur.com/HF3xnxw.png" alt="Asset Management System Logo" />
                    <h4 class="company_name">Asset Inventory System</h4>
                </div>

                <h1>Asset Assignment Notification</h1>

                <p>Hello ' . e($userName) . ',</p>
                <p>The following assets have been assigned to you:</p>

                <div class="card">
                    <table>
                        <thead>
                            <tr>
                                <th>Asset Name</th>
                                <th>Asset Code</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>';

                    foreach ($assignedAssets as $asset) {
                        $html .= '<tr>
                                    <td>' . e($asset['name']) . '</td>
                                    <td>' . e($asset['code']) . '</td>
                                    <td>' . e($asset['quantity']) . '</td>
                                </tr>';
                    }

                    $html .= '</tbody>
                                </table>
                </div>

                    <p><strong>Borrow Code:</strong> ' . e($borrowCode) . '</p>
                    <p><strong>Date Assigned:</strong> ' . e($approvalDate) . '</p>

                    <p>A PDF accountability form is attached for your records.</p>

                    <p>Best regards,<br />
                    Asset Management System</p>
                </div>
        </body>


        </html>';
    }

    public static function softwareAssignment(
        string $assignmentNo,
        string $userName,
        array $assignedSoftware,
        string $assignmentDate
    ): string {
        $softwareList = '';
        foreach ($assignedSoftware as $software) {
            $softwareList .= "<tr>";
            $softwareList .= "<td>{$software['name']}</td>";
            $softwareList .= "<td>{$software['code']}</td>";
            $softwareList .= "<td>{$software['license_key']}</td>";
            $softwareList .= "<td>{$software['quantity']}</td>";
            $softwareList .= "</tr>";
        }
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { margin-top: 20px; text-align: center; color: #6c757d; font-size: 0.9em; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Software Assignment Notification</h2>
                    <p>Assignment No: $assignmentNo</p>
                </div>
                
                <div class="content">
                    <p>Dear $userName,</p>
                    
                    <p>The following software has been assigned to you:</p>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Software Name</th>
                                <th>Code</th>
                                <th>License Key</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            $softwareList
                        </tbody>
                    </table>
                    
                    <p>Assignment Date: $assignmentDate</p>
                    
                    <p>Please find the attached accountability form for your records.</p>
                    
                    <p>If you have any questions, please contact the IT department.</p>
                </div>
                
                <div class="footer">
                    <p>This is an automated message. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

}