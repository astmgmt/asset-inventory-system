<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\AssetReturnItem;
use App\Models\User;
use App\Models\Department;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserReturnTransactions extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransaction = null;
    public $showReturnModal = false;
    public $showViewModal = false;
    public $showConfirmationModal = false;
    public $returnRemarks = '';
    public $successMessage = '';
    public $errorMessage = '';
    public $selectedItems = [];
    public $selectAll = true;

    public function render()
    {
        $transactions = AssetBorrowTransaction::where('user_id', Auth::id())
            ->whereIn('status', ['Borrowed', 'PendingReturnApproval', 'ReturnRejected'])
            ->when($this->search, function ($query) {
                $query->where('borrow_code', 'like', '%'.$this->search.'%');
            })
            ->with(['borrowItems.asset', 'user.department'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.user.user-return-transactions', [
            'transactions' => $transactions
        ]);
    }

    public function openReturnModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with('borrowItems.asset')
            ->findOrFail($transactionId);
            
        $this->returnRemarks = '';
        $this->selectedItems = $this->selectedTransaction->borrowItems->pluck('id')->toArray();
        $this->selectAll = true;
        $this->showReturnModal = true;
    }

    public function openViewModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with('borrowItems.asset')
            ->findOrFail($transactionId);
        $this->showViewModal = true;
    }

    public function updatedSelectAll($value)
    {
        $this->selectedItems = $value
            ? $this->selectedTransaction->borrowItems->pluck('id')->toArray()
            : [];
    }

    public function updatedSelectedItems()
    {
        $this->selectAll = count($this->selectedItems) === 
                          $this->selectedTransaction->borrowItems->count();
    }

    public function confirmReturn()
    {
        $this->validate([
            'returnRemarks' => 'nullable|string|max:500',
        ]);
        
        if (empty($this->selectedItems)) {
            $this->errorMessage = "Please select at least one asset to return.";
            return;
        }
        
        $this->showConfirmationModal = true;
    }

    public function processReturn()
    {
        $this->showConfirmationModal = false;
        
        try {
            DB::transaction(function () {
                $transaction = $this->selectedTransaction;
                
                // Create a new return transaction
                $returnCode = 'RT-' . now()->format('Ymd') . '-' . str_pad(AssetReturnItem::count() + 1, 8, '0', STR_PAD_LEFT);
                
                // Get selected borrow items
                $selectedBorrowItems = AssetBorrowItem::whereIn('id', $this->selectedItems)
                    ->with('asset')
                    ->get();
                
                // Create return items only for selected assets
                foreach ($selectedBorrowItems as $borrowItem) {
                    AssetReturnItem::create([
                        'return_code' => $returnCode,
                        'borrow_item_id' => $borrowItem->id,
                        'returned_by_user_id' => Auth::id(),
                        'returned_by_department_id' => Auth::user()->department_id,
                        'remarks' => $this->returnRemarks,
                        'returned_at' => null, // Will be set when approved
                    ]);
                    
                    // Update item status to PendingReturnApproval
                    $borrowItem->update(['status' => 'PendingReturnApproval']);
                }
                
                // Get unselected items
                $unselectedItems = $transaction->borrowItems()
                    ->whereNotIn('id', $this->selectedItems)
                    ->get();
                
                // Keep unselected items as Borrowed
                foreach ($unselectedItems as $item) {
                    $item->update(['status' => 'Borrowed']);
                }
                
                // Update transaction status
                $transaction->update([
                    'status' => 'PendingReturnApproval',
                    'return_requested_at' => now(),
                    'return_remarks' => $this->returnRemarks
                ]);
                
                // Send email notification to admin with selected items
                $this->sendReturnRequestEmail($transaction->borrow_code, $returnCode, $selectedBorrowItems);
                
                // Show success message
                $this->successMessage = "Return request submitted for admin approval!";
                
                // Reset state
                $this->reset([
                    'showReturnModal', 
                    'selectedTransaction', 
                    'returnRemarks',
                    'selectedItems',
                    'selectAll'
                ]);
            });
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to process return request: " . $e->getMessage();
        }
    }

    private function sendReturnRequestEmail($borrowCode, $returnCode, $selectedBorrowItems)
    {
        try {
            $emailService = new SendEmail();
            $transaction = AssetBorrowTransaction::with(['user'])
                ->where('borrow_code', $borrowCode)
                ->first();
            
            if (!$transaction || !$transaction->user) {
                throw new \Exception("User information not found for transaction");
            }
            
            $user = $transaction->user;
            
            // Get admin emails with proper fallback
            $to = config('mail.admin_email', 'admin@example.com');
            
            // Get super admin email if available
            $superAdmin = User::whereHas('role', function($q) {
                $q->where('name', 'Super Admin');
            })->first();
            
            // Get admin emails
            $admins = User::whereHas('role', function($q) {
                $q->where('name', 'Admin');
            })->get();
            
            $cc = $admins->pluck('email')->filter()->toArray();
            
            // Use super admin email if available
            if ($superAdmin && $superAdmin->email) {
                $to = $superAdmin->email;
            }
            
            // Filter valid emails
            $validAdminEmail = filter_var($to, FILTER_VALIDATE_EMAIL);
            
            if ($validAdminEmail) {
                // Prepare email data
                $emailData = [
                    'returnCode' => $returnCode,
                    'borrowCode' => $borrowCode,
                    'userName' => $user->name,
                    'returnDate' => now()->format('M d, Y H:i'),
                    'remarks' => $this->returnRemarks,
                    'selectedBorrowItems' => $selectedBorrowItems
                ];
                
                // Send using correct email structure
                $emailService->send(
                    $to,
                    "Return Request: {$returnCode}",
                    [
                        'emails.return-request-admin',  // View name
                        $emailData                     // Data array
                    ],
                    $cc,
                    null,   // No PDF attachment
                    null,   // No attachment name
                    false   // Use view instead of raw HTML
                );
            } else {
                Log::error("Invalid admin email: " . ($to ?? 'NULL'));
            }
        } catch (\Exception $e) {
            Log::error("Return request email failed: " . $e->getMessage());
        }
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}