<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\AssetQrcodeLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class PrintQRCodes extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $search = '';
    public $successMessage;
    public $errorMessage;
    public $showDeleteModal = false;
    public $logToDelete;
    public $filterOption = 'by_date';

    protected $rules = [
        'dateFrom' => 'required|date',
        'dateTo' => 'required|date|after_or_equal:dateFrom',
    ];

    protected $messages = [
        'dateFrom.required' => 'The start date is required.',
        'dateTo.required' => 'The end date is required.',
        'dateTo.after_or_equal' => 'The end date must be after or equal to the start date.',
    ];

    public function render()
    {
        $printLogs = AssetQrcodeLog::query()
            ->with('user')
            ->when($this->search, fn ($query) => $query->where('print_code', 'like', '%'.$this->search.'%'))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.print-qrcodes', [
            'printLogs' => $printLogs,
        ]);
    }

    public function generatePrintCode()
    {
        $datePart = now()->format('Ymd');
        $latest = AssetQrcodeLog::where('print_code', 'like', "PRTQ-{$datePart}-%")->latest()->first();

        if ($latest) {
            $lastNum = (int) substr($latest->print_code, -6);
            $newNum = str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNum = '000001';
        }

        return "PRTQ-{$datePart}-{$newNum}";
    }

    public function generateQrCode(
        string $assetCode,
        string $name,
        string $modelNumber,
        ?string $serialNumber,
        string $locationName,
        ?string $description
    ): string {
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

    
    public function printQRCodes()
    {
        if ($this->filterOption == 'by_date') {
            $this->validate([
                'dateFrom' => 'required|date',
                'dateTo' => 'required|date|after_or_equal:dateFrom',
            ], [
                'dateFrom.required' => 'The start date is required.',
                'dateTo.required' => 'The end date is required.',
                'dateTo.after_or_equal' => 'The end date must be after or equal to the start date.',
            ]);
        }

        if ($this->filterOption == 'select_all') {
            $assets = Asset::with('location')->get();
        } else {
            $start = \Carbon\Carbon::parse($this->dateFrom)->startOfDay();
            $end = \Carbon\Carbon::parse($this->dateTo)->endOfDay();

            $assets = Asset::with('location')
                ->whereBetween('date_acquired', [$start, $end])
                ->get();
        }

        if ($assets->isEmpty()) {
            $this->errorMessage = 'No assets found.';
            return;
        }

        $this->successMessage = 'Successfully created print job. Generating PDF for download, please wait...';

        $snapshot = $assets->map(function ($asset) {
            return [
                'asset_code' => $asset->asset_code,
                'name' => $asset->name,
                'model_number' => $asset->model_number,
                'serial_number' => $asset->serial_number,
                'location_name' => $asset->location->location_name,
                'description' => $asset->description,
            ];
        })->toArray();

        $printLog = AssetQrcodeLog::create([
            'print_code' => $this->generatePrintCode(),
            'date_from' => $this->filterOption == 'by_date' ? $this->dateFrom : null,
            'date_to' => $this->filterOption == 'by_date' ? $this->dateTo : null,
            'user_id' => Auth::id(),
            'asset_snapshot_data' => $snapshot,
        ]);

        $printLog->assets()->attach($assets->pluck('id'));

        $assetsWithQr = [];
        foreach ($assets as $asset) {
            $qrCode = $this->generateQrCode(
                $asset->asset_code,
                $asset->name,
                $asset->model_number,
                $asset->serial_number,
                $asset->location->location_name,
                $asset->description
            );
            
            $assetsWithQr[] = [
                'asset' => $asset,
                'qrCode' => 'data:image/png;base64,' . base64_encode($qrCode),
            ];
        }

        $pdf = Pdf::loadView('pdf.asset-qrcodes', [
            'assets' => $assetsWithQr,
            'printLog' => $printLog,
            'dateFrom' => $this->filterOption == 'by_date' ? $this->dateFrom : null,
            'dateTo' => $this->filterOption == 'by_date' ? $this->dateTo : null,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "{$printLog->print_code}.pdf"
        );
    }

    public function updatedFilterOption($value)
    {
        if ($value === 'select_all') {
            $this->resetValidation(['dateFrom', 'dateTo']);
        }
    }


    public function printAgain($printLogId)
    {
        $this->successMessage = 'Generating PDF for download, please wait...';

        $printLog = AssetQrcodeLog::findOrFail($printLogId);
        $snapshot = $printLog->asset_snapshot_data;

        $assetsWithQr = [];
        foreach ($snapshot as $assetData) {
            $qrCode = $this->generateQrCode(
                $assetData['asset_code'],
                $assetData['name'],
                $assetData['model_number'],
                $assetData['serial_number'],
                $assetData['location_name'],
                $assetData['description']
            );
            
            $assetsWithQr[] = [
                'asset' => (object) $assetData,
                'qrCode' => 'data:image/png;base64,' . base64_encode($qrCode),
            ];
        }

        $pdf = Pdf::loadView('pdf.asset-qrcodes', [
            'assets' => $assetsWithQr,
            'printLog' => $printLog,
            'dateFrom' => $printLog->date_from,
            'dateTo' => $printLog->date_to,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "{$printLog->print_code}-REPRINT.pdf"
        );
    }


    public function confirmDelete($logId)
    {
        $this->logToDelete = $logId;
        $this->showDeleteModal = true;
    }

    public function deletePrintLog()
    {
        $printLog = AssetQrcodeLog::findOrFail($this->logToDelete);
        $printLog->delete();
        
        $this->successMessage = 'Print log deleted successfully!';
        $this->showDeleteModal = false;
        $this->logToDelete = null;
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}
