<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AssetReturnItem;
use App\Models\BorrowAssetQuantity;
use App\Models\Asset;
use App\Models\AssetBorrowTransaction;
use App\Services\SendEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReturnApprovalController extends Controller
{
    public function index()
    {
        $returnRequests = AssetReturnItem::with([
                'borrowItem.asset', 
                'borrowItem.transaction.user',
                'returnedBy'
            ])
            ->where('status', 'Pending')
            ->groupBy('return_code')
            ->paginate(10);
            
        return view('admin.return-requests', compact('returnRequests'));
    }

    public function approve($returnCode)
    {
        DB::transaction(function () use ($returnCode) {
            $returnItems = AssetReturnItem::with('borrowItem')
                ->where('return_code', $returnCode)
                ->get();
            
            foreach ($returnItems as $item) {
                // Update return status
                $item->update([
                    'status' => 'Approved',
                    'approved_by_user_id' => auth()->id(),
                    'approved_at' => now()
                ]);
                
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
            
            // Check if all items in transaction are returned
            $transactionId = $returnItems->first()->borrowItem->borrow_transaction_id;
            $allReturned = AssetBorrowItem::where('borrow_transaction_id', $transactionId)
                ->doesntHave('returnItem', 'or', function ($query) {
                    $query->where('status', '!=', 'Approved');
                })
                ->exists();
            
            if ($allReturned) {
                AssetBorrowTransaction::find($transactionId)->update(['status' => 'Returned']);
            }
            
            // Send approval notification to user
            $this->sendApprovalEmail($returnCode, $returnItems);
        });
        
        return redirect()->back()->with('success', 'Return request approved successfully!');
    }
    
    private function sendApprovalEmail($returnCode, $returnItems)
    {
        try {
            $emailService = new SendEmail();
            $user = $returnItems->first()->returnedBy;
            $pdf = $this->generateApprovalPDF($returnCode);
            
            $emailService->send(
                $user->email,
                "Return Approved: {$returnCode}",
                [
                    'emails.return-approved-user',
                    [
                        'returnCode' => $returnCode,
                        'approvalDate' => now()->format('M d, Y H:i')
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
    
    private function generateApprovalPDF($returnCode)
    {
        $returnItems = AssetReturnItem::with([
                'borrowItem.asset', 
                'borrowItem.transaction',
                'approvedBy'
            ])
            ->where('return_code', $returnCode)
            ->get();
            
        $pdf = Pdf::loadView('pdf.return-approval', [
            'returnCode' => $returnCode,
            'returnItems' => $returnItems,
            'approvalDate' => now()->format('M d, Y H:i'),
            'approvedBy' => auth()->user()
        ]);
        
        return $pdf;
    }
}