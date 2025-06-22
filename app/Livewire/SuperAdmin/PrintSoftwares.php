<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Software;
use App\Models\SoftwarePrintLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PrintSoftwares extends Component
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
        $printLogs = SoftwarePrintLog::query()
            ->with('user')
            ->when($this->search, fn ($query) => $query->where('print_code', 'like', '%'.$this->search.'%'))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.print-softwares', [
            'printLogs' => $printLogs,
        ]);
    }

    public function generatePrintCode()
    {
        $datePart = now()->format('Ymd');
        $latest = SoftwarePrintLog::where('print_code', 'like', "PRTS-{$datePart}-%")->latest()->first();

        if ($latest) {
            $lastNum = (int) substr($latest->print_code, -6);
            $newNum = str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNum = '000001';
        }

        return "PRTS-{$datePart}-{$newNum}";
    }

    public function printSoftwares()
    {
        $this->validate();

        // Use the correct relationship name: addedBy (not addedByUser)
        $softwares = Software::with('addedBy')
            ->whereBetween('created_at', [$this->dateFrom, $this->dateTo])
            ->get();

        if ($softwares->isEmpty()) {
            $this->errorMessage = 'No software found for the selected date range.';
            return;
        }

        // Prepare snapshot data
        $snapshot = $softwares->map(function ($software) {
            return [
                'software_code' => $software->software_code,
                'software_name' => $software->software_name,
                'description' => $software->description,
                'license_key' => $software->license_key,
                'installation_date' => $software->installation_date,
                'expiry_date' => $software->expiry_date,
                // Access through the correct relationship
                'added_by' => $software->addedBy ? $software->addedBy->name : 'N/A',
            ];
        })->toArray();

        // Create print log - ENCODE THE SNAPSHOT AS JSON
        $printLog = SoftwarePrintLog::create([
            'print_code' => $this->generatePrintCode(),
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'user_id' => Auth::id(),
            'software_snapshot_data' => json_encode($snapshot), // Convert to JSON
        ]);

        // Attach software to pivot table
        $printLog->software()->attach($softwares->pluck('id'));

        // Generate PDF
        $pdf = Pdf::loadView('pdf.software-print', [
            'softwares' => $snapshot,
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
        $printLog = SoftwarePrintLog::findOrFail($printLogId);

        // Decode the JSON snapshot data
        $snapshot = json_decode($printLog->software_snapshot_data, true);
        
        // Ensure we have an array
        if (!is_array($snapshot)) {
            $snapshot = [];
        }

        $pdf = Pdf::loadView('pdf.software-print', [
            'softwares' => $snapshot,
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
        $printLog = SoftwarePrintLog::findOrFail($this->logToDelete);
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