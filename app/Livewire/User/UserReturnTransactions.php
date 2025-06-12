<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\AssetReturnItem;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class UserReturnTransactions extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransaction = null;
    public $showReturnModal = false;
    public $showConfirmationModal = false;
    public $selectedItems = [];
    public $selectAll = true;
    public $returnRemarks = '';
    public $successMessage = '';
    public $errorMessage = '';

    #[Layout('components.layouts.app')]
    public function render()
    {
        $transactions = AssetBorrowTransaction::where('user_id', Auth::id())
            ->where('status', 'Approved')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('borrow_code', 'like', '%'.$this->search.'%')
                      ->orWhereDate('borrowed_at', 'like', '%'.$this->search.'%');
                });
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
            
        // Initialize all items as selected
        $this->selectedItems = $this->selectedTransaction->borrowItems
            ->pluck('id')
            ->mapWithKeys(fn($id) => [$id => true])
            ->toArray();
            
        $this->selectAll = true;
        $this->showReturnModal = true;
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedItems = $this->selectedTransaction->borrowItems
                ->pluck('id')
                ->mapWithKeys(fn($id) => [$id => true])
                ->toArray();
        } else {
            $this->selectedItems = [];
        }
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
            // Generate return code
            $returnCode = $this->generateReturnCode();
            $user = Auth::user();
            
            // Create return items
            foreach ($this->selectedItems as $itemId => $selected) {
                if (!$selected) continue;
                
                $borrowItem = AssetBorrowItem::find($itemId);
                
                AssetReturnItem::create([
                    'return_code' => $returnCode,
                    'borrow_item_id' => $itemId,
                    'returned_by_user_id' => $user->id,
                    'returned_by_department_id' => $user->department_id,
                    'returned_at' => now(),
                    'remarks' => $this->returnRemarks,
                ]);
            }
            
            // Generate PDF
            $pdf = $this->generateReturnPDF($returnCode);
            
            // Send email notification
            $this->sendReturnEmail($returnCode, $pdf);
            
            // Show success message
            $this->successMessage = "Return request submitted successfully!";
            
            // Reset state
            $this->reset([
                'showReturnModal', 
                'selectedTransaction', 
                'selectedItems', 
                'selectAll', 
                'returnRemarks'
            ]);
            
            // Open PDF in new tab
            $this->dispatch('openPdf', returnCode: $returnCode);
            
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to process return: " . $e->getMessage();
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
            
        $pdf = Pdf::loadView('pdf.return-asset', [
            'returnCode' => $returnCode,
            'returnItems' => $returnItems,
            'user' => Auth::user(),
            'returnDate' => now()->format('M d, Y H:i')
        ]);
        
        return $pdf;
    }
    
    private function sendReturnEmail($returnCode, $pdf)
    {
        $emailService = new SendEmail();
        $user = Auth::user();
        $adminEmail = config('mail.admin_email'); // Set in your .env
        
        $emailService->send(
            $adminEmail,
            "Asset Return Request: {$returnCode}",
            'emails.return-request',
            [
                'returnCode' => $returnCode,
                'userName' => $user->name,
                'returnDate' => now()->format('M d, Y H:i'),
                'remarks' => $this->returnRemarks
            ],
            $pdf->output(),
            "Return-{$returnCode}.pdf"
        );
        
        // Also send to user
        $emailService->send(
            $user->email,
            "Your Asset Return Request: {$returnCode}",
            'emails.return-confirmation',
            [
                'returnCode' => $returnCode,
                'returnDate' => now()->format('M d, Y H:i')
            ],
            $pdf->output(),
            "Return-{$returnCode}.pdf"
        );
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}