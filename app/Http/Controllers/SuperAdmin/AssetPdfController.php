<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Asset;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class AssetPdfController extends Controller
{
    public function generate($id)
    {
        $asset = Asset::with(['category', 'condition', 'location', 'vendor'])->findOrFail($id);
    
        // Generate QR code with multiple fields
        $qrCode = $this->generateQrCode(
            $asset->asset_code,
            $asset->name,
            $asset->model_number,
            $asset->serial_number,
            $asset->location->location_name,
            $asset->description
        );
        
        $data = [
            'asset' => $asset,
            'qrCode' => base64_encode($qrCode),
        ];

        $pdf = Pdf::loadView('pdf.asset', $data);
        return $pdf->stream('asset_'.$asset->asset_code.'.pdf');
    }
    
    public function generateQrCode(
        string $assetCode,
        string $name,
        string $modelNumber,
        ?string $serialNumber,
        string $locationName,
        ?string $description
    ): string {
        // Create a structured data format
        $qrData = implode("\n", [
            "Asset Code: $assetCode",
            "Name: $name",
            "Model: $modelNumber",
            "Serial: " . ($serialNumber ?? 'N/A'),
            "Location: $locationName",
            "Description: " . ($description ? substr($description, 0, 100) . (strlen($description) > 100 ? '...' : '') : 'N/A')
        ]);

        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $qrData,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 600,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        $result = $builder->build();
        return $result->getString();
    }
}