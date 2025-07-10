<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\Asset;
use App\Models\User;
use App\Models\UserHistory;
use App\Models\AssetReturnItem;
use App\Models\AssetCondition;
use App\Models\AssetBorrowItem;
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
    public $showRejectConfirmModal = false;
    public $approveRemarks = '';
    public $rejectRemarks = '';
    public $successMessage = '';
    public $errorMessage = '';
    public $approveBorrowItems = [];
    public $rejectBorrowItems = [];

    public function render()
    {
        $transactions = AssetBorrowTransaction::whereHas('borrowItems.returnItems', function ($query) {
            $query->where('approval_status', 'Pending');
        })
            ->when($this->search, function ($query) {
                $query->where('borrow_code', 'like', '%'.$this->search.'%');
            })
            ->with(['user', 'borrowItems.asset', 'borrowItems.returnItems'])
            ->orderBy('return_requested_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.approve-return-requests', [
            'transactions' => $transactions
        ]);
    }

    public function openApproveModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with(['borrowItems.asset', 'borrowItems.returnItems'])
            ->findOrFail($transactionId);

        $this->approveBorrowItems = $this->selectedTransaction->borrowItems->filter(function ($borrowItem) {
            return $borrowItem->returnItems->contains('approval_status', 'Pending');
        })->values();

        $this->showApproveModal = true;
    }

    public function openRejectModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with(['borrowItems.asset', 'borrowItems.returnItems'])
            ->findOrFail($transactionId);

        $this->rejectBorrowItems = $this->selectedTransaction->borrowItems->filter(function ($item) {
            return $item->returnItems->contains('approval_status', 'Pending');
        })->values();

        $this->showRejectModal = true;
    }

    public function openRejectConfirmModal()
    {
        $this->validate([
            'rejectRemarks' => 'required|string|max:500',
        ]);
        
        $this->showRejectConfirmModal = true;
    }

    public function approveReturn()
    {
        $this->validate([
            'approveRemarks' => 'nullable|string|max:500',
        ]);

        $pendingReturnItems = null;
        $returnReceivedBy = Auth::user()->name; 
        $returnCode = $this->generateReturnCode();
        $transaction = $this->selectedTransaction;

        try {
            DB::transaction(function () use (&$pendingReturnItems, $returnReceivedBy, $returnCode, $transaction) {
                $borrowApprovedBy = $transaction->approvedByUser
                    ? $transaction->approvedByUser->name
                    : 'N/A';

                $availableCondition = AssetCondition::where('condition_name', 'Available')->first();
                if (!$availableCondition) {
                    throw new \Exception("Available condition not found!");
                }

                $pendingReturnItems = AssetReturnItem::with('borrowItem.asset')
                    ->whereHas('borrowItem', function ($query) use ($transaction) {
                        $query->where('borrow_transaction_id', $transaction->id);
                    })
                    ->where('approval_status', 'Pending')
                    ->get();

                if ($pendingReturnItems->isEmpty()) {
                    throw new \Exception('No pending return requests found for approval.');
                }

                foreach ($pendingReturnItems as $returnItem) {
                    $returnItem->update([
                        'return_code' => $returnCode,
                        'approval_status' => 'Approved',
                        'approved_at' => now(),
                        'approved_by_user_id' => Auth::id(),
                    ]);
                }

                foreach ($pendingReturnItems as $returnItem) {
                    $borrowItem = $returnItem->borrowItem;
                    $asset = $borrowItem->asset;

                    $borrowItem->update(['status' => 'Returned']);
                    
                    $asset->decrement('reserved_quantity', $borrowItem->quantity);
                    
                    if ($asset->reserved_quantity == 0) {
                        $asset->update(['condition_id' => $availableCondition->id]);
                    }
                }

                $allItemsReturned = $transaction->borrowItems->every(function ($item) {
                    return $item->status === 'Returned';
                });

                $transaction->update([
                    'status' => $allItemsReturned ? 'Returned' : 'PartiallyReturned',
                    'returned_at' => $allItemsReturned ? now() : null,
                    'remarks' => $this->approveRemarks,
                ]);

                $returnItemsData = $pendingReturnItems->map(function ($returnItem) {
                    $borrowItem = $returnItem->borrowItem;
                    return [
                        'borrow_item' => [
                            'asset' => $borrowItem->asset->toArray(),
                            'quantity' => $borrowItem->quantity,
                            'purpose' => $borrowItem->purpose,
                            'created_at' => $borrowItem->created_at,
                        ],
                        'status' => 'Returned',
                        'created_at' => now()
                    ];
                })->toArray();

                $returnData = [
                    'return_items' => $returnItemsData,
                    'return_received_by' => $returnReceivedBy,
                    'approved_by' => $borrowApprovedBy,
                    'return_date' => now()->format('Y-m-d H:i:s'),
                    'remarks_from_admin' => $this->approveRemarks
                ];

                UserHistory::create([
                    'user_id' => $transaction->user_id,
                    'borrow_code' => $transaction->borrow_code,
                    'return_code' => $returnCode,
                    'status' => 'Return Approved',
                    'return_data' => $returnData,
                    'action_date' => now()
                ]);

                UserHistory::where('borrow_code', $transaction->borrow_code)
                    ->where('status', 'Borrow Approved')
                    ->whereNull('return_code')
                    ->update(['return_code' => 'HIDDEN-'.$returnCode]);

            });

            $this->sendReturnApprovalEmail($transaction, $returnCode);

            $this->successMessage = "Return approved successfully!";
            $this->reset(['showApproveModal', 'selectedTransaction', 'approveRemarks', 'approveBorrowItems']);
        } catch (\Exception $e) {
            $this->errorMessage = "Error approving return: " . $e->getMessage();
            Log::error("Approve return error: " . $e->getMessage());
        }
    }

    
    public function rejectReturn()
    {
        try {
            DB::transaction(function () {
                $transaction = $this->selectedTransaction;
                $rejectRemarks = $this->rejectRemarks;

                $returnItemIds = [];
                foreach ($this->rejectBorrowItems as $borrowItem) {
                    foreach ($borrowItem->returnItems as $returnItem) {
                        if ($returnItem->approval_status === 'Pending') {
                            $returnItemIds[] = $returnItem->id;
                        }
                    }
                }

                if (!empty($returnItemIds)) {
                    AssetReturnItem::whereIn('id', $returnItemIds)->update([
                        'approval_status' => 'Rejected',
                        'approved_at' => now(),
                        'approved_by_user_id' => Auth::id(),
                        'remarks' => $rejectRemarks,
                    ]);
                }

                $borrowItemIds = collect($this->rejectBorrowItems)->pluck('id')->toArray();

                if (!empty($borrowItemIds)) {
                    AssetBorrowItem::whereIn('id', $borrowItemIds)->update([
                        'status' => 'Borrowed'
                    ]);
                }

                $transaction->update([
                    'return_remarks' => $rejectRemarks, 
                    'status' => 'ReturnRejected',
                ]);

                UserHistory::create([
                    'user_id' => $transaction->user_id,
                    'borrow_code' => $transaction->borrow_code,
                    'return_code' => $this->generateReturnCode(),
                    'status' => 'Return Denied',
                    'return_data' => [
                        'remarks' => $rejectRemarks,
                        'rejected_by' => Auth::user()->name,
                        'rejected_at' => now()->format('Y-m-d H:i:s')
                    ],
                    'action_date' => now()
                ]);
            });

            $this->sendReturnRejectionEmail($this->selectedTransaction->id, $this->rejectRemarks);

            $this->successMessage = "Return request rejected successfully!";
            $this->reset([
                'showRejectModal', 
                'showRejectConfirmModal',
                'selectedTransaction', 
                'rejectRemarks', 
                'rejectBorrowItems'
            ]);
            
            $this->dispatch('return-rejected')->self();

        } catch (\Exception $e) {
            Log::error("Reject return error: " . $e->getMessage());
            $this->errorMessage = "Error rejecting return: " . $e->getMessage();
        }
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
    
    private function generateReturnCode()
    {
        $date = now()->format('Ymd');
        $lastReturn = AssetReturnItem::where('return_code', 'like', "RT-{$date}-%")
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
    
    private function sendReturnApprovalEmail($transaction, $returnCode)
    {
        try {
            $emailService = new SendEmail();
            $user = $transaction->user;
            $approverName = Auth::user()->name;
            
            $returnItems = [];
            foreach ($transaction->borrowItems as $borrowItem) {
                foreach ($borrowItem->returnItems as $returnItem) {
                    if ($returnItem->approval_status === 'Approved') {
                        $returnItems[] = [
                            'asset_code' => $borrowItem->asset->asset_code,
                            'asset_name' => $borrowItem->asset->asset_name,
                            'model_number' => $borrowItem->asset->model_number,
                            'serial_number' => $borrowItem->asset->serial_number,
                            'quantity' => $borrowItem->quantity,                            
                        ];
                    }
                }
            }
            
            $emailContent = [
                'emails.return-approved-user', 
                [
                    'returnCode' => $returnCode,
                    'approverName' => $approverName,
                    'approvalDate' => now()->format('M d, Y H:i'),
                    'returnItems' => $returnItems
                ]
            ];
            
            $emailService->send(
                $user->email,
                "Return Approved: {$returnCode}",
                $emailContent,
                [],
                null,
                null,
                false
            );
        } catch (\Exception $e) {
            Log::error("Return approval email failed: " . $e->getMessage());
        }
    }

    private function sendReturnRejectionEmail($transactionId, $remarks)
    {
        try {
            $emailService = new SendEmail();
            
            $transaction = AssetBorrowTransaction::with([
                'user',
                'borrowItems.asset', 
                'borrowItems.returnItems' => function ($query) {
                    $query->where('approval_status', 'Rejected');
                }
            ])->findOrFail($transactionId);

            $user = $transaction->user;
            
            $assetDetails = [];
            foreach ($transaction->borrowItems as $borrowItem) {
                if ($borrowItem->returnItems->isNotEmpty()) {
                    $assetDetails[] = [
                        'asset_code' => $borrowItem->asset->asset_code,
                        'asset_name' => $borrowItem->asset->name, 
                        'model_number' => $borrowItem->asset->model_number,
                        'serial_number' => $borrowItem->asset->serial_number,
                        'quantity' => $borrowItem->quantity,
                        'purpose' => $borrowItem->purpose,
                    ];
                }
            }

            $emailContent = [
                'emails.return-denied-user',
                [
                    'returnCode' => $transaction->borrow_code,
                    'denialDate' => now()->format('M d, Y H:i'),
                    'remarks' => $remarks,
                    'assetDetails' => $assetDetails,
                ]
            ];

            $emailService->send(
                $user->email,
                "Return Request Denied: {$transaction->borrow_code}",
                $emailContent,
                [], 
                null, 
                null, 
                false 
            );
        } catch (\Exception $e) {
            Log::error("Return rejection email failed: " . $e->getMessage());
        }
    }
}