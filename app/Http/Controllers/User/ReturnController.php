<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function generatePDF($returnCode)
    {
        $returnItems = AssetReturnItem::with([
                'borrowItem.asset', 
                'borrowItem.transaction',
                'returnedBy',
                'returnedByDepartment'
            ])
            ->where('return_code', $returnCode)
            ->get();
            
        if ($returnItems->isEmpty()) {
            abort(404, 'Return record not found');
        }
        
        $firstItem = $returnItems->first();
        
        $pdf = Pdf::loadView('pdf.return-asset', [
            'returnCode' => $returnCode,
            'returnItems' => $returnItems,
            'user' => $firstItem->returnedBy,
            'returnDate' => $firstItem->returned_at ? $firstItem->returned_at->format('M d, Y H:i') : now()->format('M d, Y H:i')
        ]);
        
        return $pdf->stream("Return-{$returnCode}.pdf");
    }
        
}
