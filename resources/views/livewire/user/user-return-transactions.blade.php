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
                                @php
                                    $status = $transaction->status;

                                    $statusClass = match($status) {
                                        'Borrowed' => 'bg-indigo-100 text-indigo-800',
                                        'PendingReturnApproval' => 'bg-yellow-100 text-yellow-800',
                                        'ReturnRejected' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };

                                    // Display label override for some statuses
                                    $displayStatus = match($status) {
                                        'PendingReturnApproval' => 'Pending',
                                        'ReturnRejected' => 'Rejected',
                                        default => $status,
                                    };
                                @endphp

                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold w-[100px] justify-center {{ $statusClass }}">
                                    {{ $displayStatus }}
                                </span>
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
                                @if($transaction->return_remarks)
                                    <div class="remarks-container">
                                        <div class="truncated-remarks" title="{{ $transaction->return_remarks }}">
                                            {{ \Illuminate\Support\Str::limit($transaction->return_remarks, 25) }}
                                        </div>
                                    </div>
                                @else
                                    N/A
                                @endif
                            </td>                           

                            <td data-label="Actions" class="text-center">
                                @php
                                    $borrowItems = $transaction->borrowItems;
                                    $borrowedCount = $borrowItems->where('status', 'Borrowed')->count();
                                    $pendingReturnCount = $borrowItems->where('status', 'PendingReturnApproval')->count();
                                    $returnedCount = $borrowItems->where('status', 'Returned')->count();
                                    $rejectedCount = $borrowItems->where('status', 'ReturnRejected')->count();
                                    $totalCount = $borrowItems->count();
                                @endphp

                                @if ($transaction->status === 'ReturnRejected')
                                    <button wire:click="openReturnModal({{ $transaction->id }})"
                                            class="return-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md">
                                        <i class="fas fa-redo mr-1"></i> Return Again
                                    </button>

                                @elseif ($borrowedCount === 0 && $pendingReturnCount > 0)
                                    <button class="btn-disabled bg-gray-300 text-gray-500 py-1 px-3 rounded-md cursor-not-allowed">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </button>

                                @elseif ($pendingReturnCount > 0 && $borrowedCount > 0)
                                    <button wire:click="openReturnModal({{ $transaction->id }})"
                                            class="return-btn bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-3 rounded-md">
                                        <i class="fas fa-undo mr-1"></i> Return Others
                                    </button>

                                @elseif ($borrowedCount > 0 && $returnedCount > 0)
                                    <button wire:click="openReturnModal({{ $transaction->id }})"
                                            class="return-btn bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-3 rounded-md">
                                        <i class="fas fa-undo mr-1"></i> Return Others
                                    </button>

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
                                <i class="fas fa-spinner fa-spin mr-2"></i> Loading...
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
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        Do you really want to return the selected asset(s) now?
                                    </p>
                                    <p class="text-sm text-blue-700 mt-2">
                                        Borrow Code: <strong class="font-medium">{{ $selectedTransaction->borrow_code ?? 'N/A' }}</strong>
                                    </p>
                                    <p class="text-sm text-blue-700 mt-1">
                                        Number of Assets: <strong class="font-medium">{{ count($selectedItems) }}</strong>
                                    </p>
                                </div>
                            </div>
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
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="btn btn-danger ml-4"
                        >
                            <span wire:loading.class.add="hidden">
                                Yes, Return Asset(s)
                            </span>
                            <span wire:loading.class.remove="hidden" class="hidden flex items-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                            </span>
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