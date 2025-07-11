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
        <!-- SEARCH BAR -->
        <div class="relative w-full lg:w-1/3 mb-5">
            <input                
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search by name or username..."
                class="w-full p-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            
            @if($search)
                <button 
                    wire:click="clearSearch"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                    title="Clear search"
                >
                    <i class="fas fa-times"></i>
                </button>
            @else
                <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
            @endif
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
                    <th>Remarks</th>
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
                        
                        <td data-label="Status" class="text-center">
                            @php
                                $status = $software->assign_status;
                                $statusClass = match($status) {
                                    'Available' => 'bg-green-100 text-green-800',
                                    'Unavailable' => 'bg-red-100 text-red-800',
                                    'Assigned' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold w-[100px] justify-center {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td data-label="Expiry Date" class="text-center">
                            {{ $software->expiry_date->format('M d, Y') }}                            
                        </td>
                        <td data-label="Actions" class="text-center">
                            <div class="flex justify-center gap-3">
                                <!-- View Button -->
                                <button 
                                    wire:click="openViewModal({{ $software->id }})" 
                                    class="w-11 h-11 flex items-center justify-center text-blue-600 hover:text-blue-800 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded-full transition"
                                    title="View"
                                    aria-label="View"
                                >
                                    <i class="fas fa-eye text-sm" aria-hidden="true"></i>
                                </button>

                                <!-- Edit Button -->
                                <button 
                                    wire:click="openEditModal({{ $software->id }})" 
                                    class="w-11 h-11 flex items-center justify-center text-yellow-500 hover:text-yellow-600 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-300 rounded-full transition"
                                    title="Edit"
                                    aria-label="Edit"
                                >
                                    <i class="fas fa-edit text-sm" aria-hidden="true"></i>
                                </button>

                                @php
                                    $isAssigned = $status === 'Assigned';
                                @endphp

                                <!-- Delete Button -->
                                <button 
                                    @if($isAssigned) disabled @endif
                                    @if(!$isAssigned) wire:click="confirmDelete({{ $software->id }})" @endif
                                    class="w-11 h-11 flex items-center justify-center {{ $isAssigned ? 'text-gray-400 cursor-not-allowed' : 'text-red-600 hover:text-red-800 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-300' }} rounded-full transition"
                                    title="{{ $isAssigned ? 'Cannot delete assigned software' : 'Delete' }}"
                                    aria-label="{{ $isAssigned ? 'Cannot delete assigned software' : 'Delete' }}"
                                >
                                    <i class="fas fa-trash-alt text-sm" aria-hidden="true"></i>
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
                                class="form-input w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                class="form-input w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
            <div class="bg-gray-50 rounded-lg shadow-2xl w-full max-w-4xl p-8 text-gray-900 transition-colors duration-300 max-h-[90vh] overflow-y-auto">
                <h2 class="text-gray-700 text-2xl font-semibold mb-8 border-b border-gray-300 pb-4">
                    Software Details: {{ $viewSoftware->software_name ?? '' }}
                </h2>

                @if($viewSoftware)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                        <!-- Software Code -->
                        <div class="asset-details rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Software Code</label>
                            <p class="text-lg font-medium break-words">{{ $viewSoftware->software_code }}</p>
                        </div>

                        <!-- License Key -->
                        <div class="asset-details rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">License Key</label>
                            <p class="text-lg break-words">{{ $viewSoftware->license_key }}</p>
                        </div>

                        <!-- Software Name -->
                        <div class="asset-details rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Software Name</label>
                            <p class="text-lg font-medium">{{ $viewSoftware->software_name }}</p>
                        </div>

                        <!-- Description -->
                        <div class="asset-details rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Description</label>
                            <p class="text-lg whitespace-pre-line">{{ $viewSoftware->description }}</p>
                        </div>

                        <!-- Quantity -->
                        <div class="asset-details rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Quantity</label>
                            <p class="text-lg">{{ $viewSoftware->quantity }}</p>
                        </div>

                        <!-- Expiry Date -->
                        <div class="asset-details rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Expiry Date</label>
                            <p class="text-lg flex items-center space-x-2">
                                <span>{{ $viewSoftware->expiry_date->format('M d, Y') }}</span>                                
                            </p>
                        </div>


                        <!-- Added By -->
                        <div class="asset-details rounded-md p-4 shadow-sm">
                            <label class="block text-sm font-semibold text-gray-500 dark:text-gray-300 mb-1">Added By</label>
                            <p class="text-lg">{{ $viewSoftware->addedBy?->name ?? 'N/A' }}</p>
                        </div>

                        <!-- Expiry Status -->
                        <div class="asset-details rounded-md p-4 shadow-sm">
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
                <h2 class="text-lg font-semibold text-red-700 mb-2">Confirm Deletion</h2>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4 rounded-md">
                    <p class="text-sm text-red-700 mb-0">
                        Are you sure you want to delete this software? 
                        <strong class="font-medium">This action cannot be undone.</strong>
                    </p>
                </div>
                <div class="flex space-x-4 justify-center">
                    <button 
                        wire:click="deleteSoftware" 
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                        type="button"
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
                        type="button"
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
    });
</script>