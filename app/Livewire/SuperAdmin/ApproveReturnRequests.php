<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\AssetBorrowItem; 
use App\Models\BorrowAssetQuantity; 
use App\Models\Asset; 
use App\Models\AssetBorrowTransaction; 
use App\Models\User; 
use App\Services\SendEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.app')]
class ApproveReturnRequests extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedReturn = null;
    public $showDetailsModal = false;
    public $showApproveModal = false;
    public $showDenyModal = false;
    public $approveRemarks = '';
    public $denyRemarks = '';
    public $successMessage = '';
    public $errorMessage = '';

    public function render()
    {
        // Get distinct return codes with pending status
        $returnCodes = \App\Models\AssetReturnItem::where('status', 'Pending')
            ->distinct('return_code')
            ->pluck('return_code');

        // Get the return requests
        $returnRequests = \App\Models\AssetReturnItem::with([
                'returnedBy', 
                'borrowItem.transaction'
            ])
            ->whereIn('return_code', $returnCodes)
            ->select(
                'return_code',
                'returned_at',
                'returned_by_user_id',
                'status'
            )
            ->distinct()
            ->orderBy('returned_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.approve-return-requests', [
            'returnRequests' => $returnRequests
        ]);
    }

    public function showDetails($returnCode)
    {
        $this->selectedReturn = \App\Models\AssetReturnItem::with([
                'borrowItem.asset',
                'returnedBy',
                'borrowItem.transaction'
            ])
            ->where('return_code', $returnCode)
            ->get();
            
        $this->showDetailsModal = true;
    }

    public function confirmApprove($returnCode)
    {
        $this->selectedReturn = \App\Models\AssetReturnItem::with(['borrowItem.asset'])
            ->where('return_code', $returnCode)
            ->get();
            
        $this->approveRemarks = '';
        $this->showApproveModal = true;
    }

    public function confirmDeny($returnCode)
    {
        $this->selectedReturn = \App\Models\AssetReturnItem::where('return_code', $returnCode)
            ->get();
            
        $this->denyRemarks = '';
        $this->showDenyModal = true;
    }

    public function approveRequest()
    {
        $this->validate([
            'approveRemarks' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () {
                $returnCode = $this->selectedReturn->first()->return_code;
                $approver = Auth::user();
                
                // Update all items in this return request
                \App\Models\AssetReturnItem::where('return_code', $returnCode)
                    ->update([
                        'status' => 'Approved',
                        'approved_by_user_id' => $approver->id,
                        'approved_at' => now(),
                        'remarks' => $this->approveRemarks
                    ]);
                
                // Process each returned item
                foreach ($this->selectedReturn as $item) {
                    // Update asset quantity
                    $quantityRecord = BorrowAssetQuantity::firstOrNew(['asset_id' => $item->borrowItem->asset_id]);
                    $quantityRecord->available_quantity += $item->borrowItem->quantity;
                    $quantityRecord->save();
                    
                    // Update asset status
                    $asset = Asset::find($item->borrowItem->asset_id);
                    if ($asset->condition_name !== 'Available') {
                        $asset->update(['condition_name' => 'Available']);
                    }
                }
                
                // Check if all items in parent transaction are returned
                $transactionId = $this->selectedReturn->first()->borrowItem->borrow_transaction_id;
                $allItems = AssetBorrowItem::where('borrow_transaction_id', $transactionId)->get();
                $allReturned = true;
                
                foreach ($allItems as $borrowItem) {
                    $returnItem = \App\Models\AssetReturnItem::where('borrow_item_id', $borrowItem->id)
                        ->where('status', 'Approved')
                        ->exists();
                        
                    if (!$returnItem) {
                        $allReturned = false;
                        break;
                    }
                }
                
                if ($allReturned) {
                    AssetBorrowTransaction::find($transactionId)->update(['status' => 'Returned']);
                }
                
                // Generate PDF
                $pdf = $this->generateApprovalPDF($returnCode);
                
                // Send email notifications
                $this->sendApprovalEmails($returnCode, $pdf);
                
                // Show success message
                $this->successMessage = "Return request approved successfully!";
                
                // Reset state
                $this->reset([
                    'showApproveModal', 
                    'selectedReturn', 
                    'approveRemarks'
                ]);
            });
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to approve request: " . $e->getMessage();
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
            DB::transaction(function () {
                $returnCode = $this->selectedReturn->first()->return_code;
                
                // Update all items in this return request
                \App\Models\AssetReturnItem::where('return_code', $returnCode)
                    ->update([
                        'status' => 'Rejected',
                        'remarks' => $this->denyRemarks
                    ]);
                
                // Send denial email
                $this->sendDenialEmail($returnCode);
                
                // Show success message
                $this->successMessage = "Return request denied successfully!";
                
                // Reset state
                $this->reset([
                    'showDenyModal', 
                    'selectedReturn', 
                    'denyRemarks'
                ]);
            });
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to deny request: " . $e->getMessage();
        }
    }
    
    private function generateApprovalPDF($returnCode)
    {
        $returnItems = \App\Models\AssetReturnItem::with([
                'borrowItem.asset', 
                'returnedBy',
                'borrowItem.transaction' // Removed approvedBy from here
            ])
            ->where('return_code', $returnCode)
            ->get();
            
        $pdf = Pdf::loadView('pdf.return-approval', [
            'returnCode' => $returnCode,
            'returnItems' => $returnItems,
            'approvalDate' => now()->format('M d, Y H:i'),
            'approver' => Auth::user() // We already have the approver here
        ]);
        
        return $pdf;
    }
    
    private function sendApprovalEmails($returnCode, $pdf)
    {
        try {
            $emailService = new SendEmail();
            $returnItems = \App\Models\AssetReturnItem::with([
                    'returnedBy', 
                    'borrowItem.asset',
                    'borrowItem.transaction'
                ])
                ->where('return_code', $returnCode)
                ->get();
            
            $user = $returnItems->first()->returnedBy;
            $approver = Auth::user();
            
            // Send to admin
            $emailService->send(
                $approver->email,
                "Return Approved: {$returnCode}",
                [
                    'emails.return-approved-admin',
                    [
                        'returnCode' => $returnCode,
                        'userName' => $user->name,
                        'userDepartment' => $user->department->name ?? 'N/A',
                        'returnDate' => $returnItems->first()->returned_at->format('M d, Y H:i'),
                        'approverName' => $approver->name,
                        'approverDepartment' => $approver->department->name ?? 'N/A',
                        'approvalDate' => now()->format('M d, Y H:i'),
                        'remarks' => $this->approveRemarks,
                        'returnItems' => $returnItems
                    ]
                ],
                [],
                $pdf->output(),
                "Return-Approval-{$returnCode}.pdf",
                false
            );
            
            // Send to user
            $emailService->send(
                $user->email,
                "Your Return Approved: {$returnCode}",
                [
                    'emails.return-approved-user',
                    [
                        'returnCode' => $returnCode,
                        'approvalDate' => now()->format('M d, Y H:i'),
                        'approverName' => $approver->name
                    ]
                ],
                [],
                $pdf->output(),
                "Return-Approval-{$returnCode}.pdf",
                false
            );
        } catch (\Exception $e) {
            Log::error("Approval email failed: " . $e->getMessage());
        }
    }
    
    private function sendDenialEmail($returnCode)
    {
        try {
            $emailService = new SendEmail();
            $returnItems = \App\Models\AssetReturnItem::with(['returnedBy'])
                ->where('return_code', $returnCode)
                ->get();
            
            $user = $returnItems->first()->returnedBy;
            
            $emailService->send(
                $user->email,
                "Return Request Denied: {$returnCode}",
                [
                    'emails.return-denied-user',
                    [
                        'returnCode' => $returnCode,
                        'denialDate' => now()->format('M d, Y H:i'),
                        'remarks' => $this->denyRemarks
                    ]
                ],
                [],
                null,
                null,
                false
            );
        } catch (\Exception $e) {
            Log::error("Denial email failed: " . $e->getMessage());
        }
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}