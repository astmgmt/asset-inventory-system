<div class="superadmin-container">
    <h1 class="page-title main-title">Pending Borrow Requests</h1>
    
    <!-- Success Message -->
    @if ($successMessage)
        <div class="success-message mb-4" 
            x-data="{ show: true }" 
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)">
            {{ $successMessage }}
        </div>
    @endif

    <!-- Error Message -->
    @if ($errorMessage)
        <div class="error-message mb-4" 
            x-data="{ show: true }" 
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Search Bar -->
    <div class="search-bar mb-6 w-full md:w-1/3 relative">
        <input 
            type="text" 
            placeholder="Search by borrow code, borrower, department..." 
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
                        <th>Borrowed At</th>
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
                            <td data-label="Borrowed At" class="text-center">
                                {{ $transaction->borrowed_at ? $transaction->borrowed_at->format('M d, Y H:i') : 'N/A' }}
                            </td>
                            <td data-label="Actions" class="text-center">
                                <button 
                                    wire:click="showDetails({{ $transaction->id }})"
                                    class="view-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md transition"
                                >
                                    <i class="fas fa-eye"></i> Details
                                </button>
                                
                                <button 
                                    wire:click="confirmApprove({{ $transaction->id }})"
                                    class="approve-btn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-md ml-2 transition"
                                >
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                
                                <button 
                                    wire:click="confirmDeny({{ $transaction->id }})"
                                    class="deny-btn bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md ml-2 transition"
                                >
                                    <i class="fas fa-times"></i> Deny
                                </button>
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
                
                <div class="modal-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <h3 class="font-semibold">Borrower Information</h3>
                            <p><strong>Name:</strong> {{ $selectedTransaction->user->name }}</p>
                            <p><strong>Department:</strong> {{ $selectedTransaction->userDepartment->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold">Request Information</h3>
                            <p><strong>Requested By:</strong> {{ $selectedTransaction->requestedBy->name ?? 'N/A' }}</p>
                            <p><strong>Borrow Date:</strong> {{ $selectedTransaction->borrowed_at ? $selectedTransaction->borrowed_at->format('M d, Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <table class="user-table">
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
                <div class="text-center p-4">
                    <p class="text-lg mb-4">Are you sure you want to approve this borrow request?</p>
                    <p class="mb-4">Borrow Code: <strong>{{ $selectedTransaction->borrow_code ?? 'N/A' }}</strong></p>
                    
                    <div class="mt-4">
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
            </div>
            
            <div class="modal-footer">
                <button 
                    wire:click="$set('showApproveModal', false)" 
                    class="btn btn-secondary"
                >
                    Cancel
                </button>
                <button 
                    wire:click="approveRequest" 
                    class="btn btn-danger ml-4"
                >
                    <i class="fas fa-check mr-2"></i> Yes, Approve
                </button>
            </div>
        </div>
    </div>

    <!-- Deny Confirmation Modal -->
    <div class="modal-backdrop" x-data="{ show: @entangle('showDenyModal') }" x-show="show">
        <div class="modal" x-on:click.away="$wire.showDenyModal = false">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Denial</h2>
                <button wire:click="$set('showDenyModal', false)" class="modal-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="text-center p-4">
                    <p class="text-lg mb-4">Are you sure you want to deny this borrow request?</p>
                    <p class="mb-4">Borrow Code: <strong>{{ $selectedTransaction->borrow_code ?? 'N/A' }}</strong></p>
                    
                    <div class="mt-4">
                        <label for="deny-remarks" class="block text-sm font-medium text-gray-700 mb-1">
                            Reason for Denial (Required)
                        </label>
                        <textarea 
                            id="deny-remarks" 
                            wire:model="denyRemarks" 
                            rows="3" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            placeholder="Please provide a reason for denial..."
                            required
                        ></textarea>
                        @error('denyRemarks') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button 
                    wire:click="$set('showDenyModal', false)" 
                    class="btn btn-secondary"
                >
                    Cancel
                </button>
                <button 
                    wire:click="denyRequest" 
                    class="btn btn-danger ml-4"
                    :disabled="!$wire.denyRemarks"
                >
                    <i class="fas fa-ban mr-2"></i> Confirm Denial
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openPdf', (borrowCode) => {
            if (borrowCode) {
                console.log('Received borrowCode:', borrowCode);
                window.open(`/borrow-pdf/${borrowCode}`, '_blank');
            } else {
                console.error("Missing borrowCode from Livewire event.");
            }
        });
    });
</script>

