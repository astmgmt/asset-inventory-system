<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\User;
use App\Models\UserHistory;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\AssetCondition;
use Illuminate\Support\Facades\DB;
use App\Services\SendEmail;
// use App\Services\EmailTemplates;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View; 

class AssetAssignments extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedAssets = [];
    public $showCartModal = false;
    public $selectedForBorrow = [];
    public $successMessage = '';
    public $errorMessage = '';
    public $userIdentifier = '';
    public $isProcessing = false;
    public $generatedBorrowCode = null;

    public function updatedShowCartModal($value)
    {
        if ($value) {
            $this->selectedForBorrow = array_keys($this->selectedAssets);
            $this->errorMessage = '';
        }
    }

    public function render()
    {
        $assets = Asset::query()
            ->join('asset_conditions', 'asset_conditions.id', '=', 'assets.condition_id')
            ->whereIn('asset_conditions.condition_name', ['New', 'Available'])
            ->where('assets.is_disposed', false)
            ->whereNotIn('assets.id', array_keys($this->selectedAssets))
            ->whereRaw('(assets.quantity - assets.reserved_quantity) > 0')
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('assets.name', 'like', '%'.$this->search.'%')
                      ->orWhere('assets.asset_code', 'like', '%'.$this->search.'%')
                      ->orWhere('assets.model_number', 'like', '%'.$this->search.'%')
                      ->orWhere('asset_conditions.condition_name', 'like', '%'.$this->search.'%');
                });
            })
            ->select(
                'assets.*', 
                'asset_conditions.condition_name',
                DB::raw('(assets.quantity - assets.reserved_quantity) as available_quantity')
            )
            ->paginate(10);

        return view('livewire.super-admin.asset-assignments', compact('assets'));
    }

    public function addToCart($assetId)
    {
        $this->errorMessage = '';
        
        $asset = Asset::select('assets.*', 
                    DB::raw('(quantity - reserved_quantity) as available_quantity')
                )->find($assetId);

        if ($asset->available_quantity < 1) {
            $this->errorMessage = 'This asset is no longer available';
            return;
        }

        if (!isset($this->selectedAssets[$assetId])) {
            $this->selectedAssets[$assetId] = [
                'id' => $asset->id,
                'name' => $asset->name,
                'code' => $asset->asset_code,
                'quantity' => 1,
                'max_quantity' => $asset->available_quantity,
            ];
        }
    }

    public function updateCartQuantity($assetId, $newQuantity)
    {
        $newQuantity = (int)$newQuantity;
        if ($newQuantity < 1 || !isset($this->selectedAssets[$assetId])) return;

        $asset = Asset::select('assets.*', 
                    DB::raw('(quantity - reserved_quantity) as available_quantity')
                )->find($assetId);
                
        $available = $asset->available_quantity;

        if ($newQuantity > $available) {
            $this->addError('quantity_'.$assetId, 'Insufficient available quantity');
            $newQuantity = $available;
        }

        $this->selectedAssets[$assetId]['quantity'] = $newQuantity;
        $this->selectedAssets[$assetId]['max_quantity'] = $available;
    }

    public function removeFromCart($assetId)
    {
        if (isset($this->selectedAssets[$assetId])) {
            unset($this->selectedAssets[$assetId]);
            $this->removeFromSelected($assetId);
        }
    }

    public function removeFromSelected($assetId)
    {
        $key = array_search($assetId, $this->selectedForBorrow);
        if ($key !== false) {
            unset($this->selectedForBorrow[$key]);
        }
    }

    public function clearCart()
    {
        $this->selectedAssets = [];
        $this->selectedForBorrow = [];
        $this->userIdentifier = '';
        $this->generatedBorrowCode = null;
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    public function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function toggleSelectAll()
    {
        if (count($this->selectedForBorrow) === count($this->selectedAssets)) {
            $this->selectedForBorrow = [];
        } else {
            $this->selectedForBorrow = array_keys($this->selectedAssets);
        }
    }

    public function assign()
    {
        $this->errorMessage = '';
        $this->isProcessing = true;
        
        if (empty($this->selectedForBorrow)) {
            $this->errorMessage = 'Please select at least one asset';
            $this->isProcessing = false;
            return;
        }

        if (empty($this->userIdentifier)) {
            $this->errorMessage = 'Please enter user email or username';
            $this->isProcessing = false;
            return;
        }

        $user = User::where('email', $this->userIdentifier)
                    ->orWhere('username', $this->userIdentifier)
                    ->first();

        if (!$user) {
            $this->errorMessage = 'User not found';
            $this->isProcessing = false;
            return;
        }

        try {
            $transaction = null;
            $borrowCode = null;
            
            DB::transaction(function () use (&$transaction, $user, &$borrowCode) {
                $borrowCode = $this->generateBorrowCode();
                $transaction = AssetBorrowTransaction::create([
                    'borrow_code' => $borrowCode,
                    'user_id' => $user->id,
                    'user_department_id' => $user->department_id,
                    'requested_by_user_id' => Auth::id(),
                    'requested_by_department_id' => Auth::user()->department_id,
                    'approved_by_user_id' => Auth::id(),
                    'approved_by_department_id' => Auth::user()->department_id,
                    'status' => 'Borrowed',
                    'borrowed_at' => now(),
                    'approved_at' => now(),
                ]);

                foreach ($this->selectedForBorrow as $assetId) {
                    if (!isset($this->selectedAssets[$assetId])) continue;

                    $item = $this->selectedAssets[$assetId];
                    $asset = Asset::with('condition')
                        ->where('id', $assetId)
                        ->lockForUpdate()
                        ->first();

                    if (!$asset) {
                        throw new \Exception("Asset '{$item['name']}' not found");
                    }

                    if ($asset->is_disposed) {
                        throw new \Exception("Asset '{$item['name']}' has been disposed");
                    }

                    $available = $asset->quantity - $asset->reserved_quantity;
                    
                    if ($available < $item['quantity']) {
                        throw new \Exception(
                            "Insufficient available quantity for '{$item['name']}'. " .
                            "Available: {$available}, Requested: {$item['quantity']}"
                        );
                    }

                    $asset->increment('reserved_quantity', $item['quantity']);

                    AssetBorrowItem::create([
                        'borrow_transaction_id' => $transaction->id,
                        'asset_id' => $assetId,
                        'quantity' => $item['quantity'],
                        'status' => 'Borrowed',
                    ]);
                }
                
                // Load transaction with relationships for history
                $transaction->load([
                    'borrowItems.asset', 
                    'user', 
                    'userDepartment',
                    'requestedBy'
                ]);
                
                // Create user history record with full transaction data
                UserHistory::create([
                    'user_id' => $user->id,
                    'borrow_code' => $borrowCode,
                    'status' => 'Borrow Approved',
                    'borrow_data' => $transaction->toArray(), // Full transaction data
                    'action_date' => now(),
                ]);
            });

            $this->generatedBorrowCode = $borrowCode;
            $this->sendAssignmentEmail($transaction, $user);
            $this->clearCart();
            $this->successMessage = 'Assets assigned successfully!';
            $this->showCartModal = false;

            // Generate PDF for download
            $transaction->load([
                'borrowItems.asset', 
                'user', 
                'userDepartment',
                'requestedBy',
                'approvedBy'
            ]);
            
            $approver = $transaction->approvedBy;
            $approvalDate = $transaction->approved_at->format('M d, Y H:i');
            
            $pdf = Pdf::loadView('pdf.borrow-accountability', compact('transaction', 'approver', 'approvalDate'));
            
            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                "Borrower-Accountability-{$this->generatedBorrowCode}.pdf"
            );

        } catch (\Exception $e) {
            Log::error("Asset assignment failed: " . $e->getMessage());
            $this->errorMessage = 'Error: '.$e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    private function sendAssignmentEmail($transaction, $user)
    {
        $assignedAssets = [];
        foreach ($this->selectedForBorrow as $assetId) {
            if (!isset($this->selectedAssets[$assetId])) continue;
            
            $asset = $this->selectedAssets[$assetId];
            $assignedAssets[] = [
                'name' => $asset['name'],
                'code' => $asset['code'],
                'quantity' => $asset['quantity'],
            ];
        }
        
        $borrowCode = $transaction->borrow_code;

        // Generate email body using Blade template
        $body = View::make('emails.assign-assets', [
            'borrowCode' => $borrowCode,
            'userName' => $user->name,
            'assignedAssets' => $assignedAssets,
            'approvalDate' => $transaction->approved_at
        ])->render();

        // Generate PDF
        $transaction->load([
            'borrowItems.asset', 
            'user', 
            'userDepartment',
            'requestedBy',
            'approvedBy'
        ]);
        
        $approver = $transaction->approvedBy;
        $approvalDate = $transaction->approved_at->format('M d, Y H:i');
        $pdf = Pdf::loadView('pdf.borrow-accountability', compact('transaction', 'approver', 'approvalDate'));
        $pdfContent = $pdf->output();
        
        // Send email
        $emailService = new SendEmail();
        $emailService->send(
            $user->email,
            "Asset Assignment #{$borrowCode}", 
            $body,
            [], // cc
            $pdfContent,
            "Borrower-Accountability-{$borrowCode}.pdf",
            true // isHtml
        );
    }

    

    private function generateBorrowCode()
    {
        $date = now()->format('Ymd');
        $lastCode = AssetBorrowTransaction::where('borrow_code', 'like', "BR-{$date}-%")
            ->orderBy('borrow_code', 'desc')
            ->first();

        $lastNum = $lastCode ? intval(substr($lastCode->borrow_code, -8)) : 0;
        $newNum = str_pad($lastNum + 1, 8, '0', STR_PAD_LEFT);

        return "BR-{$date}-{$newNum}";
    }
}