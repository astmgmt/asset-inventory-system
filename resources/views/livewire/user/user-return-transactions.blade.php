<div class="superadmin-container">
    <h1 class="page-title main-title">Return Assets</h1>
    
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

    <!-- Search Bar -->
    <div class="search-bar mb-6 w-full md:w-1/3 relative">
        <input 
            type="text" 
            placeholder="Search by borrow code or date..." 
            wire:model.live.debounce.300ms="search"
            class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        @if($search)
            <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                &times;
            </button>
        @else
            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        @endif
    </div>

    <!-- Transactions Table -->
    <div class="overflow-x-auto">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Borrow Code</th>
                    <th>Status</th>
                    <th>Borrowed At</th>
                    <th>Approved At</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td data-label="Borrow Code" class="text-center">{{ $transaction->borrow_code }}</td>
                        <td data-label="Status" class="text-center">
                            <span class="status-badge approved">
                                {{ $transaction->status }}
                            </span>
                        </td>
                        <td data-label="Borrowed At" class="text-center">
                            {{ $transaction->borrowed_at->format('M d, Y H:i') }}
                        </td>
                        <td data-label="Approved At" class="text-center">
                            {{ $transaction->approved_at->format('M d, Y H:i') }}
                        </td>
                        <td data-label="Remarks" class="text-center">
                            {{ $transaction->remarks ?: 'N/A' }}
                        </td>
                        <td data-label="Actions" class="text-center">
                            <button 
                                wire:click="openReturnModal({{ $transaction->id }})"
                                class="return-btn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-md transition"
                            >
                                <i class="fas fa-undo mr-1"></i> Return
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-software-row">No approved borrow requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="mt-4 pagination-container">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Return Modal -->
    @if($showReturnModal && $selectedTransaction)
        <div class="modal-backdrop" x-data="{ show: @entangle('showReturnModal') }" x-show="show">
            <div class="modal" x-on:click.away="$wire.showReturnModal = false">
                <div class="modal-header">
                    <h2 class="modal-title">Return Assets: {{ $selectedTransaction->borrow_code }}</h2>
                    <button wire:click="$set('showReturnModal', false)" class="modal-close">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-4 flex items-center">
                        <input 
                            type="checkbox" 
                            id="select-all"
                            wire:model="selectAll"
                            wire:click="toggleSelectAll"
                            class="checkbox-success mr-2"
                        >
                        <label for="select-all" class="text-sm font-medium text-gray-700">
                            Select All Assets
                        </label>
                    </div>
                    
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th class="w-12"></th>
                                <th>Asset Code</th>
                                <th>Asset Name</th>
                                <th>Quantity</th>
                                <th>Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedTransaction->borrowItems as $item)
                                <tr>
                                    <td class="text-center">
                                        <input 
                                            type="checkbox" 
                                            wire:model="selectedItems.{{ $item->id }}"
                                            class="checkbox-item"
                                        >
                                    </td>
                                    <td data-label="Asset Code" class="text-center">
                                        {{ $item->asset->asset_code }}
                                    </td>
                                    <td data-label="Asset Name" class="text-center">
                                        {{ $item->asset->name }}
                                    </td>
                                    <td data-label="Quantity" class="text-center">
                                        {{ $item->quantity }}
                                    </td>
                                    <td data-label="Purpose" class="text-center">
                                        {{ $item->purpose ?: 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <!-- Remarks section -->
                    <div class="mt-6">
                        <label for="return-remarks" class="block text-sm font-medium text-gray-700 mb-1">
                            Remarks (Optional)
                        </label>
                        <textarea 
                            id="return-remarks" 
                            wire:model="returnRemarks" 
                            rows="3" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Add any remarks for the admin..."
                        ></textarea>
                    </div>
                    
                    @if ($errorMessage)
                        <div class="error-message mt-4">
                            {{ $errorMessage }}
                        </div>
                    @endif
                </div>
                
                <div class="modal-footer">
                    <button 
                        wire:click="$set('showReturnModal', false)" 
                        class="btn btn-secondary"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="confirmReturn" 
                        class="btn btn-primary ml-4"
                        :disabled="!count(array_filter($wire.selectedItems))"
                    >
                        <i class="fas fa-paper-plane mr-2"></i> Confirm Return
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Confirmation Modal -->
    <div class="modal-backdrop" x-data="{ show: @entangle('showConfirmationModal') }" x-show="show">
        <div class="modal" x-on:click.away="$wire.showConfirmationModal = false">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Return</h2>
                <button wire:click="$set('showConfirmationModal', false)" class="modal-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="text-center p-6">
                    <p class="text-lg mb-4">Do you really want to return the selected asset(s)?</p>
                    <p class="text-danger font-bold">This will initiate the return process!</p>
                    <p class="mt-4">Borrow Code: <strong>{{ $selectedTransaction->borrow_code ?? 'N/A' }}</strong></p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button 
                    wire:click="$set('showConfirmationModal', false)" 
                    class="btn btn-secondary"
                >
                    No, Cancel
                </button>
                <button 
                    wire:click="processReturn" 
                    class="btn btn-danger ml-4"
                >
                    Yes, Return Assets
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openPdf', (returnCode) => {
            // Open PDF in new tab
            window.open(`/return-pdf/${returnCode}`, '_blank');
        });
    });
</script>