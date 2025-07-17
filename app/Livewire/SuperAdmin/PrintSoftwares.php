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
            $softwares = Software::with('addedBy')->get();
        } else {
            $start = Carbon::parse($this->dateFrom)->startOfDay();
            $end = Carbon::parse($this->dateTo)->endOfDay();

            $softwares = Software::with('addedBy')
                ->whereBetween('date_acquired', [$start, $end])
                ->get();
        }

        if ($softwares->isEmpty()) {
            $this->errorMessage = 'No software found.';
            return;
        }

        $snapshot = $softwares->map(function ($software) {
            return [
                'software_code' => $software->software_code,
                'software_name' => $software->software_name,
                'description' => $software->description,
                'license_key' => $software->license_key,
                'installation_date' => $software->installation_date,
                'date_acquired' => $software->date_acquired,
                'expiry_date' => $software->expiry_date,
                'added_by' => $software->addedBy ? $software->addedBy->name : 'N/A',
            ];
        })->toArray();

        $printLog = SoftwarePrintLog::create([
            'print_code' => $this->generatePrintCode(),
            'date_from' => $this->filterOption == 'by_date' ? $this->dateFrom : null,
            'date_to' => $this->filterOption == 'by_date' ? $this->dateTo : null,
            'user_id' => Auth::id(),
            'software_snapshot_data' => json_encode($snapshot),
        ]);

        $printLog->software()->attach($softwares->pluck('id'));

        $pdf = Pdf::loadView('pdf.software-print', [
            'softwares' => $snapshot,
            'printLog' => $printLog,
            'dateFrom' => $this->filterOption == 'by_date' ? $this->dateFrom : null,
            'dateTo' => $this->filterOption == 'by_date' ? $this->dateTo : null,
        ])->setPaper('a4', 'landscape');

        $this->successMessage = 'PDF generated successfully!';

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
        $printLog = SoftwarePrintLog::findOrFail($printLogId);

        $snapshot = json_decode($printLog->software_snapshot_data, true);
        
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