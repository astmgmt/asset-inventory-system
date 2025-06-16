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
                    <th>Name</th>
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
                                <label>Name *</label>
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
                                <label>Model Number *</label>
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
                                <div class="form-input bg-gray-100">
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
                                <label>Vendor *</label>
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
                            
                            <div class="form-group col-span-2">
                                <label>Description</label>
                                <textarea wire:model="description" rows="3" class="form-input"></textarea>
                                @error('description') <span class="error">{{ $message }}</span> @enderror
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
                                <label>Name *</label>
                                <input type="text" wire:model="name" class="form-input">
                                @error('name') <span class="error">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- REMOVED QUANTITY HERE -->
                            
                            <div class="form-group">
                                <label>Model Number *</label>
                                <input type="text" wire:model="model_number" class="form-input">
                                @error('model_number') <span class="error">{{ $message }}</span> @enderror
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
                                <select wire:model="condition_id" class="form-input">
                                    <option value="">Select Condition</option>
                                    @foreach($conditions as $condition)
                                        <option value="{{ $condition->id }}">{{ $condition->condition_name }}</option>
                                    @endforeach
                                </select>
                                @error('condition_id') <span class="error">{{ $message }}</span> @enderror
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
                                <label>Vendor *</label>
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

        <!-- ... (other modals remain the same) ... -->
        @if ($showViewModal)
            <div class="modal-backdrop">
                <div class="modal">
                    <div class="modal-header">
                        <h2 class="modal-title">Asset Details: {{ $viewAsset->name ?? '' }}</h2>
                    </div>
                    
                    @if($viewAsset)
                    <div class="asset-details-grid view-grid">
                        <div class="detail-group">
                            <label>Asset Code:</label>
                            <p>{{ $viewAsset->asset_code }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Serial Number:</label>
                            <p>{{ $viewAsset->serial_number }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Name:</label>
                            <p>{{ $viewAsset->name }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Description:</label>
                            <p>{{ $viewAsset->description }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Quantity:</label>
                            <p>{{ $viewAsset->quantity }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Model Number:</label>
                            <p>{{ $viewAsset->model_number }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Category:</label>
                            <p>{{ $viewAsset->category->category_name }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Condition:</label>
                            <p>{{ $viewAsset->condition->condition_name }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Location:</label>
                            <p>{{ $viewAsset->location->location_name }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Vendor:</label>
                            <p>{{ $viewAsset->vendor->vendor_name }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Warranty Expiration:</label>
                            <p>{{ $viewAsset->warranty_expiration->format('M d, Y') }}</p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Status:</label>
                            <p>
                                @if($viewAsset->is_disposed)
                                    <span class="status-badge disposed">Disposed</span>
                                @else
                                    <span class="status-badge available">Available</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif                   
                   
                    <div class="modal-footer">
                        <button type="button" wire:click="closeModals" class="btn btn-secondary btn-sm-custom">
                            <i class="fas fa-xmark" style="margin-right: 0.5rem;"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Delete Confirmation Modal -->
        @if ($showDeleteModal)
            <div class="modal-backdrop">
                <div class="modal modal-delete">
                    <h2 class="modal-title">Confirm Deletion</h2>
                    <p class="modal-text">Are you sure you want to delete this asset? This action cannot be undone.</p>
                    
                    <div class="modal-actions">
                        <button wire:click="deleteAsset" class="btn btn-danger">
                            <i class="fas fa-trash-alt"></i> Confirm
                        </button>

                        <button wire:click="closeModals" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
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
        });
    </script>
</div>