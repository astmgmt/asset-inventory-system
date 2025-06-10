<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Software;
use App\Models\User;
use App\Models\SoftwareAssignment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class SoftwareAssignments extends Component
{
    use WithPagination;

    public $search = '';
    public $users;
    public $softwareList;
    public $admins;
    
    // Assignment fields
    public $assignmentId;
    public $reference_no;
    public $user_id;
    public $admin_id;
    public $software_id;
    public $purpose;
    public $remarks;
    public $date_assigned;
    
    // Modals
    public $showAddModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    
    // View assignment
    public $viewAssignment;
    
    // Messages
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->users = User::whereHas('role', function($q) {
            $q->where('name', 'User');
        })->get();

        $this->admins = User::whereHas('role', function($q) {
            $q->whereIn('name', ['Admin', 'Super Admin']);
        })->get();

        $this->softwareList = Software::all();
        $this->admin_id = auth()->id();
        $this->date_assigned = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function openEditModal($id)
    {
        $assignment = SoftwareAssignment::findOrFail($id);
        
        $this->assignmentId = $assignment->id;
        $this->reference_no = $assignment->reference_no;
        $this->user_id = $assignment->user_id;
        $this->admin_id = $assignment->admin_id;
        $this->software_id = $assignment->software_id;
        $this->purpose = $assignment->purpose;
        $this->remarks = $assignment->remarks;
        $this->date_assigned = $assignment->date_assigned->format('Y-m-d');
        
        $this->showEditModal = true;
    }
    
    public function openViewModal($id)
    {
        $this->viewAssignment = SoftwareAssignment::with(['user', 'admin', 'software'])
            ->findOrFail($id);
        $this->showViewModal = true;
    }

    public function confirmDelete($id)
    {
        $this->assignmentId = $id;
        $this->showDeleteModal = true;
    }

    public function closeModals()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'assignmentId', 'reference_no', 'user_id', 'admin_id', 
            'software_id', 'purpose', 'remarks'
        ]);
        $this->resetErrorBag();
        $this->viewAssignment = null;
        $this->date_assigned = now()->format('Y-m-d');
        $this->admin_id = auth()->id();
    }

    private function generateReferenceNo()
    {
        return implode('-', [
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4))
        ]);
    }

    public function createAssignment()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'software_id' => 'required|exists:software,id',
            'purpose' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'date_assigned' => 'required|date',
        ]);

        $assignment = SoftwareAssignment::create([
            'reference_no' => $this->generateReferenceNo(),
            'user_id' => $this->user_id,
            'admin_id' => $this->admin_id,
            'software_id' => $this->software_id,
            'purpose' => $this->purpose,
            'remarks' => $this->remarks,
            'date_assigned' => $this->date_assigned,
        ]);

        $this->successMessage = 'Software assigned successfully! Generating PDF...';
        $this->closeModals();
        
        // Open PDF in new tab
        $this->dispatch('open-software-pdf', assignmentId: $assignment->id);
    }

    public function updateAssignment()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'software_id' => 'required|exists:software,id',
            'purpose' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'date_assigned' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $assignment = SoftwareAssignment::findOrFail($this->assignmentId);
            
            $assignment->update([
                'user_id' => $this->user_id,
                'admin_id' => $this->admin_id,
                'software_id' => $this->software_id,
                'purpose' => $this->purpose,
                'remarks' => $this->remarks,
                'date_assigned' => $this->date_assigned,
            ]);

            DB::commit();

            $this->successMessage = 'Assignment updated successfully!';
            $this->closeModals();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error updating assignment: ' . $e->getMessage();
        }
    }

    public function deleteAssignment()
    {
        $assignment = SoftwareAssignment::findOrFail($this->assignmentId);
        $assignment->delete();

        $this->successMessage = 'Assignment deleted successfully!';
        $this->closeModals();
    }

    public function render()
    {
        $assignments = SoftwareAssignment::with(['user.role', 'admin.role', 'software'])
            ->where(function ($query) {
                $query->where('reference_no', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('admin', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                        ->whereHas('role', function ($roleQuery) {
                            $roleQuery->whereIn('name', ['Admin', 'Super Admin']);
                        });
                    })
                    ->orWhereHas('software', function ($q) {
                        $q->where('software_name', 'like', '%' . $this->search . '%')
                        ->orWhere('software_code', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereRaw("DATE_FORMAT(date_assigned, '%M %d, %Y') LIKE ?", ['%' . $this->search . '%']);
            })
            ->orderBy('date_assigned', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.software-assignments', [
            'assignments' => $assignments,
        ]);
    }
}