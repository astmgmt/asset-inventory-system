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
        
        $pdf = Pdf::loadView('pdf.return-asset', [
            'returnCode' => $returnCode,
            'returnItems' => $returnItems,
            'user' => $returnItems->first()->returnedBy,
            'returnDate' => $returnItems->first()->returned_at->format('M d, Y H:i')
        ]);
        
        return $pdf->stream("Return-{$returnCode}.pdf");
    }
        
}
