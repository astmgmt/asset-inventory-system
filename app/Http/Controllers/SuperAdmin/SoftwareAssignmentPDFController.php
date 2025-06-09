<?php


namespace App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\Controller; 
use App\Models\SoftwareAssignment;
use Barryvdh\DomPDF\Facade\Pdf;

class SoftwareAssignmentPDFController extends Controller
{
    public function generatePDF($id)
    {
        $assignment = SoftwareAssignment::with(['user', 'admin', 'software'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.software-assignment', compact('assignment'));
        return $pdf->stream('software_assignment_'.$assignment->reference_no.'.pdf');
    }
}