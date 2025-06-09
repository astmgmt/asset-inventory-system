<div class="superadmin-container">
    <h1 class="page-title">Manage Software</h1>
    
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
        <div class="search-bar w-full md:w-1/3">
            <input                
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search software..."
                class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />   
            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>             
        </div>        
        
        <div class="flex justify-end mb-4">
            <button wire:click="openAddModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md shadow-sm inline-flex items-center transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i> Add New Software
            </button>
        </div>
    </div>

    <!-- Software Table -->
        <table class="user-table">
            <thead>
                <tr>
                    <th>Software Code</th>
                    <th>Name</th>
                    <th>License Key</th>
                    <th>Responsible User</th>
                    <th>Expiry Date</th>
                    <th class="actions-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($softwares as $software)
                    <tr>
                        <td data-label="Software Code" class="text-center">{{ $software->software_code }}</td>
                        <td data-label="Name" class="text-center">{{ $software->software_name }}</td>
                        <td data-label="License Key" class="text-center">{{ substr($software->license_key, 0, 8) . '...' }}</td>
                        <td data-label="Responsible User" class="text-center">{{ $software->responsibleUser->name }}</td>
                        <td data-label="Expiry Date" class="text-center">
                            {{ $software->expiry_date->format('M d, Y') }}
                            @if($software->expiry_date < now()->addDays(30))
                                <span class="expiring-badge">Expiring</span>
                            @endif
                        </td>
                        <td data-label="Actions" class="text-center">
                            <div class="flex justify-center gap-3">
                                <button wire:click="openViewModal({{ $software->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button wire:click="openEditModal({{ $software->id }})" class="text-yellow-500 hover:text-yellow-600 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $software->id }})" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-software-row">No software found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="mt-4 pagination-container">
            {{ $softwares->links() }}
        </div>

    <!-- Add Software Modal -->
    @if ($showAddModal)
        <div class="modal-backdrop">
            <div class="modal">
                <h2 class="modal-title">Add New Software</h2>
                
                <form wire:submit.prevent="createSoftware">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Software Name *</label>
                            <input type="text" wire:model="software_name" class="form-input">
                            @error('software_name') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>License Key *</label>
                            <input type="text" wire:model="license_key" class="form-input">
                            @error('license_key') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Installation Date *</label>
                            <input type="date" wire:model="installation_date" class="form-input">
                            @error('installation_date') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Expiry Date *</label>
                            <input type="date" wire:model="expiry_date" class="form-input">
                            @error('expiry_date') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Responsible User *</label>
                            <select wire:model="responsible_user_id" class="form-input">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('responsible_user_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group col-span-2">
                            <label>Description</label>
                            <textarea wire:model="description" rows="3" class="form-input"></textarea>
                            @error('description') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="submit" class="btn btn-primary">Create Software</button>
                        <button type="button" wire:click="closeModals" class="btn btn-secondary">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Edit Software Modal -->
    @if ($showEditModal)
        <div class="modal-backdrop">
            <div class="modal">
                <h2 class="modal-title">Edit Software</h2>
                
                <form wire:submit.prevent="updateSoftware">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Software Name *</label>
                            <input type="text" wire:model="software_name" class="form-input">
                            @error('software_name') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>License Key *</label>
                            <input type="text" wire:model="license_key" class="form-input">
                            @error('license_key') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Installation Date *</label>
                            <input type="date" wire:model="installation_date" class="form-input">
                            @error('installation_date') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Expiry Date *</label>
                            <input type="date" wire:model="expiry_date" class="form-input">
                            @error('expiry_date') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Responsible User *</label>
                            <select wire:model="responsible_user_id" class="form-input">
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('responsible_user_id') <span class="error">{{ $message }}</span> @enderror
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
                        <button type="button" wire:click="closeModals" class="btn btn-secondary">
                            <i class="fas fa-times-circle"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- View Software Modal -->
    @if ($showViewModal)
        <div class="modal-backdrop">
            <div class="modal">
                <h2 class="modal-title">Software Details: {{ $viewSoftware->software_name ?? '' }}</h2>
                
                @if($viewSoftware)
                <div class="software-details-grid view-grid">
                    <div class="detail-group">
                        <label>Software Code:</label>
                        <p>{{ $viewSoftware->software_code }}</p>
                    </div>
                    
                    <div class="detail-group">
                        <label>License Key:</label>
                        <p>{{ $viewSoftware->license_key }}</p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Installation Date:</label>
                        <p>{{ $viewSoftware->installation_date->format('M d, Y') }}</p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Expiry Date:</label>
                        <p>
                            {{ $viewSoftware->expiry_date->format('M d, Y') }}
                            @if($viewSoftware->expiry_date < now()->addDays(30))
                                <span class="expiring-badge">Expiring</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Responsible User:</label>
                        <p>{{ $viewSoftware->responsibleUser->name }}</p>
                    </div>
                    
                    <div class="detail-group col-span-2">
                        <label>Description:</label>
                        <p>{{ $viewSoftware->description }}</p>
                    </div>
                </div>
                @endif
                
                <div class="modal-actions">
                    <button type="button" wire:click="closeModals" class="btn btn-secondary">
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
                <p class="modal-text">Are you sure you want to delete this software? This action cannot be undone.</p>
                
                <div class="modal-actions">
                    <button wire:click="deleteSoftware" class="btn btn-danger">
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
    });
</script>