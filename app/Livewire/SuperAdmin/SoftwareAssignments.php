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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

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
    public $userSearchResults = [];

    public function updatedShowCartModal($value)
    {
        if ($value) {
            $this->selectedForAssignment = array_keys($this->selectedSoftware);
            $this->errorMessage = '';
        } else {
            $this->userSearchResults = [];
        }
    }

    public function render()
    {
        $software = Software::query()
            ->where('expiry_status', '!=', 'expired') 
            ->where('show_status', true) 
            ->whereRaw('(quantity - reserved_quantity) > 0') 
            ->whereNotIn('id', array_keys($this->selectedSoftware)) 
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
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.software-assignments', compact('software'));
    }

    public function addToCart($softwareId)
    {
        $this->errorMessage = '';
        
        $software = Software::find($softwareId);
        $available = $software->quantity - $software->reserved_quantity;
        
        if ($available < 1) {
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
                'max_quantity' => $available,
            ];
        }
    }

    public function updatedUserIdentifier($value)
    {
        if (strlen($value) < 2) {
            $this->userSearchResults = [];
            return;
        }

        $this->userSearchResults = User::query()
            ->where('email', 'like', '%'.$value.'%')
            ->orWhere('name', 'like', '%'.$value.'%')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function selectUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $this->userIdentifier = $user->email;
            $this->userSearchResults = [];
        }
    }


    public function updateCartQuantity($softwareId, $newQuantity)
    {
        $newQuantity = (int)$newQuantity;
        if ($newQuantity < 1 || !isset($this->selectedSoftware[$softwareId])) return;

        $software = Software::find($softwareId);
        $available = $software->quantity - $software->reserved_quantity;

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

    public function clearUserSearch()
    {
        $this->userIdentifier = '';
        $this->userSearchResults = [];
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
            
            DB::transaction(function () use (&$batch, $user) {
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

                    if ($software->expiry_status === 'expired') {
                        throw new \Exception("Software '{$item['name']}' has expired");
                    }

                    $available = $software->quantity - $software->reserved_quantity;
                    
                    if ($available < $item['quantity']) {
                        throw new \Exception(
                            "Insufficient available licenses for '{$item['name']}'. " .
                            "Available: {$available}, Requested: {$item['quantity']}"
                        );
                    }
                    
                    $software->reserved_quantity += $item['quantity'];
                    $software->assign_status = 'Assigned'; 
                    $software->save();

                    SoftwareAssignmentItem::create([
                        'assignment_batch_id' => $batch->id,
                        'software_id' => $softwareId,
                        'quantity' => $item['quantity'],
                        'status' => 'Assigned',
                        'installation_date' => now(), 
                    ]);
                }
            });

            $this->generatedAssignmentNo = $batch->assignment_no;
            $this->sendAssignmentEmail($batch, $user);
            $this->successMessage = 'Software assigned successfully!';
            $this->showCartModal = false;
            $this->clearCart();

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
        
        $assignmentNo = $batch->assignment_no;

        $body = View::make('emails.assign-softwares', [
            'assignmentNo' => $assignmentNo,
            'userName' => $user->name,
            'assignedSoftware' => $assignedSoftware,
            'assignmentDate' => $batch->date_assigned
        ])->render();

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
            [], 
            $pdfContent,
            "Software-Assignment-{$assignmentNo}.pdf",
            true 
        );
    }

    private function generateAssignmentNo()
    {
        $date = now()->format('Ymd');
        $prefix = "SW-{$date}-";
        
        $lastNo = SoftwareAssignmentBatch::where('assignment_no', 'like', $prefix . '%')
            ->orderBy('assignment_no', 'desc')
            ->first();

        $lastNum = 0;
        if ($lastNo) {
            $lastCode = $lastNo->assignment_no;
            $sequencePart = substr($lastCode, -8);
            $lastNum = intval($sequencePart);
        }

        $nextNum = $lastNum + 1;
        $formattedNum = str_pad($nextNum, 8, '0', STR_PAD_LEFT);

        return $prefix . $formattedNum;
    }
}