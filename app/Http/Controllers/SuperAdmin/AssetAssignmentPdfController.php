<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AssetAssignment;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetAssignmentPdfController extends Controller
{
    public function generate($id)
    {
        $assignment = AssetAssignment::with(['user', 'admin', 'asset'])->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.asset-assignment', [
            'assignment' => $assignment,
            'company' => config('app.name')
        ]);

        return $pdf->stream("assignment-{$assignment->reference_no}.pdf");
    }
}
