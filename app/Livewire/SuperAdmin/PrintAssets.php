<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\AssetPrintLog;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PrintAssets extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $search = '';
    public $successMessage;
    public $errorMessage;
    public $showDeleteModal = false;
    public $logToDelete;

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
        $printLogs = AssetPrintLog::query()
            ->when($this->search, fn ($query) => $query->where('print_code', 'like', '%'.$this->search.'%'))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.print-assets', [
            'printLogs' => $printLogs,
        ]);
    }

    public function generatePrintCode()
    {
        $datePart = now()->format('Ymd');
        $latest = AssetPrintLog::where('print_code', 'like', "PRT-{$datePart}-%")->latest()->first();

        if ($latest) {
            $lastNum = (int) substr($latest->print_code, -6);
            $newNum = str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNum = '000001';
        }

        return "PRT-{$datePart}-{$newNum}";
    }

    public function printAssets()
    {
        $this->validate();

        $assets = Asset::with(['category', 'condition', 'location', 'vendor'])
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->get();

        if ($assets->isEmpty()) {
            $this->errorMessage = 'No assets found for the selected date range.';
            return;
        }

        // Prepare snapshot data
        $snapshot = $assets->map(function ($asset) {
            return [
                'asset_code' => $asset->asset_code,
                'name' => $asset->name,
                'serial_number' => $asset->serial_number,
                'model_number' => $asset->model_number,
                'quantity' => $asset->quantity,
                'category' => $asset->category->category_name, 
                'condition' => $asset->condition->condition_name, 
                'location' => $asset->location->location_name,
                'vendor' => $asset->vendor->vendor_name,
                'warranty_expiration' => $asset->warranty_expiration,
            ];
        })->toArray();

        // Create print log
        $printLog = AssetPrintLog::create([
            'print_code' => $this->generatePrintCode(),
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'user_id' => Auth::id(),
            'asset_snapshot_data' => $snapshot,
        ]);

        // Attach assets to pivot table
        $printLog->assets()->attach($assets->pluck('id'));

        // Generate PDF
        $pdf = Pdf::loadView('pdf.asset-print', [
            'assets' => $assets,
            'printLog' => $printLog,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "{$printLog->print_code}.pdf"
        );
    }

    public function printAgain($printLogId)
    {
        $printLog = AssetPrintLog::findOrFail($printLogId);

        $pdf = Pdf::loadView('pdf.asset-print', [
            'assets' => collect($printLog->asset_snapshot_data),
            'printLog' => $printLog,
            'dateFrom' => $printLog->date_from,
            'dateTo' => $printLog->date_to,
        ])->setPaper('a4', 'landscape'); 

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
        $printLog = AssetPrintLog::findOrFail($this->logToDelete);
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