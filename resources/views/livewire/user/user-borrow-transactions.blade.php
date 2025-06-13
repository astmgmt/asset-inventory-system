<div class="superadmin-container">
    <h1 class="page-title main-title">Borrow Requests</h1>
    
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
            placeholder="Search by code, status, date..." 
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
                    <th>Date Request</th>
                    <th>Remarks</th>
                    <th>Borrowed At</th>
                    <th>Approved At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td data-label="Borrow Code" class="text-center">{{ $transaction->borrow_code }}</td>
                        <td data-label="Status" class="text-center">
                            <span class="status-badge {{ strtolower($transaction->status) }}">
                                {{ $transaction->status }}
                            </span>
                        </td>
                        <td data-label="Date Request" class="text-center">
                            {{ $transaction->created_at->format('F d, Y')}}
                        </td>
                        <td data-label="Remarks" class="text-center">
                            {{ $transaction->remarks ?: 'N/A' }}
                        </td>
                        <td data-label="Borrowed At" class="text-center">
                            {{ $transaction->borrowed_at ? $transaction->borrowed_at->format('F d, Y') : 'N/A' }}
                        </td>
                        <td data-label="Approved At" class="text-center">
                            @if($transaction->status === 'Approved' && $transaction->approved_at)
                                {{ $transaction->approved_at->format('F d, Y') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td data-label="Actions" class="text-center">
                            <button 
                                wire:click="showDetails({{ $transaction->id }})"
                                class="view-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md transition"
                            >
                                <i class="fas fa-eye"></i> View
                            </button>
                            
                            @if($transaction->status === 'Pending')
                                <button 
                                    wire:click="confirmCancel({{ $transaction->id }})"
                                    class="cancel-btn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md ml-2 transition"
                                >
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-software-row">No borrow requests found.</td>
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
                                <th>Asset Name</th>
                                <th>Asset Code</th>
                                <th>Quantity</th>
                                <th>Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedTransaction->borrowItems as $item)
                                <tr>
                                    <td data-label="Asset Name" class="text-center">{{ $item->asset->name }}</td>
                                    <td data-label="Asset Code" class="text-center">{{ $item->asset->asset_code }}</td>
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
    <div class="modal-backdrop" x-data="{ show: @entangle('showCancelModal') }" x-show="show">
        <div class="modal" x-on:click.away="$wire.showCancelModal = false">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Cancellation</h2>
                <button wire:click="$set('showCancelModal', false)" class="modal-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="text-center p-6">
                    <p class="text-lg mb-4">Do you really want to cancel this request?</p>
                    <p class="text-danger font-bold">This will delete your request permanently!</p>
                    <p class="mt-4">Borrow Code: <strong>{{ $transactionToCancel->borrow_code ?? 'N/A' }}</strong></p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button 
                    wire:click="$set('showCancelModal', false)" 
                    class="btn btn-secondary"
                >
                    No, Keep It
                </button>
                <button 
                    wire:click="cancelRequest" 
                    class="btn btn-danger ml-4"
                >
                    Yes, Cancel Request
                </button>
            </div>
        </div>
    </div>
</div>