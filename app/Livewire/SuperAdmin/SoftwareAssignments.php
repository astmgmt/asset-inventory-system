<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Software;
use App\Models\User;
use App\Models\SoftwareAssignmentBatch;
use App\Models\SoftwareAssignmentItem;
use Illuminate\Support\Facades\DB;
use App\Services\SendEmail;
use App\Services\EmailTemplates;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class SoftwareAssignments extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedSoftware = [];
    public $showCartModal = false;
    public $selectedForAssignment = [];
    public $successMessage = '';
    public $errorMessage = '';
    public $userIdentifier = '';
    public $isProcessing = false;
    public $generatedAssignmentNo = null;

    public function updatedShowCartModal($value)
    {
        if ($value) {
            $this->selectedForAssignment = array_keys($this->selectedSoftware);
            $this->errorMessage = '';
        }
    }

    public function render()
    {
        $software = Software::query()
            ->where('expiry_status', 'active')
            ->where('expiry_flag', false)
            ->whereNotIn('id', array_keys($this->selectedSoftware))
            ->whereRaw('(quantity - reserved_quantity) > 0')
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('software_name', 'like', '%'.$this->search.'%')
                      ->orWhere('software_code', 'like', '%'.$this->search.'%')
                      ->orWhere('license_key', 'like', '%'.$this->search.'%');
                });
            })
            ->select(
                '*',
                DB::raw('(quantity - reserved_quantity) as available_quantity')
            )
            ->paginate(10);

        return view('livewire.super-admin.software-assignments', compact('software'));
    }

    public function addToCart($softwareId)
    {
        $this->errorMessage = '';
        
        $software = Software::select(
                    '*',
                    DB::raw('(quantity - reserved_quantity) as available_quantity')
                )->find($softwareId);

        if ($software->available_quantity < 1) {
            $this->errorMessage = 'This software is no longer available';
            return;
        }

        if (!isset($this->selectedSoftware[$softwareId])) {
            $this->selectedSoftware[$softwareId] = [
                'id' => $software->id,
                'name' => $software->software_name,
                'code' => $software->software_code,
                'license_key' => $software->license_key,
                'quantity' => 1,
                'max_quantity' => $software->available_quantity,
            ];
        }
    }

    public function updateCartQuantity($softwareId, $newQuantity)
    {
        $newQuantity = (int)$newQuantity;
        if ($newQuantity < 1 || !isset($this->selectedSoftware[$softwareId])) return;

        $software = Software::select(
                    '*',
                    DB::raw('(quantity - reserved_quantity) as available_quantity')
                )->find($softwareId);
                
        $available = $software->available_quantity;

        if ($newQuantity > $available) {
            $this->addError('quantity_'.$softwareId, 'Insufficient available quantity');
            $newQuantity = $available;
        }

        $this->selectedSoftware[$softwareId]['quantity'] = $newQuantity;
        $this->selectedSoftware[$softwareId]['max_quantity'] = $available;
    }

    public function removeFromCart($softwareId)
    {
        if (isset($this->selectedSoftware[$softwareId])) {
            unset($this->selectedSoftware[$softwareId]);
            $this->removeFromSelected($softwareId);
        }
    }

    public function removeFromSelected($softwareId)
    {
        $key = array_search($softwareId, $this->selectedForAssignment);
        if ($key !== false) {
            unset($this->selectedForAssignment[$key]);
        }
    }

    public function clearCart()
    {
        $this->selectedSoftware = [];
        $this->selectedForAssignment = [];
        $this->userIdentifier = '';
        $this->generatedAssignmentNo = null;
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
        if (count($this->selectedForAssignment) === count($this->selectedSoftware)) {
            $this->selectedForAssignment = [];
        } else {
            $this->selectedForAssignment = array_keys($this->selectedSoftware);
        }
    }

    public function assign()
    {
        $this->errorMessage = '';
        $this->isProcessing = true;
        
        if (empty($this->selectedForAssignment)) {
            $this->errorMessage = 'Please select at least one software';
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
            $batch = null;
            $assignmentData = [];
            
            DB::transaction(function () use (&$batch, $user, &$assignmentData) {
                $assignmentNo = $this->generateAssignmentNo();
                $batch = SoftwareAssignmentBatch::create([
                    'assignment_no' => $assignmentNo,
                    'user_id' => $user->id,
                    'assigned_by' => Auth::id(),
                    'approved_by' => Auth::id(),
                    'purpose' => 'Software assignment',
                    'status' => 'Assigned',
                    'date_assigned' => now(),
                    'approved_at' => now(),
                ]);

                foreach ($this->selectedForAssignment as $softwareId) {
                    if (!isset($this->selectedSoftware[$softwareId])) continue;

                    $item = $this->selectedSoftware[$softwareId];
                    $software = Software::where('id', $softwareId)
                        ->lockForUpdate()
                        ->first();

                    if (!$software) {
                        throw new \Exception("Software '{$item['name']}' not found");
                    }

                    if ($software->expiry_flag || $software->expiry_status !== 'active') {
                        throw new \Exception("Software '{$item['name']}' is not active");
                    }

                    $available = $software->quantity - $software->reserved_quantity;
                    
                    if ($available < $item['quantity']) {
                        throw new \Exception(
                            "Insufficient available licenses for '{$item['name']}'. " .
                            "Available: {$available}, Requested: {$item['quantity']}"
                        );
                    }

                    $software->increment('reserved_quantity', $item['quantity']);

                    SoftwareAssignmentItem::create([
                        'assignment_batch_id' => $batch->id,
                        'software_id' => $softwareId,
                        'quantity' => $item['quantity'],
                        'status' => 'Assigned',
                    ]);
                    
                    $assignmentData[] = [
                        'software_id' => $softwareId,
                        'name' => $item['name'],
                        'code' => $item['code'],
                        'license_key' => $item['license_key'],
                        'quantity' => $item['quantity'],
                    ];
                }
            });

            $this->generatedAssignmentNo = $batch->assignment_no;
            $this->sendAssignmentEmail($batch, $user);
            $this->clearCart();
            $this->successMessage = 'Software assigned successfully!';
            $this->showCartModal = false;

            // Generate and return PDF for download
            $batch->load([
                'assignmentItems.software', 
                'user', 
                'assignedByUser',
                'approvedByUser'
            ]);
            
            $approver = $batch->approvedByUser;
            $approvalDate = $batch->approved_at->format('M d, Y H:i');
            
            $pdf = Pdf::loadView('pdf.software-assignment', compact('batch', 'approver', 'approvalDate'));
            
            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                "Software-Assignment-{$this->generatedAssignmentNo}.pdf"
            );

        } catch (\Exception $e) {
            Log::error("Software assignment failed: " . $e->getMessage());
            $this->errorMessage = 'Error: '.$e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    private function sendAssignmentEmail($batch, $user)
    {
        $assignedSoftware = [];
        foreach ($this->selectedForAssignment as $softwareId) {
            if (!isset($this->selectedSoftware[$softwareId])) continue;
            
            $software = $this->selectedSoftware[$softwareId];
            $assignedSoftware[] = [
                'name' => $software['name'],
                'code' => $software['code'],
                'license_key' => $software['license_key'],
                'quantity' => $software['quantity'],
            ];
        }
        
        $approvalDate = now()->format('M d, Y');
        $assignmentNo = $batch->assignment_no;

        $body = EmailTemplates::softwareAssignment(
            $assignmentNo,
            $user->name,
            $assignedSoftware,
            $approvalDate
        );
        
        // Generate PDF content directly
        $batch->load([
            'assignmentItems.software', 
            'user', 
            'assignedByUser',
            'approvedByUser'
        ]);
        
        $approver = $batch->approvedByUser;
        $approvalDate = $batch->approved_at->format('M d, Y H:i');
        
        $pdf = Pdf::loadView('pdf.software-assignment', compact('batch', 'approver', 'approvalDate'));
        $pdfContent = $pdf->output();
        
        $emailService = new SendEmail();
        $emailService->send(
            $user->email,
            "Software Assignment #{$assignmentNo}", 
            $body,
            [], // cc
            $pdfContent, // attachment content
            "Software-Assignment-{$assignmentNo}.pdf", // attachment name
            true // isHtml
        );
    }

    private function generateAssignmentNo()
    {
        $date = now()->format('Ymd');
        $lastNo = SoftwareAssignmentBatch::where('assignment_no', 'like', "ASN-{$date}-%")
            ->orderBy('assignment_no', 'desc')
            ->first();

        $lastNum = $lastNo ? intval(substr($lastNo->assignment_no, -8)) : 0;
        $newNum = str_pad($lastNum + 1, 8, '0', STR_PAD_LEFT);

        return "SW-{$date}-{$newNum}";
    }
}