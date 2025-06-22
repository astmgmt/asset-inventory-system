<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SoftwareAssignmentBatch;
use Barryvdh\DomPDF\Facade\Pdf;

class TrackSoftwares extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBatch = null;

    public function clearSearch()
    {
        $this->search = '';
    }

    public function render()
    {
        $batches = SoftwareAssignmentBatch::query()
            ->with(['user', 'assignedByUser'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('assignment_no', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($q2) {
                          $q2->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('email', 'like', '%' . $this->search . '%')
                             ->orWhere('username', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('assignedByUser', function ($q3) {
                          $q3->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('email', 'like', '%' . $this->search . '%')
                             ->orWhere('username', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('date_assigned', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.track-softwares', [
            'batches' => $batches
        ]);
    }

    public function viewBatch($batchId)
    {
        $this->selectedBatch = SoftwareAssignmentBatch::with([
            'user', 
            'assignedByUser',
            'assignmentItems.software'
        ])->findOrFail($batchId);
    }

    public function closeModal()
    {
        $this->selectedBatch = null;
    }

    public function printBatch($batchId)
    {
        $batch = SoftwareAssignmentBatch::with([
            'assignmentItems.software', 
            'user', 
            'assignedByUser',
            'approvedByUser'
        ])->findOrFail($batchId);
        
        $approver = $batch->approvedByUser;
        $approvalDate = $batch->approved_at?->format('M d, Y H:i') ?? 'N/A';
        
        $pdf = Pdf::loadView('pdf.software-assignment', compact('batch', 'approver', 'approvalDate'));
        
        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            "Software-Assignment-{$batch->assignment_no}.pdf"
        );
    }
}