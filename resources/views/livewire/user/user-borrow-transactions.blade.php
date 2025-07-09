<div class="superadmin-container">
    <h1 class="page-title main-title">Pending Borrow Requests</h1>
    
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
            placeholder="Search by code or date..." 
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
                    <th>Date Requested</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td data-label="Borrow Code" class="text-center">
                            <button wire:click="showDetails({{ $transaction->id }})" class="text-blue-500 hover:underline">
                                {{ $transaction->borrow_code }}
                            </button>
                        </td>
                        <td data-label="Status" class="text-center">
                            <span class="status-badge pending">Pending</span>
                        </td>
                        <td data-label="Date Requested" class="text-center">
                            {{ $transaction->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td data-label="Remarks" class="text-center">
                            {{ $transaction->remarks ?: 'N/A' }}
                        </td>
                        <td data-label="Actions" class="text-center">
                            <button 
                                wire:click="showDetails({{ $transaction->id }})"
                                class="view-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md transition"
                            >
                                <i class="fas fa-eye mr-1"></i> View
                            </button>
                            
                            <button 
                                wire:click="confirmCancel({{ $transaction->id }})"
                                class="cancel-btn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md ml-2 transition"
                            >
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="no-software-row">No pending borrow requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="mt-4 pagination-container">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- View Details Modal -->
    @if($showDetailsModal && $selectedTransaction)
        <div class="modal-backdrop" x-data="{ show: @entangle('showDetailsModal') }" x-show="show">
            <div class="modal" x-on:click.away="$wire.showDetailsModal = false">
                <div class="modal-header">
                    <h2 class="modal-title">Request Details: {{ $selectedTransaction->borrow_code }}</h2>
                    <button wire:click="$set('showDetailsModal', false)" class="modal-close">&times;</button>
                </div>
                
                <div class="modal-body">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Asset Code</th>
                                <th>Brand</th>
                                <th>Model</th>                                
                                <th>Qty.</th>
                                <th>Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedTransaction->borrowItems as $item)
                                <tr>
                                    <td data-label="Asset Code" class="text-center">{{ $item->asset->asset_code }}</td>
                                    <td data-label="Brand" class="text-center">{{ $item->asset->name }}</td>
                                    <td data-label="Brand" class="text-center">{{ $item->asset->model_number }}</td>                                    
                                    <td data-label="Quantity" class="text-center">{{ $item->quantity }}</td>
                                    <td data-label="Purpose" class="text-center">{{ $item->purpose ?: 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="modal-footer">
                    <button 
                        wire:click="$set('showDetailsModal', false)" 
                        class="btn btn-secondary"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Cancel Confirmation Modal -->
    @if($showCancelModal)
        <div class="modal-backdrop" x-data="{ show: @entangle('showCancelModal') }" x-show="show">
            <div class="modal" x-on:click.away="$wire.showCancelModal = false">
                <div class="modal-header">
                    <h2 class="modal-title">Confirm Cancellation</h2>
                    <button wire:click="$set('showCancelModal', false)" class="modal-close">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Do you really want to cancel this borrow request?
                                    <strong class="font-medium">This will release all reserved assets!</strong>
                                </p>
                                <p class="text-sm text-yellow-700 mt-2">
                                    Borrow Code: <strong>{{ $transactionToCancel->borrow_code ?? 'N/A' }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button 
                        wire:click="$set('showCancelModal', false)" 
                        class="btn btn-secondary"
                    >
                        No, Keep Request
                    </button>
                    <button 
                        wire:click="cancelRequest" 
                        wire:loading.attr="disabled" wire:target="cancelRequest"
                        wire:loading.class="opacity-50 cursor-not-allowed" wire:target="cancelRequest"
                        class="btn btn-danger ml-4"
                    >
                        <span wire:loading.class.add="hidden" wire:target="cancelRequest">
                            Yes, Cancel Request
                        </span>
                        <span wire:loading.class.remove="hidden" wire:target="cancelRequest" class="hidden flex items-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>