<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\Asset;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

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

    public function render()
    {
        $transactions = AssetBorrowTransaction::where('user_id', Auth::id())
            ->whereIn('status', ['Borrowed', 'PendingReturnApproval', 'ReturnRejected'])
            ->when($this->search, function ($query) {
                $query->where('borrow_code', 'like', '%'.$this->search.'%');
            })
            ->with(['borrowItems.asset', 'user.department']) // Add department relationship
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
        $this->showReturnModal = true;
    }

    public function openViewModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with('borrowItems.asset')
            ->findOrFail($transactionId);
        $this->showViewModal = true;
    }

    public function confirmReturn()
    {
        $this->validate([
            'returnRemarks' => 'nullable|string|max:500',
        ]);
        
        $this->showConfirmationModal = true;
    }

    public function processReturn()
    {
        $this->showConfirmationModal = false;
        
        try {
            DB::transaction(function () {
                $transaction = $this->selectedTransaction;
                
                // Update transaction status
                $transaction->update([
                    'status' => 'PendingReturnApproval',
                    'return_requested_at' => now(),
                    'return_remarks' => $this->returnRemarks
                ]);
                
                // Generate PDF
                $pdf = $this->generateReturnPDF($transaction->borrow_code);
                
                // Send email notification to admin
                $this->sendReturnRequestEmail($transaction->borrow_code, $pdf);
                
                // Show success message
                $this->successMessage = "Return request submitted for admin approval!";
                
                // Reset state
                $this->reset([
                    'showReturnModal', 
                    'selectedTransaction', 
                    'returnRemarks'
                ]);
            });
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to process return request: " . $e->getMessage();
        }
    }
    
    
    private function generateReturnPDF($borrowCode)
    {
        $transaction = AssetBorrowTransaction::with([
                'borrowItems.asset',
                'user.department' // Load user and department relationship
            ])
            ->where('borrow_code', $borrowCode)
            ->first();
            
        $pdf = Pdf::loadView('pdf.return-request', [
            'borrowCode' => $borrowCode,
            'transaction' => $transaction, // Pass entire transaction
            'returnDate' => now()->format('M d, Y H:i'),
            'remarks' => $this->returnRemarks
        ]);
        
        return $pdf;
    }

    private function sendReturnRequestEmail($borrowCode, $pdf)
    {
        try {
            $emailService = new SendEmail();
            $transaction = AssetBorrowTransaction::with('user')
                ->where('borrow_code', $borrowCode)
                ->first();
            
            if (!$transaction || !$transaction->user) {
                throw new \Exception("User information not found for transaction");
            }
            
            $user = $transaction->user;
            
            // Get admin emails
            $superAdmin = User::whereHas('role', function($q) {
                $q->where('name', 'Super Admin');
            })->first();
            
            $admins = User::whereHas('role', function($q) {
                $q->where('name', 'Admin');
            })->get();
            
            $to = $superAdmin ? $superAdmin->email : config('mail.admin_email');
            $cc = $admins->pluck('email')->toArray();
            
            // Filter valid emails
            $validAdminEmail = filter_var($to, FILTER_VALIDATE_EMAIL);
            $validUserEmail = filter_var($user->email, FILTER_VALIDATE_EMAIL);
            
            if ($validAdminEmail) {
                $emailService->send(
                    $to,
                    "Return Request: {$borrowCode}",
                    [
                        'emails.return-request-admin',
                        [
                            'borrowCode' => $borrowCode,
                            'userName' => $user->name,
                            'returnDate' => now()->format('M d, Y H:i'),
                            'remarks' => $this->returnRemarks,
                            'transaction' => $transaction
                        ]
                    ],
                    $cc,
                    $pdf->output(),
                    "Return-Request-{$borrowCode}.pdf",
                    false
                );
            }
            
            if ($validUserEmail) {
                $emailService->send(
                    $user->email,
                    "Your Return Request: {$borrowCode}",
                    [
                        'emails.return-request-user',
                        [
                            'borrowCode' => $borrowCode,
                            'returnDate' => now()->format('M d, Y H:i')
                        ]
                    ],
                    [],
                    $pdf->output(),
                    "Return-Request-{$borrowCode}.pdf",
                    false
                );
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