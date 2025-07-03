<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\AssetBorrowTransaction;
use App\Models\Asset;
use App\Models\AssetBorrowItem;
use App\Models\UserHistory;
use App\Models\AssetCondition;
use App\Services\SendEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.app')]
class ApproveBorrowerRequests extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransaction = null;
    public $showDetailsModal = false;
    public $showApproveModal = false;
    public $showDenyModal = false;
    public $approveRemarks = '';
    public $denyRemarks = '';
    public $successMessage = '';
    public $errorMessage = '';

    public function render()
    {
        $transactions = AssetBorrowTransaction::with(['user', 'userDepartment', 'requestedBy'])
            ->where('status', 'PendingBorrowApproval')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('borrow_code', 'like', '%'.$this->search.'%')
                      ->orWhereHas('user', function ($userQuery) {
                          $userQuery->where('name', 'like', '%'.$this->search.'%');
                      })
                      ->orWhereHas('userDepartment', function ($deptQuery) {
                          $deptQuery->where('name', 'like', '%'.$this->search.'%');
                      })
                      ->orWhereHas('requestedBy', function ($requestedQuery) {
                          $requestedQuery->where('name', 'like', '%'.$this->search.'%');
                      })
                      ->orWhereDate('created_at', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.approve-borrower-requests', [
            'transactions' => $transactions
        ]);
    }

    public function showDetails($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with([
                'borrowItems.asset', 
                'user', 
                'userDepartment',
                'requestedBy'
            ])
            ->findOrFail($transactionId);
        $this->showDetailsModal = true;
    }

    public function confirmApprove($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::findOrFail($transactionId);
        $this->approveRemarks = '';
        $this->showApproveModal = true;
    }

    public function confirmDeny($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::findOrFail($transactionId);
        $this->denyRemarks = '';
        $this->showDenyModal = true;
    }

    

    public function approveRequest()
    {
        $this->validate([
            'approveRemarks' => 'nullable|string|max:500',
        ]);

        try {
            $transaction = $this->selectedTransaction;
            $borrowCode = $transaction->borrow_code;

            DB::transaction(function () use ($transaction) {
                $user = Auth::user();
                $assetIds = $transaction->borrowItems->pluck('asset_id')->toArray();
                $assets = Asset::whereIn('id', $assetIds)->lockForUpdate()->get()->keyBy('id');
                
                $borrowedCondition = AssetCondition::where('condition_name', 'Borrowed')->first();
                if (!$borrowedCondition) {
                    throw new \Exception("Borrowed condition not found!");
                }

                $disallowedConditions = ['Defective', 'Disposed'];
                foreach ($transaction->borrowItems as $item) {
                    $asset = $assets[$item->asset_id] ?? Asset::find($item->asset_id);
                    
                    if (!$asset) {
                        throw new \Exception("Asset not found for ID: {$item->asset_id}");
                    }
                    
                    if ($asset->is_disposed) {
                        throw new \Exception("Asset {$asset->name} is disposed and cannot be borrowed.");
                    }
                    
                    if (in_array($asset->condition->condition_name, $disallowedConditions)) {
                        throw new \Exception(
                            "Asset {$asset->name} is in {$asset->condition->condition_name} condition and cannot be borrowed."
                        );
                    }
                    
                    if ($asset->quantity < $item->quantity) {
                        throw new \Exception(
                            "Insufficient quantity for asset: {$asset->name}. " .
                            "Available: {$asset->quantity}, Requested: {$item->quantity}"
                        );
                    }
                }

                $updatedAssets = [];

                foreach ($transaction->borrowItems as $item) {
                    $asset = $assets[$item->asset_id];
                    
                    $asset->quantity -= $item->quantity;
                    $asset->reserved_quantity -= $item->quantity;
                    
                    if (!in_array($asset->id, $updatedAssets)) {
                        $asset->condition_id = $borrowedCondition->id;
                        $updatedAssets[] = $asset->id;
                    }
                    
                    $asset->save();
                }

                $transaction->update([
                    'status' => 'Borrowed',
                    'approved_by_user_id' => $user->id,
                    'approved_by_department_id' => $user->department_id,
                    'borrowed_at' => now(),
                    'approved_at' => now(),
                    'remarks' => $this->approveRemarks
                ]);

                UserHistory::create([
                    'user_id' => $transaction->user_id,
                    'borrow_code' => $transaction->borrow_code,
                    'status' => 'Borrow Approved',
                    'borrow_data' => $transaction->load('borrowItems.asset')->toArray(),
                    'action_date' => now()
                ]);
            });
            
            $this->sendApprovalEmail($transaction);
            
            $this->reset([
                'showApproveModal', 
                'selectedTransaction', 
                'approveRemarks'
            ]);

            $this->successMessage = "Borrow request approved successfully!";
            
            $this->dispatch('openPdf', $borrowCode);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = "Failed to approve request: " . $e->getMessage();
            Log::error("Approve error: " . $e->getMessage());
            
            try {
                if ($this->selectedTransaction) {
                    foreach ($this->selectedTransaction->borrowItems as $item) {
                        $asset = Asset::find($item->asset_id);
                        if ($asset) {
                            $asset->decrement('reserved_quantity', $item->quantity);
                        }
                    }

                    $this->selectedTransaction->update([
                        'status' => 'Rejected',
                        'remarks' => 'Auto-rejected: ' . $e->getMessage()
                    ]);
                    
                    UserHistory::create([
                        'user_id' => $this->selectedTransaction->user_id,
                        'borrow_code' => $this->selectedTransaction->borrow_code,
                        'status' => 'Borrow Auto-Rejected',
                        'borrow_data' => $this->selectedTransaction->load('borrowItems.asset')->toArray(),
                        'action_date' => now()
                    ]);
                    
                    $this->successMessage = "Request auto-rejected due to insufficient quantity";
                    $this->reset(['showApproveModal', 'selectedTransaction']);
                }
            } catch (\Exception $updateEx) {
                Log::error("Auto-reject failed: " . $updateEx->getMessage());
            }
        }
    }


    public function denyRequest()
    {
        $this->validate([
            'denyRemarks' => 'required|string|max:500',
        ], [
            'denyRemarks.required' => 'Please provide a reason for denial.'
        ]);

        try {
            $transaction = $this->selectedTransaction;
            
            foreach ($transaction->borrowItems as $item) {
                $asset = Asset::find($item->asset_id);
                if ($asset) {
                    $asset->decrement('reserved_quantity', $item->quantity);
                }
            }

            $transaction->update([
                'status' => 'BorrowRejected',
                'remarks' => $this->denyRemarks
            ]);

            $denialRemarks = $this->denyRemarks;
            
            UserHistory::create([
                'user_id' => $transaction->user_id,
                'borrow_code' => $transaction->borrow_code,
                'status' => 'Borrow Denied',
                'borrow_data' => $transaction->load('borrowItems.asset')->toArray(),
                'action_date' => now()
            ]);
            
            $this->sendDenialEmail($transaction, $denialRemarks);
            
            $this->successMessage = "Borrow request denied!";
            
            $this->reset([
                'showDenyModal', 
                'selectedTransaction', 
                'denyRemarks'
            ]);       
            
        } catch (\Exception $e) {
                DB::rollBack();
                $this->errorMessage = "Failed to approve request: " . $e->getMessage();
                Log::error("Approve error: " . $e->getMessage());
        }
    }
    
    private function generateApprovalPDF($transaction)
    {
        $pdf = Pdf::loadView('pdf.borrow-approval', [
            'transaction' => $transaction,
            'approver' => Auth::user(),
            'approvalDate' => now()->format('M d, Y H:i')
        ]);
        
        return $pdf;
    }
    
    private function sendApprovalEmail($transaction)
    {
        try {
            $emailService = new SendEmail();
            $borrower = $transaction->user;

            $pdf = $this->generateApprovalPDF($transaction);

            $emailService->send(
                $borrower->email,
                "Your Borrow Request Has Been Approved: {$transaction->borrow_code}",
                ['emails.borrow-approval', [
                    'borrowCode' => $transaction->borrow_code,
                    'approvalDate' => now()->format('M d, Y H:i'),
                    'remarks' => $this->approveRemarks
                ]],
                [],
                $pdf->output(),
                "Approval-{$transaction->borrow_code}.pdf",
                false
            );

        } catch (\Exception $e) {
            Log::error("Approval email error: " . $e->getMessage());
        }
    }

    private function sendDenialEmail($transaction, $remarks)
    {
        try {
            $emailService = new SendEmail();
            $borrower = $transaction->user;

            $assetDetails = $transaction->borrowItems->map(function ($item) {
                return [
                    'asset_code' => $item->asset->asset_code ?? 'N/A',
                    'name' => $item->asset->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'purpose' => $item->purpose ?: 'N/A'
                ];
            });

            $emailService->send(
                $borrower->email,
                "Your Borrow Request Has Been Denied: {$transaction->borrow_code}",
                ['emails.borrow-denial', [
                    'borrowCode' => $transaction->borrow_code,
                    'denialDate' => now()->format('M d, Y H:i'),
                    'remarks' => $remarks,
                    'assetDetails' => $assetDetails
                ]],
                [],
                null,
                null,
                false
            );

        } catch (\Exception $e) {
            Log::error("Denial email error: " . $e->getMessage());
        }
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}