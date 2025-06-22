<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AssetBorrowTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class AssignAssetController extends Controller
{
    public function generateAccountabilityPDF($borrow_code)
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

            $approver = $transaction->approvedBy;
            $approvalDate = $transaction->approved_at->format('M d, Y H:i');

            $pdf = Pdf::loadView('pdf.borrow-accountability', compact('transaction', 'approver', 'approvalDate'));

            return $pdf->download("Borrower-Accountability-{$borrow_code}.pdf");

        } catch (\Exception $e) {
            Log::error("PDF generation failed: " . $e->getMessage());
            abort(404, 'Borrow record not found');
        }
    }
}