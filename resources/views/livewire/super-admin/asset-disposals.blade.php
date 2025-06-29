<div class="superadmin-container">
    <h1 class="page-title main-title">Asset Disposal Management</h1>
    
    <!-- Real-time polling -->
    <div wire:poll.10s>
        <!-- Success Message -->
        @if ($successMessage)
            <div class="success-message mb-4" 
                 x-data="{ show: true }" 
                 x-show="show"
                 x-init="setTimeout(() => { show = false; $wire.clearMessages(); }, 3000)">
                {{ $successMessage }}
            </div>
        @endif

        <!-- Error Message -->
        @if ($errorMessage)
            <div class="error-message mb-4" 
                 x-data="{ show: true }" 
                 x-show="show"
                 x-init="setTimeout(() => { show = false; $wire.clearMessages(); }, 3000)">
                {{ $errorMessage }}
            </div>
        @endif
    </div>

    <!-- Search Bar -->
    <div class="search-bar mb-6 w-full md:w-1/3 relative">
        <input 
            type="text" 
            placeholder="Search code, name..." 
            wire:model.live.debounce.300ms="search"
            class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        @if($search)
            <button wire:click="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                &times;
            </button>
        @else
            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        @endif
    </div>

    <!-- Asset Table -->
    <div class="overflow-x-auto">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Asset Name</th>
                    <th>Condition</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    <tr>
                        <td data-label="Asset Code" class="text-center">{{ $asset->asset_code }}</td>
                        <td data-label="Asset Name" class="text-center">{{ $asset->name }}</td>
                        <td data-label="Condition" class="text-center">
                            <span class="status-badge {{ strtolower($asset->condition->condition_name) }}">
                                {{ $asset->condition->condition_name }}
                            </span>
                        </td>
                        <td data-label="Category" class="text-center">{{ $asset->category->category_name }}</td>
                        <td data-label="Location" class="text-center">{{ $asset->location->location_name }}</td>
                        <td data-label="Actions" class="text-center">
                            <button 
                                wire:click="disposeAsset({{ $asset->id }})"
                                class="view-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md transition"
                            >
                                Dispose
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-software-row">No defective assets found for disposal.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="mt-4 pagination-container">
            {{ $assets->links() }}
        </div>
    </div>

    <!-- Dispose Asset Modal -->
    @if($showDisposeModal)
        <div class="modal-backdrop" x-data="{ show: @entangle('showDisposeModal') }" x-show="show">
            <div class="modal" x-on:click.away="$wire.showDisposeModal = false">
                <div class="modal-header">
                    <h2 class="modal-title">
                        @if($selectedAsset)
                            Dispose Asset: {{ $selectedAsset->asset_code }} - {{ $selectedAsset->name }}
                        @else
                            Dispose Asset
                        @endif
                    </h2>
                    <button wire:click="closeModal" class="modal-close">&times;</button>
                </div>
                
                <div class="modal-body">
                    @if($selectedAsset)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4 text-sm font-sans">
                            <div class="p-2 border border-gray-200 rounded-lg shadow-sm bg-white">
                                <p class="font-semibold text-xs text-gray-600 uppercase tracking-wide mb-0.5">Asset Code</p>
                                <p class="text-gray-800 leading-tight">{{ $selectedAsset->asset_code }}</p>
                            </div>
                            <div class="p-2 border border-gray-200 rounded-lg shadow-sm bg-white">
                                <p class="font-semibold text-xs text-gray-600 uppercase tracking-wide mb-0.5">Asset Name</p>
                                <p class="text-gray-800 leading-tight">{{ $selectedAsset->name }}</p>
                            </div>
                            <div class="p-2 border border-gray-200 rounded-lg shadow-sm bg-white">
                                <p class="font-semibold text-xs text-gray-600 uppercase tracking-wide mb-0.5">Category</p>
                                <p class="text-gray-800 leading-tight">{{ $selectedAsset->category->category_name }}</p>
                            </div>
                            <div class="p-2 border border-gray-200 rounded-lg shadow-sm bg-white">
                                <p class="font-semibold text-xs text-gray-600 uppercase tracking-wide mb-0.5">Location</p>
                                <p class="text-gray-800 leading-tight">{{ $selectedAsset->location->location_name }}</p>
                            </div>
                            <div class="p-2 border border-gray-200 rounded-lg shadow-sm bg-white">
                                <p class="font-semibold text-xs text-gray-600 uppercase tracking-wide mb-0.5">Condition</p>
                                <p>
                                    <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ 
                                        strtolower($selectedAsset->condition->condition_name) === 'good' ? 'bg-green-100 text-green-800' : 
                                        (strtolower($selectedAsset->condition->condition_name) === 'fair' ? 'bg-yellow-100 text-yellow-800' : 
                                        (strtolower($selectedAsset->condition->condition_name) === 'poor' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'))
                                        }}">
                                        {{ $selectedAsset->condition->condition_name }}
                                    </span>
                                </p>
                            </div>
                            <div class="p-2 border border-gray-200 rounded-lg shadow-sm bg-white">
                                <p class="font-semibold text-xs text-gray-600 uppercase tracking-wide mb-0.5">Serial Number</p>
                                <p class="text-gray-800 leading-tight">{{ $selectedAsset->serial_number }}</p>
                            </div>
                        </div>



                        <!-- Full spacing for form elements -->
                        <div class="mt-4 border-t border-gray-200 pt-4">
                            <div class="mt-4">
                                <label for="disposalMethod" class="block text-sm font-medium mb-1">Disposal Method *</label>
                                <select 
                                    wire:model="disposalMethod"
                                    id="disposalMethod" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">Select a disposal method</option>
                                    @foreach($disposalMethods as $method)
                                        <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                                    @endforeach
                                </select>
                                @error('disposalMethod') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="mt-4">
                                <label for="reason" class="block text-sm font-medium mb-1">Reason for Disposal *</label>
                                <textarea 
                                    wire:model="reason"
                                    id="reason" 
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Explain why this asset needs to be disposed..."
                                ></textarea>
                                @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="mt-4">
                                <label for="notes" class="block text-sm font-medium mb-1">Additional Notes</label>
                                <textarea 
                                    wire:model="notes"
                                    id="notes" 
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Any additional information about the disposal..."
                                ></textarea>
                            </div>
                        </div>
                    @else
                        <p class="text-center py-8 text-gray-500">
                            Asset information is no longer available
                        </p>
                    @endif
                </div>
                
                <div class="modal-footer">
                    <button 
                        wire:click="closeModal" 
                        class="btn btn-secondary"
                    >
                        Cancel
                    </button>
                    @if($selectedAsset)
                        <button 
                            wire:click="confirmDisposal" 
                            class="btn btn-danger ml-4"
                        >
                            Proceed to Dispose
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Confirmation Modal -->
    @if($showConfirmModal)
        <div class="modal-backdrop" x-data="{ show: @entangle('showConfirmModal') }" x-show="show">
            <div class="modal modal-delete" x-on:click.away="$wire.showConfirmModal = false">
                <div class="modal-header">
                    <h2 class="modal-title">Confirm Asset Disposal</h2>
                    <button wire:click="closeModal" class="modal-close">&times;</button>
                </div>
                
                <div class="modal-body">
                    @if($selectedAsset)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Are you sure you want to permanently dispose of this asset?
                                        <strong class="font-medium">This action cannot be undone.</strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded border border-gray-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-lg bg-red-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-900">{{ $selectedAsset->asset_code }} - {{ $selectedAsset->name }}</h3>
                                    <p class="text-sm text-gray-500">
                                        <span class="font-medium">Method:</span> {{ ucfirst(str_replace('_', ' ', $disposalMethod)) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-center py-8 text-gray-500">
                            Asset information is no longer available
                        </p>
                    @endif
                </div>
                
                <div class="modal-footer">
                    <button 
                        wire:click="closeModal" 
                        class="btn btn-secondary"
                    >
                        Cancel
                    </button>
                    @if($selectedAsset)
                        <button 
                            wire:click="performDisposal" 
                            class="btn btn-danger ml-4"
                        >
                            Confirm Disposal
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>