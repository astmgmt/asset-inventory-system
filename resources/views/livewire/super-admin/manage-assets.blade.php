<div>
    <div class="superadmin-container">
        <h1 class="page-title main-title">Manage Assets</h1>
        
        <!-- Success Message -->
        @if ($successMessage)
            <div class="success-message mb-4" 
                x-data="{ show: true }" 
                x-show="show"
                x-init="setTimeout(() => show = false, 3000)">
                {{ $successMessage }}
            </div>
        @endif

        <!-- Action Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <!-- Search Bar with Clear Button -->
            <div class="relative w-full md:w-1/3">
                <input                
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search assets..."
                    class="w-full p-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                @if($search)
                    <button 
                        wire:click="clearSearch"
                        class="absolute right-3 top-1/2 -translate-y-1/2 transform text-gray-400 hover:text-gray-600"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                @else
                    <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 transform text-gray-400 pointer-events-none"></i>
                @endif
            </div>
            
            <!-- Add Button -->
            <div class="flex justify-end mb-4">
                <button wire:click="openAddModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md shadow-sm inline-flex items-center transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i> Add New Asset
                </button>
            </div>
        </div>

        <!-- ... (table content remains the same) ... -->
        <table class="user-table">           
            <!-- Table Headers (Visible on Desktop) -->
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Condition</th>
                    <th>Warranty</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
                @forelse($assets as $asset)
                    <tr>
                        <!-- Added data attributes for mobile labels -->
                        <td data-label="Asset Code">{{ $asset->asset_code }}</td>
                        <td data-label="Name">{{ $asset->name }}</td>
                        <td data-label="Name">{{ $asset->model_number }}</td>
                        <td data-label="Category">{{ $asset->category->category_name }}</td>
                        <td data-label="Quantity">{{ $asset->quantity }}</td>
                        <td data-label="Condition">
                            @php
                                $conditionName = strtolower($asset->condition->condition_name);
                                $conditionClass = match($conditionName) {
                                    'new' => 'bg-blue-100 text-blue-800',        
                                    'borrowed' => 'bg-indigo-100 text-indigo-800', 
                                    'available' => 'bg-green-100 text-green-800',  
                                    'defective' => 'bg-red-100 text-red-800',        
                                    'disposed' => 'bg-yellow-100 text-yellow-800', 
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $displayCondition = ucfirst($conditionName);
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $conditionClass }} w-[80px] justify-center">
                                {{ $displayCondition }}
                            </span>
                        </td>

                        <td data-label="Warranty">
                            {{ $asset->warranty_expiration->format('M d, Y') }}
                           
                        </td>
                        <td data-label="Actions" class="text-center">
                            <div class="flex justify-center gap-3">
                                <button wire:click="openViewModal({{ $asset->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button wire:click="openEditModal({{ $asset->id }})" class="text-yellow-500 hover:text-yellow-600 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $asset->id }})" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No assets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>


        <!-- Add Asset Modal -->
        @if ($showAddModal)
            <div class="modal-backdrop">
                <div class="modal">
                    <h2 class="modal-title">Add New Asset</h2>
                    
                    <form wire:submit.prevent="createAsset">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Brand *</label>
                                <input type="text" wire:model="name" class="form-input">
                                @error('name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Quantity *</label>
                                <input type="number" wire:model="quantity" min="1" class="form-input">
                                @error('quantity') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Model Number (Live Search) -->
                            <div class="form-group">
                                <label>Model *</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        wire:model.live="modelInput"
                                        placeholder="Search or enter model number"
                                        class="form-input"
                                    />
                                    @if($showModelDropdown)
                                        <div class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto">
                                            @foreach($modelSuggestions as $model)
                                                <div 
                                                    wire:click="selectModelNumber('{{ $model }}')"
                                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                                >
                                                    {{ $model }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @error('model_number') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Category (Live Search) -->
                            <div class="form-group">
                                <label>Category *</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        wire:model.live="categorySearch"
                                        placeholder="Search or add category"
                                        class="form-input"
                                    />
                                    @if($showCategoryDropdown)
                                        <div class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto">
                                            @foreach($categorySuggestions as $category)
                                                <div 
                                                    wire:click="selectCategory('{{ $category }}')"
                                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                                >
                                                    {{ $category }}
                                                </div>
                                            @endforeach
                                            @if(!in_array($categorySearch, $categorySuggestions))
                                                <div 
                                                    wire:click="selectCategory('{{ $categorySearch }}')"
                                                    class="px-4 py-2 text-blue-500 hover:bg-blue-100 cursor-pointer"
                                                >
                                                    Add "{{ $categorySearch }}"
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @error('category_id') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Condition (Fixed to "New") -->
                            <div class="form-group">
                                <label>Condition *</label>
                                <div class="form-input">
                                    New
                                </div>
                                <input type="hidden" wire:model="condition_id">
                            </div>
                            
                            <!-- Location (Live Search) -->
                            <div class="form-group">
                                <label>Location *</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        wire:model.live="locationSearch"
                                        placeholder="Search or add location"
                                        class="form-input"
                                    />
                                    @if($showLocationDropdown)
                                        <div class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto">
                                            @foreach($locationSuggestions as $location)
                                                <div 
                                                    wire:click="selectLocation('{{ $location }}')"
                                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                                >
                                                    {{ $location }}
                                                </div>
                                            @endforeach
                                            @if(!in_array($locationSearch, $locationSuggestions))
                                                <div 
                                                    wire:click="selectLocation('{{ $locationSearch }}')"
                                                    class="px-4 py-2 text-blue-500 hover:bg-blue-100 cursor-pointer"
                                                >
                                                    Add "{{ $locationSearch }}"
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @error('location_id') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Vendor (Live Search) -->
                            <div class="form-group">
                                <label>Vendor/Supplier *</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        wire:model.live="vendorSearch"
                                        placeholder="Search or add vendor"
                                        class="form-input"
                                    />
                                    @if($showVendorDropdown)
                                        <div class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto">
                                            @foreach($vendorSuggestions as $vendor)
                                                <div 
                                                    wire:click="selectVendor('{{ $vendor }}')"
                                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                                >
                                                    {{ $vendor }}
                                                </div>
                                            @endforeach
                                            @if(!in_array($vendorSearch, $vendorSuggestions))
                                                <div 
                                                    wire:click="selectVendor('{{ $vendorSearch }}')"
                                                    class="px-4 py-2 text-blue-500 hover:bg-blue-100 cursor-pointer"
                                                >
                                                    Add "{{ $vendorSearch }}"
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @error('vendor_id') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Warranty Expiration *</label>
                                <input 
                                    type="date" 
                                    wire:model="warranty_expiration" 
                                    class="form-input"
                                    min="{{ now()->format('Y-m-d') }}"
                                >
                                @error('warranty_expiration') <span class="error">{{ $message }}</span> @enderror
                            </div>                         
                           
                            <!-- Serial Number and Description in a single row -->
                            <div class="flex flex-col md:flex-row gap-4 col-span-2">
                                <div class="w-full md:w-1/2">
                                    <div class="form-group">
                                        <label>Serial Number</label>
                                        <input 
                                            type="text" 
                                            wire:model="serial_number" 
                                            class="form-input"
                                            placeholder="Enter serial number"
                                        >
                                        @error('serial_number') 
                                            <span class="error">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="w-full md:w-1/2">
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea 
                                            wire:model="description" 
                                            rows="3" 
                                            class="form-input w-full"
                                        ></textarea>
                                        @error('description') 
                                            <span class="error">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                        
                        <div class="modal-actions">
                            <button type="submit" class="btn btn-primary btn-update">
                                <i class="fas fa-plus-circle"></i> Create Asset
                            </button>
                            <button type="button" wire:click="closeModals" class="btn btn-secondary">
                                <i class="fas fa-ban"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Edit Asset Modal -->
        @if ($showEditModal)
            <div class="modal-backdrop">
                <div class="modal">
                    <h2 class="modal-title">Edit Asset</h2>
                    
                    <form wire:submit.prevent="updateAsset">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Brand *</label>
                                <input type="text" wire:model="name" class="form-input">
                                @error('name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- REMOVED QUANTITY HERE -->
                            
                            <div class="form-group">
                                <label>Model *</label>
                                <input type="text" wire:model="model_number" class="form-input">
                                @error('model_number') <span class="error">{{ $message }}</span> @enderror
                            </div>

                            <!-- Add Serial Number field -->
                            <div class="form-group">
                                <label>Serial Number</label>
                                <input 
                                    type="text" 
                                    wire:model="serial_number" 
                                    class="form-input"
                                    placeholder="Enter serial number"
                                >
                                @error('serial_number') 
                                    <span class="error">{{ $message }}</span> 
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Category *</label>
                                <select wire:model="category_id" class="form-input">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Condition *</label>
                                @php
                                    $borrowedCondition = $conditions->firstWhere('condition_name', 'Borrowed');
                                    $isBorrowed = $borrowedCondition && $originalConditionId == $borrowedCondition->id;
                                    
                                    $currentCondition = $conditions->firstWhere('id', $condition_id);
                                @endphp

                                <select 
                                    wire:model="condition_id" 
                                    class="form-input"
                                    @if($isBorrowed) disabled @endif
                                >
                                    @if($isBorrowed)
                                        <option value="{{ $borrowedCondition->id }}" selected>
                                            Borrowed (cannot be changed)
                                        </option>
                                    @else
                                        <option value="{{ $condition_id }}" selected>
                                            {{ $currentCondition->condition_name ?? 'Current Condition' }}
                                        </option>
                                        
                                        @foreach($conditions as $condition)
                                            @if(
                                                $condition->id !== $condition_id &&
                                                $condition->condition_name !== 'Disposed' &&
                                                $condition->condition_name !== 'Borrowed'
                                            )
                                                <option value="{{ $condition->id }}">
                                                    {{ $condition->condition_name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                                
                                @error('condition_id') <span class="error">{{ $message }}</span> @enderror
                                
                                @if($isBorrowed)
                                    <p class="text-xs text-red-600 mt-1">
                                        * Condition cannot be changed for borrowed assets
                                    </p>
                                @endif
                            </div>
                            
                            <div class="form-group">
                                <label>Location *</label>
                                <select wire:model="location_id" class="form-input">
                                    <option value="">Select Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->location_name }}</option>
                                    @endforeach
                                </select>
                                @error('location_id') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Vendor/Supplier *</label>
                                <select wire:model="vendor_id" class="form-input">
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>
                                    @endforeach
                                </select>
                                @error('vendor_id') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="form-group">
                                <label>Warranty Expiration *</label>
                                <input 
                                    type="date" 
                                    wire:model="warranty_expiration" 
                                    class="form-input"
                                    min="{{ now()->format('Y-m-d') }}"
                                >
                                @error('warranty_expiration') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="form-group col-span-2">
                                <label>Description</label>
                                <textarea wire:model="description" rows="3" class="form-input"></textarea>
                                @error('description') <span class="error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="modal-actions">
                            <button type="submit" class="btn btn-primary btn-update">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <button type="button" wire:click="closeModals" class="btn btn-secondary btn-cancel">
                                <i class="fas fa-times-circle"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        
        @if ($showViewModal)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-gray-50 rounded-lg shadow-2xl w-full max-w-4xl p-8 text-gray-900 transition-colors duration-300 max-h-[90vh] overflow-y-auto">
                    <h2 class="text-gray-700 text-2xl font-semibold mb-8 border-b border-gray-300 pb-4">
                        Asset Details: {{ $viewAsset->name ?? '' }}
                    </h2>

                    @if($viewAsset)
                        @php
                            // Compute disabled state for print button
                            $isPrintDisabled = in_array($viewAsset->condition->condition_name, ['Disposed', 'Defective']);
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
                            <!-- Asset Code -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Asset Code</label>
                                <p class="text-lg">{{ $viewAsset->asset_code }}</p>
                            </div>

                            <!-- Serial Number -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Serial Number</label>
                                <p class="text-lg">{{ $viewAsset->serial_number ?? 'None' }}</p>
                            </div>

                            <!-- Brand -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Brand</label>
                                <p class="text-lg">{{ $viewAsset->name }}</p>
                            </div>

                            <!-- Model -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Model</label>
                                <p class="text-lg">{{ $viewAsset->model_number }}</p>
                            </div>

                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Description</label>
                                <p class="text-lg">{{ $viewAsset->description }}</p>
                            </div>

                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Quantity</label>
                                <p class="text-lg">{{ $viewAsset->quantity }}</p>
                            </div>

                            <!-- Category -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Category</label>
                                <p class="text-lg">{{ $viewAsset->category->category_name }}</p>
                            </div>

                            <!-- Condition -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Condition</label>
                                <p class="text-lg">{{ $viewAsset->condition->condition_name }}</p>
                            </div>

                            <!-- Location -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Location</label>
                                <p class="text-lg">{{ $viewAsset->location->location_name }}</p>
                            </div>

                            <!-- Vendor/Supplier -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Vendor/Supplier</label>
                                <p class="text-lg">{{ $viewAsset->vendor->vendor_name }}</p>
                            </div>

                            <!-- Warranty Expiration -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Warranty Expiration</label>
                                <p class="text-lg">{{ $viewAsset->warranty_expiration->format('M d, Y') }}</p>
                                @if ($viewAsset->expiry_status === 'expired' && $viewAsset->show_status === 0)
                                    <p class="text-red-500 font-medium">Expired</p>
                                @endif
                            </div>

                            <!-- Status -->
                            <div class="asset-details p-4 rounded-md shadow-sm">
                                <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Status</label>
                                <p class="text-lg">
                                    @if($viewAsset->condition->condition_name === 'Disposed' && $viewAsset->is_disposed)
                                        <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide">Disposed</span>
                                    @else
                                        @php
                                            $conditionName = $viewAsset->condition->condition_name;
                                            $conditionClass = match(strtolower($conditionName)) {
                                                'defective' => 'bg-red-100 text-red-800',
                                                'new' => 'bg-blue-100 text-blue-800',
                                                'available' => 'bg-green-100 text-green-800',
                                                'borrowed' => 'bg-indigo-100 text-indigo-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="inline-block {{ $conditionClass }} text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide">
                                            {{ $conditionName }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Footer -->
                    <div class="mt-10 flex justify-end space-x-4">
                        <a href="{{ $isPrintDisabled ? '#' : route('assets.pdf', ['id' => $viewAsset->id]) }}"
                        @if(!$isPrintDisabled) target="_blank" @endif
                        class="inline-flex items-center px-6 py-3 text-sm font-medium rounded-md transition 
                                {{ $isPrintDisabled 
                                        ? 'bg-gray-400 text-gray-200 cursor-not-allowed' 
                                        : 'bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' }}"
                        @if($isPrintDisabled) onclick="return false;" @endif>
                            <i class="fas fa-print mr-2"></i> Print
                        </a>

                        <!-- Close Button -->
                        <button type="button" wire:click="closeModals"
                                class="inline-flex items-center px-6 py-3 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                            <i class="fas fa-xmark mr-2"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Delete Confirmation Modal -->
        @if ($showDeleteModal)
            <div class="modal-backdrop">
                
                <div class="modal modal-delete">
                    <h2 class="text-lg font-semibold mb-2 text-red-700">
                        Confirm Deletion
                    </h2>
                    <div class="bg-danger border-l-4 border-red-500 p-3 sm:p-4 rounded-md shadow-sm mb-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-0.5">
                                <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 text-sm text-red-800 leading-tight">
                                <p>Are you sure you want to delete this asset? This action cannot be undone.</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions flex space-x-3">
                        <button
                            wire:click="deleteAsset"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                        >
                            <span wire:loading.class.add="hidden" class="flex items-center">
                                <i class="fas fa-trash-alt mr-2"></i>
                                Confirm
                            </span>
                            <span wire:loading.class.remove="hidden" class="hidden flex items-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Deleting...
                            </span>
                        </button>

                        <button
                            wire:click="closeModals"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition"
                        >
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                    </div>


                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('clear-message', () => {
                setTimeout(() => {
                    @this.set('successMessage', '');
                }, 3000);
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.relative')) {
                    @this.set('showModelDropdown', false);
                    @this.set('showCategoryDropdown', false);
                    @this.set('showLocationDropdown', false);
                    @this.set('showVendorDropdown', false);
                }
            });

            // Handle single asset PDF opening - FIXED
            Livewire.on('open-asset-pdf', (event) => {
                // Extract ID correctly from the event object
                const id = event.id ?? event;
                if (!id || isNaN(id)) {
                    console.error('Invalid asset ID:', id);
                    return;
                }
                
                const url = "{{ route('assets.pdf', ['id' => ':id']) }}".replace(':id', id);
                window.open(url, '_blank');
            });
        });
    </script>
</div>