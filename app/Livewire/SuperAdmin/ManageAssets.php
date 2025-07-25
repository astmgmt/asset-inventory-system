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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;


class ManageAssets extends Component
{
    use WithPagination;

    public $search = '';
    public $categories;
    public $conditions;
    public $locations;
    public $vendors;
    
    public $assetId;
    public $name;
    public $description;
    public $quantity = 1;
    public $model_number;
    public $serial_number;
    public $category_id;
    public $condition_id;
    public $location_id;
    public $vendor_id;
    public $warranty_expiration;
    public $date_acquired;
    
    public $modelInput = '';
    public $modelSuggestions = [];
    public $categorySearch = '';
    public $categorySuggestions = [];
    public $locationSearch = '';
    public $locationSuggestions = [];
    public $vendorSearch = '';
    public $vendorSuggestions = [];
    
    public $showModelDropdown = false;
    public $showCategoryDropdown = false;
    public $showLocationDropdown = false;
    public $showVendorDropdown = false;
    
    public $showAddModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    
    public $viewAsset;

    public $originalConditionId;
    
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->categories = AssetCategory::all();
        $this->conditions = AssetCondition::all(); 
        $this->locations = AssetLocation::all();
        $this->vendors = Vendor::all();
        
        $newCondition = AssetCondition::where('condition_name', 'New')->first();
        $this->condition_id = $newCondition->id ?? null;
        
        $this->warranty_expiration = now()->addYear()->format('Y-m-d');
        $this->date_acquired = now()->format('Y-m-d');      
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function updatedModelInput()
    {
        $this->showModelDropdown = !empty($this->modelInput);
        
        if (empty($this->modelInput)) {
            $this->modelSuggestions = [];
            return;
        }
        
        $this->modelSuggestions = Asset::where('model_number', 'like', '%'.$this->modelInput.'%')
            ->distinct('model_number')
            ->pluck('model_number')
            ->take(5)
            ->toArray();
    }

    public function updatedCategorySearch()
    {
        $value = $this->categorySearch;
        $this->showCategoryDropdown = !empty($value);
        
        if (empty($value)) {
            $this->categorySuggestions = [];
            return;
        }
        
        $this->categorySuggestions = AssetCategory::where('category_name', 'like', '%'.$value.'%')
            ->pluck('category_name')
            ->take(5)
            ->toArray();
    }

    public function updatedLocationSearch()
    {
        $value = $this->locationSearch;
        $this->showLocationDropdown = !empty($value);
        
        if (empty($value)) {
            $this->locationSuggestions = [];
            return;
        }
        
        $this->locationSuggestions = AssetLocation::where('location_name', 'like', '%'.$value.'%')
            ->pluck('location_name')
            ->take(5)
            ->toArray();
    }

    public function updatedVendorSearch()
    {
        $value = $this->vendorSearch;
        $this->showVendorDropdown = !empty($value);
        
        if (empty($value)) {
            $this->vendorSuggestions = [];
            return;
        }
        
        $this->vendorSuggestions = Vendor::where('vendor_name', 'like', '%'.$value.'%')
            ->pluck('vendor_name')
            ->take(5)
            ->toArray();
    }

    public function selectModelNumber($model)
    {
        $this->modelInput = $model;
        $this->model_number = $model;
        $this->modelSuggestions = [];
        $this->showModelDropdown = false;
    }

    public function selectCategory($categoryName)
    {
        $category = AssetCategory::firstOrCreate(['category_name' => $categoryName]);
        $this->category_id = $category->id;
        $this->categorySearch = $category->category_name;
        $this->categorySuggestions = [];
        $this->showCategoryDropdown = false;
    }

    public function selectLocation($locationName)
    {
        $location = AssetLocation::firstOrCreate(['location_name' => $locationName]);
        $this->location_id = $location->id;
        $this->locationSearch = $location->location_name;
        $this->locationSuggestions = [];
        $this->showLocationDropdown = false;
    }

    public function selectVendor($vendorName)
    {
        $vendor = Vendor::firstOrCreate(['vendor_name' => $vendorName]);
        $this->vendor_id = $vendor->id;
        $this->vendorSearch = $vendor->vendor_name;
        $this->vendorSuggestions = [];
        $this->showVendorDropdown = false;
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
        $this->modelInput = $asset->model_number;
        $this->serial_number = $asset->serial_number;
        
        $this->category_id = $asset->category_id;
        $this->categorySearch = $asset->category->category_name ?? '';
        
        $this->condition_id = $asset->condition_id;
        $this->originalConditionId = $asset->condition_id;
        
        $this->location_id = $asset->location_id;
        $this->locationSearch = $asset->location->location_name ?? '';
        
        $this->vendor_id = $asset->vendor_id;
        $this->vendorSearch = $asset->vendor->vendor_name ?? '';
        
        $this->date_acquired = $asset->date_acquired->format('Y-m-d');
        $this->warranty_expiration = $asset->warranty_expiration->format('Y-m-d');
        

        $this->showEditModal = true;
    }    

    public function openViewModal($id)
    {
        $this->viewAsset = Asset::with(['category', 'condition', 'location', 'vendor'])
            ->findOrFail($id)
            ->fresh();             
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
            'model_number', 'serial_number', 'modelInput', 'category_id', 'condition_id', 
            'location_id', 'vendor_id', 'warranty_expiration', 'date_acquired',
            'modelSuggestions', 'categorySearch', 'categorySuggestions',
            'locationSearch', 'locationSuggestions', 'vendorSearch', 'vendorSuggestions',
            'showModelDropdown', 'showCategoryDropdown', 'showLocationDropdown', 'showVendorDropdown',
            'originalConditionId'
        ]);
        $this->resetErrorBag();
        $this->viewAsset = null;

        $this->conditions = AssetCondition::all(); 
        
        $newCondition = AssetCondition::where('condition_name', 'New')->first();
        $this->condition_id = $newCondition->id ?? null;
        
        $this->warranty_expiration = now()->addYear()->format('Y-m-d');
        $this->date_acquired = now()->format('Y-m-d'); 
    }

    private function generateAssetCode($lastNum = null)
    {
        $date = now()->format('mdY');
        
        if ($lastNum === null) {
            $lastAsset = Asset::withTrashed()
                ->where('asset_code', 'like', "AST-{$date}-%")
                ->orderBy('asset_code', 'desc')
                ->first();
                
            $lastNum = $lastAsset ? intval(substr($lastAsset->asset_code, -8)) : 0;
        }
        
        $newNum = $lastNum + 1;
        $formattedNum = str_pad($newNum, 8, '0', STR_PAD_LEFT);
        
        return [
            'code' => "AST-{$date}-{$formattedNum}",
            'nextNum' => $newNum
        ];
    }

    public function createAsset()
    {
        $this->model_number = $this->modelInput;
        
        $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'serial_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('assets', 'serial_number')->ignore($this->assetId),
            ],
            'quantity' => 'required|integer|min:1|max:100',
            'model_number' => 'required|string|max:50',
            'category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'required|exists:asset_locations,id',
            'vendor_id' => 'required|exists:vendors,id',
            'warranty_expiration' => 'required|date',
            'date_acquired' => 'nullable|date',
        ]);

        try {            

            $insertedIds = [];
            $assetsData = [];
            
            DB::transaction(function () use (&$insertedIds, &$assetsData) {
                $lastAsset = Asset::orderBy('id', 'desc')->first();
                $lastId = $lastAsset ? $lastAsset->id : 0;
                $lastAssetCode = Asset::withTrashed() 
                    ->where('asset_code', 'like', 'AST-%')
                    ->orderBy('asset_code', 'desc')
                    ->first();
                    
                $lastNum = $lastAssetCode ? intval(substr($lastAssetCode->asset_code, -8)) : 0;
                
                $assets = [];
                $currentDate = now()->format('mdY');
                $assetCodes = [];
                
                for ($i = 0; $i < $this->quantity; $i++) {
                    $newNum = $lastNum + 1;
                    $formattedNum = str_pad($newNum, 8, '0', STR_PAD_LEFT);
                    $assetCode = "AST-{$currentDate}-{$formattedNum}";
                    $lastNum = $newNum;
                    
                    $serialNumber = ($i === 0) ? $this->serial_number : null;

                    $assets[] = [
                        'asset_code' => $assetCode,
                        'serial_number' => $serialNumber,
                        'name' => $this->name,
                        'description' => $this->description,
                        'quantity' => 1, 
                        'model_number' => $this->model_number,
                        'category_id' => $this->category_id,
                        'condition_id' => $this->condition_id,
                        'location_id' => $this->location_id,
                        'vendor_id' => $this->vendor_id,
                        'warranty_expiration' => $this->warranty_expiration,
                        'date_acquired' => $this->date_acquired,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    $assetCodes[] = $assetCode;
                }

                Asset::insert($assets);
                
                $insertedAssets = Asset::with(['category', 'condition', 'location', 'vendor'])
                    ->whereIn('asset_code', $assetCodes)
                    ->orderBy('id')
                    ->get();
                    
                $insertedIds = $insertedAssets->pluck('id')->toArray();
                $assetsData = $insertedAssets->toArray();
            });

            $this->closeModals();
            $this->successMessage = $this->quantity > 1 
                ? "{$this->quantity} assets created successfully!" 
                : "Asset created successfully!";            
            
            $this->dispatch('refresh-serial-count');
            $this->dispatch('clear-message');
            
        } catch (\Exception $e) {
            $this->errorMessage = "Error creating asset: " . $e->getMessage();
            $this->dispatch('clear-message');
        }
    }

    public function updateAsset()
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'serial_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('assets', 'serial_number')->ignore($this->assetId),
            ],
            'model_number' => 'required|string|max:50',
            'category_id' => 'required|exists:asset_categories,id',
            'condition_id' => 'required|exists:asset_conditions,id',
            'location_id' => 'required|exists:asset_locations,id',
            'vendor_id' => 'required|exists:vendors,id',
            'warranty_expiration' => 'required|date',
            'date_acquired' => 'nullable|date',
        ], [
            'serial_number.unique' => 'This serial number is already in use by another asset.',
        ]);

        try {          

            $asset = Asset::findOrFail($this->assetId);
            $newCondition = AssetCondition::find($this->condition_id);
            $isDisposed = $newCondition->condition_name === 'Disposed';
            
            $warrantyExpired = Carbon::parse($this->warranty_expiration)->isPast();
            $expiredCondition = AssetCondition::where('condition_name', 'Expired')->first();
            
            if ($warrantyExpired && $newCondition->condition_name !== 'Expired' && $expiredCondition) {
                $this->condition_id = $expiredCondition->id;
                $newCondition = $expiredCondition;
            }

            $asset->update([
                'name' => $this->name,
                'description' => $this->description,
                'serial_number' => $this->serial_number,
                'model_number' => $this->model_number,
                'category_id' => $this->category_id,
                'condition_id' => $this->condition_id,
                'location_id' => $this->location_id,
                'vendor_id' => $this->vendor_id,
                'warranty_expiration' => $this->warranty_expiration,
                'date_acquired' => $this->date_acquired,
                'is_disposed' => $isDisposed,
                'expiry_status' => $warrantyExpired ? 'expired' : 'active',
                'show_status' => $warrantyExpired ? $asset->show_status : 1,
            ]);

            $this->successMessage = 'Asset updated successfully!';
            $this->closeModals();
            $this->dispatch('refresh-serial-count'); 
            $this->dispatch('clear-message');

        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to update asset: ' . $e->getMessage();
            $this->dispatch('clear-message');
        }
    }

    public function deleteAsset()
    {
        try {
            $asset = Asset::findOrFail($this->assetId);
            $asset->delete();
            
            $this->successMessage = 'Asset deleted successfully!';
            $this->closeModals();
            $this->dispatch('refresh-serial-count');
            $this->dispatch('clear-message');
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to delete asset: ' . $e->getMessage();
            $this->dispatch('clear-message');
        }
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
                      })
                      ->orWhereHas('condition', function ($q) {
                          $q->where('condition_name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'assetsPage');

        return view('livewire.super-admin.manage-assets', [
            'assets' => $assets,
        ]);
    }
}