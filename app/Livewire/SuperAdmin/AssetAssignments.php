<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Asset;
use App\Models\User;
use App\Models\AssetAssignment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class AssetAssignments extends Component
{
    use WithPagination;

    public $search = '';
    public $users;
    public $assets;
    public $admins;
    
    // Assignment fields
    public $assignmentId;
    public $reference_no;
    public $user_id;
    public $admin_id;
    public $asset_id;
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

    protected function loadAvailableAssets()
    {
        // Get assets that are not disposed and are in good condition
        $this->assets = Asset::where('is_disposed', false)
            ->whereHas('condition', function($query) {
                $query->where('condition_name', 'Available'); // Adjust as needed
            })
            ->get();
    }

    public function mount()
    {
        $this->users = User::whereHas('role', function($q) {
            $q->where('name', 'User');
        })->get();

        $this->admins = User::whereHas('role', function($q) {
            $q->whereIn('name', ['Admin', 'Super Admin']);
        })->get();

        $this->loadAvailableAssets(); // Load assets here
        $this->admin_id = auth()->id();
        $this->date_assigned = now()->format('Y-m-d');
    }

    public function updatedAssetId($value)
    {
        $this->selectedAsset = Asset::find($value);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->loadAvailableAssets(); // Reload assets
        $this->showAddModal = true;
    }

    public function openEditModal($id)
    {
        $assignment = AssetAssignment::findOrFail($id);
        
        $this->assignmentId = $assignment->id;
        $this->reference_no = $assignment->reference_no;
        $this->user_id = $assignment->user_id;
        $this->admin_id = $assignment->admin_id;
        $this->asset_id = $assignment->asset_id;
        $this->selectedAsset = $assignment->asset;
        $this->purpose = $assignment->purpose;
        $this->remarks = $assignment->remarks;
        $this->date_assigned = $assignment->date_assigned->format('Y-m-d');
        
        // Load available assets plus the current one
        $this->assets = Asset::where('is_disposed', false)
            ->whereHas('condition', function($query) {
                $query->where('condition_name', 'Good');
            })
            ->orWhere('id', $this->asset_id)
            ->get();

        $this->showEditModal = true;
    }
    
    public function openViewModal($id)
    {
        $this->viewAssignment = AssetAssignment::with(['user', 'admin', 'asset'])
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
            'asset_id', 'purpose', 'remarks'
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
            'asset_id' => 'required|exists:assets,id',
            'purpose' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'date_assigned' => 'required|date',
        ]);

        $assignment = AssetAssignment::create([
            'reference_no' => $this->generateReferenceNo(),
            'user_id' => $this->user_id,
            'admin_id' => $this->admin_id,
            'asset_id' => $this->asset_id,
            'purpose' => $this->purpose,
            'remarks' => $this->remarks,
            'date_assigned' => $this->date_assigned,
        ]);

        $this->successMessage = 'Asset assigned successfully! Generating PDF...';
        $this->closeModals();
        
        // Open PDF in new tab
        $this->dispatch('open-pdf', assignmentId: $assignment->id);
    }

    public function updateAssignment()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'asset_id' => 'required|exists:assets,id',
            'purpose' => 'required|string|max:255',
            'remarks' => 'nullable|string',
            'date_assigned' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $assignment = AssetAssignment::findOrFail($this->assignmentId);
            
            $assignment->update([
                'user_id' => $this->user_id,
                'admin_id' => $this->admin_id,
                'asset_id' => $this->asset_id,
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
        $assignment = AssetAssignment::findOrFail($this->assignmentId);
        
        // Revert asset status
        // Asset::find($assignment->asset_id)->update(['status' => 'available']);
        $goodConditionId = \App\Models\AssetCondition::where('condition_name', 'Available')->firstOrFail()->id;
        Asset::find($assignment->asset_id)->update(['condition_id' => $goodConditionId]);

        
        $assignment->delete();

        $this->successMessage = 'Assignment deleted successfully!';
        $this->closeModals();
    }

    public function clearSuccessMessage()
    {
        $this->reset('successMessage');
    }

    public function render()
    {
        $assignments = AssetAssignment::with(['user', 'admin', 'asset'])
            ->where(function ($query) {
                $query->where('reference_no', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('asset', function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('asset_code', 'like', '%' . $this->search . '%');
                        });

            })
            ->orderBy('date_assigned', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.asset-assignment', [
            'assignments' => $assignments,
        ]);
    }
}
