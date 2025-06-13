<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AssetBorrowTransaction;
use Illuminate\Support\Facades\Log;

class ApproveBorrowController extends Controller
{
    public function generatePDF($borrow_code)
    {
        try {
            $transaction = AssetBorrowTransaction::with([
                    'borrowItems.asset', 
                    'user', 
                    'userDepartment',
                    'requestedBy',
                    'approvedBy'
                ])
                ->where('borrow_code', $borrow_code)
                ->firstOrFail();
                
            $pdf = Pdf::loadView('pdf.borrow-approval', [
                'transaction' => $transaction,
                'approver' => $transaction->approvedBy,
                'approvalDate' => $transaction->approved_at->format('M d, Y H:i')
            ]);
            
            return $pdf->stream("Approval-{$borrow_code}.pdf");
            
        } catch (\Exception $e) {
            Log::error("PDF generation failed: " . $e->getMessage());
            abort(404, 'Borrow record not found');
        }
    }
}
