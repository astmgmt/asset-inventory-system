<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserHistory;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackAssets extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHistory = null;
    public $showDetailsModal = false;

    public function render()
    {
        $properReturns = UserHistory::select('borrow_code')
            ->whereNotNull('return_code')
            ->where('return_code', 'NOT LIKE', '%HIDDEN%')
            ->groupBy('borrow_code');

        $histories = UserHistory::with(['user', 'user.role', 'user.department'])
            ->select('user_histories.*')
            ->leftJoinSub($properReturns, 'proper_returns', function ($join) {
                $join->on('user_histories.borrow_code', '=', 'proper_returns.borrow_code');
            })
            ->join('users', 'user_histories.user_id', '=', 'users.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->where(function ($query) {
                $query->whereNull('proper_returns.borrow_code')
                    ->orWhere(function ($q) {
                        $q->where('user_histories.return_code', 'NOT LIKE', '%HIDDEN%')
                            ->orWhereNull('user_histories.return_code');
                    });
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('user_histories.borrow_code', 'like', '%'.$this->search.'%')
                      ->orWhere('user_histories.return_code', 'like', '%'.$this->search.'%')
                      ->orWhere('users.name', 'like', '%'.$this->search.'%')
                      ->orWhere('departments.name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('user_histories.action_date', 'desc')
            ->paginate(50);

        return view('livewire.super-admin.track-assets', [
            'histories' => $histories
        ]);
    }

    public function showDetails($historyId)
    {
        $this->selectedHistory = UserHistory::with(['user', 'user.role', 'user.department'])->findOrFail($historyId);
        $this->showDetailsModal = true;
    }

    public function generatePdf($historyId)
    {
        $history = UserHistory::with(['user', 'user.role', 'user.department'])->findOrFail($historyId);
        
        // Get JSON data as arrays (already decoded by model cast)
        $borrowData = $history->borrow_data ?? [];
        $returnData = $history->return_data ?? [];
        
        // Get approver names
        $borrowApprovedBy = 'N/A';
        if (isset($borrowData['approved_by_user_id'])) {
            $approver = User::find($borrowData['approved_by_user_id']);
            $borrowApprovedBy = $approver ? $approver->name : 'N/A';
        } elseif (isset($borrowData['approved_by'])) {
            $borrowApprovedBy = $borrowData['approved_by'];
        }

        $transaction = [
            'borrow_code' => $history->borrow_code,
            'return_code' => $history->return_code,
            'user' => [
                'name' => $history->user->name,
                'department' => $history->user->department->name ?? 'N/A',
            ],
            'role' => $history->user->role->name ?? 'N/A',
            'borrow_approved_by' => $borrowApprovedBy,
            'return_received_by' => $returnData['return_received_by'] ?? 'N/A',
            'borrow_date' => $history->action_date->format('M d, Y H:i'),
            'return_date' => isset($returnData['return_date']) ? \Carbon\Carbon::parse($returnData['return_date'])->format('M d, Y H:i') : 'N/A',
            'remarks' => $history->remarks ?? 'N/A',
            'borrowItems' => $borrowData['borrow_items'] ?? [],
            'returnItems' => $returnData['return_items'] ?? [],
        ];

        $pdf = Pdf::loadView('pdf.track-asset-history', [
            'transaction' => (object)$transaction,
            'borrowDate' => $transaction['borrow_date'],
            'returnDate' => $transaction['return_date']
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "asset-history-{$history->borrow_code}.pdf"
        );
    }
    
    public function isHiddenReturnCode($code)
    {
        return $code && Str::contains($code, 'HIDDEN');
    }
}