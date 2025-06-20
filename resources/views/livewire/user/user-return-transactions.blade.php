<div class="superadmin-container">
    <h1 class="page-title main-title">Return Assets</h1>

    <div>
        <!-- Flash Messages -->
        @if ($successMessage)
            <div class="success-message mb-4" 
                 x-data="{ show: true }" 
                 x-show="show"
                 x-init="setTimeout(() => { show = false; $wire.clearMessages(); }, 5000)">
                {{ $successMessage }}
            </div>
        @endif

        @if ($errorMessage)
            <div class="error-message mb-4" 
                 x-data="{ show: true }" 
                 x-show="show"
                 x-init="setTimeout(() => { show = false; $wire.clearMessages(); }, 5000)">
                {{ $errorMessage }}
            </div>
        @endif

        <!-- Search Bar -->
        <div class="search-bar mb-6 w-full md:w-1/3 relative">
            <input 
                type="text" 
                placeholder="Search by borrow code..." 
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
                        <th>Return Requested</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td data-label="Borrow Code" class="text-center">
                                <button wire:click="openViewModal({{ $transaction->id }})" 
                                        class="text-blue-500 hover:underline">
                                    {{ $transaction->borrow_code }}
                                </button>
                            </td>
                            <td data-label="Status" class="text-center">
                                @if($transaction->status === 'Borrowed')
                                    <span class="status-badge borrowed">Borrowed</span>
                                @elseif($transaction->status === 'PendingReturnApproval')
                                    <span class="status-badge pending">Pending</span>
                                @elseif($transaction->status === 'ReturnRejected')
                                    <span class="status-badge rejected">Rejected</span>
                                @endif
                            </td>
                            <td data-label="Borrowed At" class="text-center">
                                {{ $transaction->borrowed_at?->format('M d, Y') ?? '-' }}
                            </td>
                            <td data-label="Return Requested" class="text-center">
                                @if($transaction->status === 'ReturnRejected' || $transaction->status === 'PendingReturnApproval')
                                    {{ $transaction->return_requested_at?->format('M d, Y') ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td data-label="Remarks" class="text-center">
                                {{ $transaction->return_remarks ?: 'N/A' }}
                            </td>

                            
                            <td data-label="Actions" class="text-center">
                                @php
                                    $borrowItems = $transaction->borrowItems;
                                    $borrowedCount = $borrowItems->where('status', 'Borrowed')->count();
                                    $pendingReturnCount = $borrowItems->where('status', 'PendingReturnApproval')->count();
                                    $returnedCount = $borrowItems->where('status', 'Returned')->count();
                                    $totalCount = $borrowItems->count();
                                @endphp

                                {{-- 1. Show "Return Again" if rejected --}}
                                @if ($transaction->status === 'ReturnRejected')
                                    <button wire:click="openReturnModal({{ $transaction->id }})"
                                            class="return-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md">
                                        <i class="fas fa-redo mr-1"></i> Return Again
                                    </button>

                                {{-- 2. All items are pending approval, show "Pending" --}}
                                @elseif ($borrowedCount === 0 && $pendingReturnCount > 0)
                                    <button class="btn-disabled bg-gray-300 text-gray-500 py-1 px-3 rounded-md cursor-not-allowed">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </button>

                                {{-- 3. Partial return, show "Return Others" --}}
                                @elseif ($borrowedCount > 0 && $returnedCount > 0)
                                    <button wire:click="openReturnModal({{ $transaction->id }})"
                                            class="return-btn bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-3 rounded-md">
                                        <i class="fas fa-undo mr-1"></i> Return Others
                                    </button>

                                {{-- 4. All items still borrowed, normal return --}}
                                @elseif ($borrowedCount === $totalCount)
                                    <button wire:click="openReturnModal({{ $transaction->id }})"
                                            class="return-btn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-md">
                                        <i class="fas fa-undo mr-1"></i> Return
                                    </button>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-software-row">No borrow transactions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="mt-4 pagination-container">
                {{ $transactions->links() }}
            </div>
        </div>

        <!-- View Modal -->
        @if($showViewModal && $selectedTransaction)
            <div class="modal-backdrop" x-data="{ show: @entangle('showViewModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showViewModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Borrow Details: {{ $selectedTransaction->borrow_code }}</h2>
                        <button wire:click="$set('showViewModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <h3 class="font-semibold">Borrower:</h3>
                                <p>{{ $selectedTransaction->user->name }}</p>
                            </div>
                            <div>
                                <h3 class="font-semibold">Department:</h3>
                                <p>{{ $selectedTransaction->user->department->name }}</p>
                            </div>
                            <div>
                                <h3 class="font-semibold">Borrowed At:</h3>
                                <p>{{ $selectedTransaction->borrowed_at?->format('M d, Y H:i') }}</p>
                            </div>
                            <div>
                                <h3 class="font-semibold">Status:</h3>
                                <p>
                                    @if($selectedTransaction->status === 'Borrowed')
                                        <span class="status-badge borrowed">Borrowed</span>
                                    @elseif($selectedTransaction->status === 'PendingReturnApproval')
                                        <span class="status-badge pending">Pending</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <h3 class="font-semibold mb-2">Borrowed Assets:</h3>
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Asset Code</th>
                                    <th>Name</th>
                                    <th>Quantity</th>
                                    <th>Purpose</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedTransaction->borrowItems as $item)
                                    <tr>
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
                    </div>
                    
                    <div class="modal-footer">
                        <button wire:click="$set('showViewModal', false)" class="btn btn-secondary">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Return Modal -->
        @if($showReturnModal && $selectedTransaction)
            <div class="modal-backdrop" x-data="{ show: @entangle('showReturnModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showReturnModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Return Assets: {{ $selectedTransaction->borrow_code }}</h2>
                        <button wire:click="$set('showReturnModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th class="w-10">
                                        <input type="checkbox" 
                                            wire:model.live="selectAll"
                                            class="form-checkbox h-4 w-4 text-blue-600"
                                        >
                                    </th>
                                    <th>Asset Code</th>
                                    <th>Asset Name</th>
                                    <th>Quantity</th>
                                    <th>Purpose</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedTransaction->borrowItems->whereIn('status', ['Borrowed', 'PendingReturnApproval', 'ReturnRejected']) as $item)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" 
                                                wire:model.live="selectedItems"
                                                value="{{ $item->id}}"
                                                class="form-checkbox h-4 w-4 text-blue-600"
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
                            wire:loading.attr="disabled"
                            class="btn btn-danger ml-4 {{ empty($selectedItems) ? 'opacity-50 cursor-not-allowed' : '' }}"
                            @if(empty($selectedItems)) disabled @endif
                        >
                            <span wire:loading.remove>
                                <i class="fas fa-paper-plane mr-2"></i> Submit Return Request
                            </span>
                            <span wire:loading>
                                <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Confirmation Modal -->
        @if($showConfirmationModal)
            <div class="modal-backdrop" x-data="{ show: @entangle('showConfirmationModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showConfirmationModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Confirm Return Request</h2>
                        <button wire:click="$set('showConfirmationModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="text-center p-6">
                            <p class="text-lg mb-4">Do you really want to return the selected asset(s) now?</p>
                            <p class="mt-4">Borrow Code: <strong>{{ $selectedTransaction->borrow_code ?? 'N/A' }}</strong></p>
                            <p class="mt-2">Number of Assets: <strong>{{ count($selectedItems) }}</strong></p>
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
                            Yes, Return Asset(s)
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <style>
        .btn:disabled {
            pointer-events: none;
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</div>