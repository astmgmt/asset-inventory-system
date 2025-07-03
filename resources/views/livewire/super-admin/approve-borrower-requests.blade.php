<div class="superadmin-container">
    <h1 class="page-title main-title">Approve Borrow Requests</h1>

    <!-- Success/Error Messages -->
    @if ($successMessage)
        <div class="success-message mb-4" 
            x-data="{ show: true }" 
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="error-message mb-4" 
            x-data="{ show: true }" 
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Search Bar -->
    <div class="search-bar mb-6 w-full md:w-1/3 relative">
        <input 
            type="text" 
            placeholder="Search code, borrower..." 
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

    <!-- Real-time polling -->
    <div wire:poll.5s>
        <!-- Transactions Table -->
        <div class="overflow-x-auto">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Borrow Code</th>
                        <th>Borrower</th>
                        <th>Borrower Department</th>
                        <th>Requested By</th>
                        <th>Requested At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td data-label="Borrow Code" class="text-center">{{ $transaction->borrow_code }}</td>
                            <td data-label="Borrower" class="text-center">
                                {{ $transaction->user->name }}
                            </td>
                            <td data-label="Department" class="text-center">
                                {{ $transaction->userDepartment->name ?? 'N/A' }}
                            </td>
                            <td data-label="Requested By" class="text-center">
                                {{ $transaction->requestedBy->name ?? 'N/A' }}
                            </td>
                            <td data-label="Requested At" class="text-center">
                                {{ $transaction->created_at->format('M d, Y H:i') }}
                            </td>
                            <td data-label="Actions" class="text-center">
                                <div class="flex justify-center space-x-2">
                                    <button 
                                        wire:click="showDetails({{ $transaction->id }})"
                                        class="view-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md transition mb-1"
                                    >
                                        <i class="fas fa-eye"></i> Details
                                    </button>
                                    
                                    <button 
                                        wire:click="confirmApprove({{ $transaction->id }})"
                                        class="approve-btn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-md transition mb-1"
                                    >
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    
                                    <button 
                                        wire:click="confirmDeny({{ $transaction->id }})"
                                        class="deny-btn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md transition mb-1"
                                    >
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-software-row">No pending borrow requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="mt-4 pagination-container">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    @if($showDetailsModal && $selectedTransaction)
        <div class="modal-backdrop" x-data="{ show: @entangle('showDetailsModal') }" x-show="show">
            <div class="modal" x-on:click.away="$wire.showDetailsModal = false">
                <div class="modal-header">
                    <h2 class="modal-title">Borrow Details: {{ $selectedTransaction->borrow_code }}</h2>
                    <button wire:click="$set('showDetailsModal', false)" class="modal-close">&times;</button>
                </div>

                <div class="modal-body text-[12px]">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 text-[12px] leading-tight">
                        <div class="bg-white border border-gray-200 rounded shadow-sm p-4 space-y-1">
                            <h3 class="font-semibold text-[14px] text-gray-900 mb-2">Borrower Information</h3>
                            <p>
                                <span class="font-medium text-gray-600">Name:</span>
                                <span class="text-gray-900">{{ $selectedTransaction->user->name }}</span>
                            </p>
                            <p>
                                <span class="font-medium text-gray-600">Department:</span>
                                <span class="text-gray-900">{{ $selectedTransaction->userDepartment->name ?? 'N/A' }}</span>
                            </p>
                        </div>

                        <div class="bg-white border border-gray-200 rounded shadow-sm p-4 space-y-1">
                            <h3 class="font-semibold text-[14px] text-gray-900 mb-2">Request Information</h3>
                            <p>
                                <span class="font-medium text-gray-600">Requested By:</span>
                                <span class="text-gray-900">{{ $selectedTransaction->requestedBy->name ?? 'N/A' }}</span>
                            </p>
                            <p>
                                <span class="font-medium text-gray-600">Request Date:</span>
                                <span class="text-gray-900">{{ $selectedTransaction->created_at->format('M d, Y H:i') }}</span>
                            </p>
                        </div>
                    </div>

                    @if($selectedTransaction->remarks)
                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                            <h3 class="font-semibold text-[14px] text-blue-600 mb-1">Borrower Remarks / Message:</h3>
                            <p class="text-gray-700">{{ $selectedTransaction->remarks }}</p>
                        </div>
                    @endif

                    <table class="user-table w-full text-[12px]">
                        <thead>
                            <tr>
                                <th>Asset Code</th>
                                <th>Asset Name</th>
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

    <!-- Approve Confirmation Modal -->
    <div class="modal-backdrop" x-data="{ show: @entangle('showApproveModal') }" x-show="show">
        <div class="modal" x-on:click.away="$wire.showApproveModal = false">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Approval</h2>
                <button wire:click="$set('showApproveModal', false)" class="modal-close">&times;</button>
            </div>
            
            <div class="modal-body">                
                <div class="bg-blue-50 border-l-4 border-blue-500 p-3 sm:p-4 rounded-md shadow-sm mb-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 text-sm text-blue-800 leading-tight">
                            Are you sure you want to approve this borrow request?
                            <strong class="block font-medium mt-0.5">This action will notify the borrower and move the request forward.</strong>
                        </div>
                    </div>
                </div>

                <div class="text-center p-3 sm:p-4 bg-white rounded-md border border-gray-200 shadow-sm">
                    <p class="text-sm text-gray-700 mb-2 leading-snug">
                        Borrow Code: 
                        <strong class="text-gray-900">{{ $selectedTransaction->borrow_code ?? 'N/A' }}</strong>
                    </p>

                    <div class="mt-3 text-left">
                        <label for="approve-remarks" class="block text-xs font-medium text-gray-700 mb-1">
                            Remarks <span class="text-gray-400">(Optional)</span>
                        </label>
                        <textarea 
                            id="approve-remarks" 
                            wire:model="approveRemarks" 
                            rows="3" 
                            class="w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm leading-tight resize-none"
                            placeholder="Add any remarks for the borrower..."
                        ></textarea>
                    </div>
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
                    wire:click="approveRequest" 
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium py-1.5 px-4 rounded text-sm transition duration-150 ease-in-out"
                >
                    <span wire:loading.class.add="hidden">
                        <i class="fas fa-check mr-2"></i> Confirm
                    </span>
                    <span wire:loading.class.remove="hidden" class="hidden flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Deny Confirmation Modal -->
    <div class="modal-backdrop" x-data="{ show: @entangle('showDenyModal') }" x-show="show">
        <div class="modal" x-on:click.away="$wire.showDenyModal = false">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Rejection</h2>
                <button wire:click="$set('showDenyModal', false)" class="modal-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="bg-red-50 border-l-4 border-red-500 p-3 sm:p-4 rounded-md shadow-sm mb-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 text-sm text-red-800 leading-tight">
                            Are you sure you want to reject this borrow request?
                            <strong class="block font-medium mt-0.5">This action is final and will notify the requester immediately.</strong>
                        </div>
                    </div>
                </div>

                <!-- Main Rejection Content -->
                <div class="text-center p-3 sm:p-4 bg-white rounded-md border border-gray-200 shadow-sm">
                    <p class="text-sm text-gray-700 mb-2 leading-snug">
                        Borrow Code: 
                        <strong class="text-gray-900">{{ $selectedTransaction->borrow_code ?? 'N/A' }}</strong>
                    </p>

                    <div class="mt-3 text-left">
                        <label for="deny-remarks" class="block text-xs font-medium text-gray-700 mb-1">
                            Reason for Rejection <span class="text-red-500">(Required)</span>
                        </label>
                        <textarea 
                            id="deny-remarks" 
                            wire:model="denyRemarks" 
                            rows="3" 
                            required
                            class="w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500 text-sm leading-tight resize-none"
                            placeholder="Please provide a reason for rejection..."
                        ></textarea>
                        @error('denyRemarks') 
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>


            </div>
            
            <div class="modal-footer flex justify-end space-x-3 mt-4">
                <button 
                    wire:click="$set('showDenyModal', false)" 
                    class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white font-medium py-1.5 px-4 rounded text-sm transition duration-150 ease-in-out"
                >
                    Cancel
                </button>

                <button 
                    wire:click="denyRequest" 
                    :disabled="!$wire.denyRemarks"
                    class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-4 rounded text-sm transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-ban mr-2"></i> Reject
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openPdf', (borrowCode) => {
            if (borrowCode) {
                window.open(`/borrow-pdf/${borrowCode}`, '_blank');
            }
        });
    });
</script>