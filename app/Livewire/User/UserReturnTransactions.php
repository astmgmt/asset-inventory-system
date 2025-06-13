<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\AssetReturnItem;
use App\Models\BorrowAssetQuantity;
use App\Models\Asset;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;


class UserReturnTransactions extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransaction = null;
    public $showReturnModal = false;
    public $showConfirmationModal = false;
    public $returnRemarks = '';
    public $successMessage = '';
    public $errorMessage = '';

    public function render()
    {
        $transactions = AssetBorrowTransaction::where('user_id', Auth::id())
            ->where('status', 'Approved')
            ->when($this->search, function ($query) {
                $query->where('borrow_code', 'like', '%'.$this->search.'%');
            })
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
                $returnCode = $this->generateReturnCode();
                $user = Auth::user();
                
                // Create return request (not final return)
                foreach ($this->selectedTransaction->borrowItems as $borrowItem) {
                    AssetReturnItem::create([
                        'return_code' => $returnCode,
                        'borrow_item_id' => $borrowItem->id,
                        'returned_by_user_id' => $user->id,
                        'returned_by_department_id' => $user->department_id,
                        'returned_at' => now(),
                        'remarks' => $this->returnRemarks,
                        'status' => 'Pending', // Awaiting admin approval
                    ]);
                }
                
                // Generate PDF
                $pdf = $this->generateReturnPDF($returnCode);
                
                // Send email notification to admin
                $this->sendReturnRequestEmail($returnCode, $pdf);
                
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
    
    private function generateReturnCode()
    {
        $today = now()->format('Ymd');
        $lastReturn = AssetReturnItem::where('return_code', 'like', "RT-{$today}-%")
            ->orderBy('return_code', 'desc')
            ->first();
            
        $number = $lastReturn ? (int)substr($lastReturn->return_code, -8) + 1 : 1;
        
        return sprintf("RT-%s-%08d", $today, $number);
    }
    
    private function generateReturnPDF($returnCode)
    {
        $returnItems = AssetReturnItem::with([
                'borrowItem.asset', 
                'borrowItem.transaction'
            ])
            ->where('return_code', $returnCode)
            ->get();
            
        $pdf = Pdf::loadView('pdf.return-request', [
            'returnCode' => $returnCode,
            'returnItems' => $returnItems,
            'user' => Auth::user(),
            'returnDate' => now()->format('M d, Y H:i')
        ]);
        
        return $pdf;
    }
    
    private function sendReturnRequestEmail($returnCode, $pdf)
    {
        try {
            $emailService = new SendEmail();
            $user = Auth::user();
            
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
                    "Return Request: {$returnCode}",
                    [
                        'emails.return-request-admin',
                        [
                            'returnCode' => $returnCode,
                            'userName' => $user->name,
                            'returnDate' => now()->format('M d, Y H:i'),
                            'remarks' => $this->returnRemarks,
                            'transaction' => $this->selectedTransaction
                        ]
                    ],
                    $cc,
                    $pdf->output(),
                    "Return-Request-{$returnCode}.pdf",
                    false
                );
            }
            
            if ($validUserEmail) {
                $emailService->send(
                    $user->email,
                    "Your Return Request: {$returnCode}",
                    [
                        'emails.return-request-user',
                        [
                            'returnCode' => $returnCode,
                            'returnDate' => now()->format('M d, Y H:i')
                        ]
                    ],
                    [],
                    $pdf->output(),
                    "Return-Request-{$returnCode}.pdf",
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