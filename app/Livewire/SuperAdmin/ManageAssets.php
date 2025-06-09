<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetCondition;
use App\Models\AssetLocation;
use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class ManageAssets extends Component
{
    use WithPagination;

    public $search = '';
    public $categories;
    public $conditions;
    public $locations;
    public $vendors;
    
    // Asset fields
    public $assetId;
    public $name;
    public $description;
    public $quantity;
    public $model_number;
    public $category_id;
    public $condition_id;
    public $location_id;
    public $vendor_id;
    public $warranty_expiration;
    
    // Modals
    public $showAddModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    
    // View asset
    public $viewAsset;
    
    // Messages
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->categories = AssetCategory::all();
        $this->conditions = AssetCondition::all();
        $this->locations = AssetLocation::all();
        $this->vendors = Vendor::all();
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
        $asset = Asset::findOrFail($id);
        
        $this->assetId = $asset->id;
        $this->name = $asset->name;
        $this->description = $asset->description;
        $this->quantity = $asset->quantity;
        $this->model_number = $asset->model_number;
        $this->category_id = $asset->category_id;
        $this->condition_id = $asset->condition_id;
        $this->location_id = $asset->location_id;
        $this->vendor_id = $asset->vendor_id;
        $this->warranty_expiration = $asset->warranty_expiration->format('Y-m-d');
        
        $this->showEditModal = true;
    }
    
    public function openViewModal($id)
    {
        $this->viewAsset = Asset::with(['category', 'condition', 'location', 'vendor'])
            ->findOrFail($id);
        $this->showViewModal = true;
    }

    public function confirmDelete($id)
    {
        $this->assetId = $id;
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
            'assetId', 'name', 'description', 'quantity', 
            'model_number', 'category_id', 'condition_id', 
            'location_id', 'vendor_id', 'warranty_expiration'
        ]);
        $this->resetErrorBag();
        $this->viewAsset = null;
    }

    private function generateAssetCode()
    {
        return implode('-', [
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4))
        ]);
    }

    private function generateSerialNumber()
    {
        $lastAsset = Asset::orderBy('id', 'desc')->first();
        $lastId = $lastAsset ? $lastAsset->id : 0;
        return str_pad($lastId + 1, 16, '0', STR_PAD_LEFT);
    }

    public function createAsset()
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'model_number' => 'required|string|max:50',
            'category_id' => 'required|exists:asset_categories,id',
            'condition_id' => 'required|exists:asset_conditions,id',
            'location_id' => 'required|exists:asset_locations,id',
            'vendor_id' => 'required|exists:vendors,id',
            'warranty_expiration' => 'required|date',
        ]);

        Asset::create([
            'asset_code' => $this->generateAssetCode(),
            'serial_number' => $this->generateSerialNumber(),
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'model_number' => $this->model_number,
            'category_id' => $this->category_id,
            'condition_id' => $this->condition_id,
            'location_id' => $this->location_id,
            'vendor_id' => $this->vendor_id,
            'warranty_expiration' => $this->warranty_expiration,
        ]);

        $this->successMessage = 'Asset created successfully!';
        $this->closeModals();
        $this->dispatch('clear-message');
    }

    public function updateAsset()
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'model_number' => 'required|string|max:50',
            'category_id' => 'required|exists:asset_categories,id',
            'condition_id' => 'required|exists:asset_conditions,id',
            'location_id' => 'required|exists:asset_locations,id',
            'vendor_id' => 'required|exists:vendors,id',
            'warranty_expiration' => 'required|date',
        ]);

        $asset = Asset::findOrFail($this->assetId);
        $asset->update([
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'model_number' => $this->model_number,
            'category_id' => $this->category_id,
            'condition_id' => $this->condition_id,
            'location_id' => $this->location_id,
            'vendor_id' => $this->vendor_id,
            'warranty_expiration' => $this->warranty_expiration,
        ]);

        $this->successMessage = 'Asset updated successfully!';
        $this->closeModals();
        $this->dispatch('clear-message');
    }

    public function deleteAsset()
    {
        $asset = Asset::findOrFail($this->assetId);
        $asset->delete();
        
        $this->successMessage = 'Asset deleted successfully!';
        $this->closeModals();
        $this->dispatch('clear-message');
    }

    public function clearSuccessMessage()
    {
        $this->dispatch('clear-message');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $assets = Asset::with(['category', 'condition', 'location', 'vendor'])
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('asset_code', 'like', '%' . $this->search . '%')
                      ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                      ->orWhere('model_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('category', function ($q) {
                          $q->where('category_name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('vendor', function ($q) {
                          $q->where('vendor_name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.manage-assets', [
            'assets' => $assets,
        ]);
    }
}