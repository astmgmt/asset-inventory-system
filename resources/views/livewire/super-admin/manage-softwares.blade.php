<div class="superadmin-container">
    <h1 class="page-title main-title">Manage Software</h1>
    
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
        <div class="search-bar w-full md:w-1/3 relative">
            <input                
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search software..."
                class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            @if($search)
                <button wire:click="clearSearch" class="absolute right-8 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            @endif
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
                    <th>Added By</th>
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
                        <td data-label="Added By" class="text-center">{{ $software->addedBy?->name ?? 'N/A' }}</td>
                        <td data-label="Expiry Date" class="text-center">
                            {{ $software->expiry_date->format('M d, Y') }}                            
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
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Add New Software</h2>

                <form wire:submit.prevent="createSoftware">
                    <!-- 2-column grid layout -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Software Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Software Name *</label>
                            <input type="text" wire:model="software_name"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('software_name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- License Key -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">License Key *</label>
                            <input type="text" wire:model="license_key"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('license_key') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model="description" rows="3"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                            @error('description') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date *</label>
                            <input type="date" wire:model="expiry_date"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('expiry_date') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            <i class="fas fa-plus-circle mr-2"></i> Add Software
                        </button>

                        <button type="button" wire:click="closeModals"
                            class="inline-flex items-center px-5 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition">
                            <i class="fas fa-ban mr-2"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif





    <!-- Edit Software Modal -->
    @if ($showEditModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Edit Software</h2>

                <form wire:submit.prevent="updateSoftware">
                    <!-- 2-column grid layout -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Software Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Software Name *</label>
                            <input type="text" wire:model="software_name"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('software_name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- License Key -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">License Key *</label>
                            <input type="text" wire:model="license_key"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('license_key') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date *</label>
                            <input type="date" wire:model="expiry_date"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('expiry_date') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model="description" rows="3"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                            @error('description') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                            <i class="fas fa-save mr-2"></i> Update
                        </button>

                        <button type="button" wire:click="closeModals"
                            class="inline-flex items-center px-5 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition">
                            <i class="fas fa-times-circle mr-2"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showViewModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div
                class="bg-gray-50 dark:bg-gray-800 rounded-lg shadow-2xl w-full max-w-3xl p-8 text-gray-900 dark:text-gray-100 transition-colors duration-300">
                <h2 class="text-3xl font-semibold mb-8 border-b border-gray-300 dark:border-gray-700 pb-4">
                    Software Details: {{ $viewSoftware->software_name ?? '' }}
                </h2>

                @if($viewSoftware)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                        <!-- Software Code -->
                        <div class="bg-white dark:bg-gray-700 rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Software Code</label>
                            <p class="text-lg font-medium break-words">{{ $viewSoftware->software_code }}</p>
                        </div>

                        <!-- License Key -->
                        <div class="bg-white dark:bg-gray-700 rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">License Key</label>
                            <p class="text-lg break-words">{{ $viewSoftware->license_key }}</p>
                        </div>

                        <!-- Software Name -->
                        <div class="bg-white dark:bg-gray-700 rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Software Name</label>
                            <p class="text-lg font-medium">{{ $viewSoftware->software_name }}</p>
                        </div>

                        <!-- Description -->
                        <div class="bg-white dark:bg-gray-700 rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Description</label>
                            <p class="text-lg whitespace-pre-line">{{ $viewSoftware->description }}</p>
                        </div>

                        <!-- Quantity -->
                        <div class="bg-white dark:bg-gray-700 rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Quantity</label>
                            <p class="text-lg">{{ $viewSoftware->quantity }}</p>
                        </div>

                        <!-- Expiry Date -->
                        <div class="bg-white dark:bg-gray-700 rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Expiry Date</label>
                            <p class="text-lg flex items-center space-x-2">
                                <span>{{ $viewSoftware->expiry_date->format('M d, Y') }}</span>                                
                            </p>
                        </div>


                        <!-- Added By -->
                        <div class="bg-white dark:bg-gray-700 rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Added By</label>
                            <p class="text-lg">{{ $viewSoftware->addedBy?->name ?? 'N/A' }}</p>
                        </div>

                        <!-- Expiry Status -->
                        <div class="bg-white dark:bg-gray-700 rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Expiry Status</label>
                            <p class="text-lg text-red-500">
                                {{ ucwords(str_replace('_', ' ', $viewSoftware->expiry_status)) }}
                            </p>
                        </div>
                    </div>
                @endif

                <div class="mt-10 flex justify-end">
                    <button type="button" wire:click="closeModals"
                        class="inline-flex items-center px-6 py-3 bg-gray-600 text-white text-base font-semibold rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                        <i class="fas fa-xmark mr-3"></i> Close
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