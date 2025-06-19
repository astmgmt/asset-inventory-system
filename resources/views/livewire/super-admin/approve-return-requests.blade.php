<!-- resources/views/livewire/super-admin/approve-return-requests.blade.php -->

<div class="superadmin-container">
    <h1 class="page-title main-title">Approve Return Requests</h1>

    <div>
        <!-- Success/Error Messages -->
        @if ($successMessage)
            <div class="success-message mb-4" 
                 x-data="{ show: true }" 
                 x-show="show"
                 x-init="setTimeout(() => { show = false; $wire.clearMessages(); }, 3000)">
                {{ $successMessage }}
            </div>
        @endif

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

        <!-- Return Requests Table -->
        <div class="overflow-x-auto">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Borrow Code</th>
                        <th>Borrower</th>
                        <th>Department</th>
                        <th>Assets Count</th>
                        <th>Requested At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td data-label="Borrow Code" class="text-center">
                                {{ $transaction->borrow_code }}
                            </td>
                            <td data-label="Borrower" class="text-center">
                                {{ $transaction->user->name }}
                            </td>
                            <td data-label="Department" class="text-center">
                                {{ $transaction->user->department->name ?? 'N/A' }}
                            </td>

                            <td data-label="Assets" class="text-center">
                                {{
                                    $transaction->borrowItems->filter(function ($item) {
                                        return $item->returnItems->where('approval_status', 'Pending')->isNotEmpty();
                                    })->count()
                                }}
                            </td>

                            <td data-label="Requested At" class="text-center">
                                {{ $transaction->return_requested_at ? $transaction->return_requested_at->format('M d, Y H:i') : 'N/A' }}
                            </td>
                            <td data-label="Actions" class="text-center space-x-2">
                                <button 
                                    wire:click="openApproveModal({{ $transaction->id }})"
                                    class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded text-sm transition duration-150 ease-in-out"
                                >
                                    <i class="fas fa-check mr-1"></i> Approve
                                </button>

                                <button 
                                    wire:click="openRejectModal({{ $transaction->id }})"
                                    class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-3 rounded text-sm transition duration-150 ease-in-out"
                                >
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-software-row">No pending return requests</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="mt-4 pagination-container">
                {{ $transactions->links() }}
            </div>
        </div>

        <!-- Approve Modal -->
        @if($showApproveModal && $selectedTransaction)
            <div class="modal-backdrop" x-data="{ show: @entangle('showApproveModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showApproveModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Approve Return: {{ $selectedTransaction->borrow_code }}</h2>
                        <button wire:click="$set('showApproveModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-4">
                            <h3 class="font-semibold">Borrower:</h3>
                            <p>{{ $selectedTransaction->user->name }}</p>
                        </div>
                        
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Asset Code</th>
                                    <th>Asset Name</th>
                                    <th>Quantity</th>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <!-- Remarks section -->
                        <div class="mt-6">
                            <label for="approve-remarks" class="block text-sm font-medium text-gray-700 mb-1">
                                Remarks (Optional)
                            </label>
                            <textarea 
                                id="approve-remarks" 
                                wire:model="approveRemarks" 
                                rows="3" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Add any remarks for the borrower..."
                            ></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer flex justify-end space-x-3 mt-4">
                        <button 
                            wire:click="$set('showApproveModal', false)" 
                            class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white font-medium py-1.5 px-4 rounded text-sm transition duration-150 ease-in-out"
                        >
                            Cancel
                        </button>

                        <button 
                            wire:click="approveReturn" 
                            class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium py-1.5 px-4 rounded text-sm transition duration-150 ease-in-out"
                        >
                            <i class="fas fa-check mr-2"></i> Approve Return
                        </button>
                    </div>

                </div>
            </div>
        @endif

        <!-- Reject Modal -->
        @if($showRejectModal && $selectedTransaction)
            <div class="modal-backdrop" x-data="{ show: @entangle('showRejectModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showRejectModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Reject Return: {{ $selectedTransaction->borrow_code }}</h2>
                        <button wire:click="$set('showRejectModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-4">
                            <h3 class="font-semibold">Borrower:</h3>
                            <p>{{ $selectedTransaction->user->name }}</p>
                        </div>
                        
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Asset Code</th>
                                    <th>Asset Name</th>
                                    <th>Quantity</th>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <!-- Remarks section -->
                        <div class="mt-6">
                            <label for="reject-remarks" class="block text-sm font-medium text-gray-700 mb-1">
                                Reason for Rejection (Required)
                            </label>
                            <textarea 
                                id="reject-remarks" 
                                wire:model="rejectRemarks" 
                                rows="3" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Explain why this return is being rejected..."
                                required
                            ></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer flex justify-end space-x-3 mt-4">
                        <button 
                            wire:click="$set('showRejectModal', false)" 
                            class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white font-medium py-1.5 px-4 rounded text-sm transition duration-150 ease-in-out"
                        >
                            Cancel
                        </button>

                        <button 
                            wire:click="rejectReturn" 
                            class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-4 rounded text-sm transition duration-150 ease-in-out"
                        >
                            <i class="fas fa-times mr-2"></i> Reject Return
                        </button>
                    </div>

                </div>
            </div>
        @endif
    </div>
</div>