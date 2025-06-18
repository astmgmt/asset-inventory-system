<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\Asset;
use App\Models\UserHistory;
use App\Services\SendEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveReturnRequests extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransaction = null;
    public $showApproveModal = false;
    public $showRejectModal = false;
    public $approveRemarks = '';
    public $rejectRemarks = '';
    public $successMessage = '';
    public $errorMessage = '';

    public function render()
    {
        $transactions = AssetBorrowTransaction::where('status', 'PendingReturnApproval')
            ->when($this->search, function ($query) {
                $query->where('borrow_code', 'like', '%'.$this->search.'%');
            })
            ->with(['user', 'borrowItems.asset'])
            ->orderBy('return_requested_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.approve-return-requests', [
            'transactions' => $transactions
        ]);
    }

    public function openApproveModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with('borrowItems.asset')
            ->findOrFail($transactionId);
        $this->showApproveModal = true;
    }

    public function openRejectModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with('borrowItems.asset')
            ->findOrFail($transactionId);
        $this->showRejectModal = true;
    }

    public function approveReturn()
    {
        $this->validate([
            'approveRemarks' => 'nullable|string|max:500',
        ]);

        try {
            $returnCode = null;
            $transaction = null;
            
            DB::transaction(function () use (&$returnCode, &$transaction) {
                $transaction = $this->selectedTransaction;
                $returnCode = $this->generateReturnCode();
                
                // Update assets quantities
                foreach ($transaction->borrowItems as $item) {
                    $asset = Asset::find($item->asset_id);
                    $asset->increment('quantity', $item->quantity);
                }
                
                // Update transaction status
                $transaction->update([
                    'status' => 'Returned',
                    'returned_at' => now(),
                    'approved_by_user_id' => Auth::id(),
                    'approved_at' => now(),
                    'remarks' => $this->approveRemarks
                ]);
                
                // Find existing history record and update it
                $history = UserHistory::where('borrow_code', $transaction->borrow_code)
                    ->whereNull('return_code')
                    ->first();
                
                if ($history) {
                    $history->update([
                        'return_code' => $returnCode,
                        'status' => 'Return Approved',
                        'return_data' => [
                            'return_items' => $transaction->borrowItems->map(function ($item) {
                                return [
                                    'borrow_item' => [
                                        'asset' => $item->asset->toArray(),
                                        'quantity' => $item->quantity,
                                        'purpose' => $item->purpose,
                                        'created_at' => $item->created_at
                                    ],
                                    'status' => 'Returned',
                                    'created_at' => now()
                                ];
                            })->toArray()
                        ],
                        'action_date' => now()
                    ]);
                } else {
                    // Create new history if no existing record found
                    UserHistory::create([
                        'user_id' => $transaction->user_id,
                        'borrow_code' => $transaction->borrow_code,
                        'return_code' => $returnCode,
                        'status' => 'Return Approved',
                        'return_data' => [
                            'return_items' => $transaction->borrowItems->map(function ($item) {
                                return [
                                    'borrow_item' => [
                                        'asset' => $item->asset->toArray(),
                                        'quantity' => $item->quantity,
                                        'purpose' => $item->purpose,
                                        'created_at' => $item->created_at
                                    ],
                                    'status' => 'Returned',
                                    'created_at' => now()
                                ];
                            })->toArray()
                        ],
                        'action_date' => now()
                    ]);
                }
            });
            
            // Generate PDF
            $pdfContent = $this->generateReturnApprovalPDF($transaction, $returnCode);
            
            // Send email notification to user
            $this->sendReturnApprovalEmail($transaction, $returnCode, $pdfContent);
            
            $this->successMessage = "Return approved successfully!";
            $this->reset(['showApproveModal', 'selectedTransaction', 'approveRemarks']);
        } catch (\Exception $e) {
            $this->errorMessage = "Error approving return: " . $e->getMessage();
            Log::error("Approve return error: " . $e->getMessage());
        }
    }

    
    private function generateReturnCode()
    {
        $date = now()->format('Ymd');
        $lastReturn = UserHistory::where('return_code', 'like', "RT-{$date}-%")
            ->orderBy('return_code', 'desc')
            ->first();
            
        $number = $lastReturn ? (int)substr($lastReturn->return_code, -8) + 1 : 1;
        
        return sprintf("RT-%s-%08d", $date, $number);
    }
    
    private function generateReturnApprovalPDF($transaction, $returnCode)
    {
        $pdf = Pdf::loadView('pdf.return-approval', [
            'transaction' => $transaction,
            'returnCode' => $returnCode,
            'approvalDate' => now()->format('M d, Y H:i'),
            'approver' => Auth::user(),
        ]);
        
        return $pdf->output();
    }
    
    private function sendReturnApprovalEmail($transaction, $returnCode, $pdfContent)
    {
        try {
            $emailService = new SendEmail();
            $user = $transaction->user;
            $approverName = Auth::user()->name;
            
            // Prepare email content in required format
            $emailContent = [
                'emails.return-approved-user',
                [
                    'returnCode' => $returnCode,
                    'approverName' => $approverName,
                    'approvalDate' => now()->format('M d, Y H:i')
                ]
            ];
            
            // Send email using your service's required format
            $emailService->send(
                $user->email,
                "Return Approved: {$returnCode}",
                $emailContent,         // Content as array [view, data]
                [],                     // CC addresses
                $pdfContent,            // PDF content
                "Return-Approval-{$returnCode}.pdf",
                false                   // isHtml = false (using view template)
            );
        } catch (\Exception $e) {
            Log::error("Return approval email failed: " . $e->getMessage());
        }
    }

    public function rejectReturn()
    {
        $this->validate([
            'rejectRemarks' => 'required|string|max:500',
        ]);

        try {
            $this->selectedTransaction->update([
                'status' => 'ReturnRejected',
                'remarks' => $this->rejectRemarks
            ]);
            
            $this->successMessage = "Return rejected successfully!";
            $this->reset(['showRejectModal', 'selectedTransaction', 'rejectRemarks']);
        } catch (\Exception $e) {
            $this->errorMessage = "Error rejecting return: " . $e->getMessage();
        }
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}