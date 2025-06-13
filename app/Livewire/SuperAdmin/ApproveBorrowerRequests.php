<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\BorrowAssetQuantity;
use App\Services\SendEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
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
            ->where('status', 'Pending')
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
                      ->orWhereDate('borrowed_at', 'like', '%'.$this->search.'%');
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
            $user = Auth::user();
            
            // Update transaction status
            $transaction->update([
                'status' => 'Approved',
                'approved_by_user_id' => $user->id,
                'approved_by_department_id' => $user->department_id,
                'borrowed_at' => now(),
                'approved_at' => now(),
                'remarks' => $this->approveRemarks
            ]);
            
            // Deduct quantities
            foreach ($transaction->borrowItems as $item) {
                $assetQuantity = BorrowAssetQuantity::where('asset_id', $item->asset_id)->first();
                if ($assetQuantity) {
                    $assetQuantity->decrement('available_quantity', $item->quantity);
                }
            }
            
            // Send email notification to borrower
            $this->sendApprovalEmail($transaction);
            
            // Show success message
            //$this->successMessage = "Borrow request approved successfully!";
            
            // Reset state
            $this->reset([
                'showApproveModal', 
                'selectedTransaction', 
                'approveRemarks'
            ]);

            // Show success message
            $this->successMessage = "Borrow request approved successfully!";
            
            // Open PDF in new tab
            //$this->dispatch('openPdf', borrowCode: $transaction->borrow_code);
            //$this->dispatch('openPdf', ['borrowCode' => $transaction->borrow_code]);
            $this->dispatch('openPdf', $transaction->borrow_code);

            
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to approve request: " . $e->getMessage();
            Log::error("Approve error: " . $e->getMessage());
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
            
            // Update transaction status
            $transaction->update([
                'status' => 'Denied',
                'remarks' => $this->denyRemarks
            ]);
            
            // Send email notification to borrower
            $this->sendDenialEmail($transaction);
            
            // Show success message
            $this->successMessage = "Borrow request denied successfully!";
            
            // Reset state
            $this->reset([
                'showDenyModal', 
                'selectedTransaction', 
                'denyRemarks'
            ]);
            
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to deny request: " . $e->getMessage();
            Log::error("Deny error: " . $e->getMessage());
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
                $borrower->email, // To
                "Your Borrow Request Has Been Approved: {$transaction->borrow_code}", // Subject
                ['emails.borrow-approval', [ // View + data wrapped in array
                    'borrowCode' => $transaction->borrow_code,
                    'approvalDate' => now()->format('M d, Y H:i'),
                    'remarks' => $this->approveRemarks
                ]],
                [], // CC
                $pdf->output(),
                "Approval-{$transaction->borrow_code}.pdf",
                false // Blade view mode
            );

        } catch (\Exception $e) {
            Log::error("Approval email error: " . $e->getMessage());
        }
    }

    
    private function sendDenialEmail($transaction)
    {
        try {
            $emailService = new SendEmail();
            $borrower = $transaction->user;

            $emailService->send(
                $borrower->email,
                "Your Borrow Request Has Been Denied: {$transaction->borrow_code}",
                ['emails.borrow-denial', [
                    'borrowCode' => $transaction->borrow_code,
                    'denialDate' => now()->format('M d, Y H:i'),
                    'remarks' => $this->denyRemarks
                ]],
                [],
                null,
                null,
                false // Blade view mode
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