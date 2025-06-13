<div class="superadmin-container">    
    <h1 class="page-title main-title">Transaction History</h1>
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

        <!-- Search Bar -->
        <div class="search-bar mb-6 w-full md:w-1/3 relative">
            <input 
                type="text" 
                placeholder="Search by code, status..." 
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

        <!-- History Table -->
        <div class="overflow-x-auto">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Borrow Code</th>
                        <th>Return Code</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $record)
                        <tr>
                            <td data-label="Borrow Code" class="text-center">
                                {{ $record->borrow_code ?? 'N/A' }}
                            </td>
                            <td data-label="Return Code" class="text-center">
                                {{ $record->return_code ?? 'N/A' }}
                            </td>
                            <td data-label="Status" class="text-center">
                                @php
                                    $returnCode = $record->return_code ?? '';
                                    $status = $record->status;
                                    $statusClass = strtolower(str_replace(' ', '-', $status));
                                    
                                    // Show "Approved Return" when return code exists and isn't 'N/A'
                                    if ($returnCode && $returnCode !== 'N/A') {
                                        $status = 'Approved Return';
                                        $statusClass = 'approved-return';
                                    }
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td data-label="Date" class="text-center">
                                {{ $record->action_date->format('M d, Y') }}
                            </td>
                            <td data-label="Actions" class="text-center">
                                <button 
                                    wire:click="showDetails({{ $record->id }})"
                                    class="view-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md transition mr-1"
                                >
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button 
                                    wire:click="confirmDelete({{ $record->id }})"
                                    class="delete-btn bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded-md transition"
                                >
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="no-software-row">No transaction history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="mt-4 pagination-container">
                {{ $history->links() }}
            </div>
        </div>

        <!-- Details Modal -->
        @if($showDetailsModal && $selectedHistory)
            <div class="modal-backdrop" x-data="{ show: @entangle('showDetailsModal') }" x-show="show">
                <div class="modal max-w-6xl" x-on:click.away="$wire.showDetailsModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Transaction Details</h2>
                        <button wire:click="$set('showDetailsModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Borrow Details -->
                            <div class="borrow-details">
                                <h3 class="text-lg font-semibold mb-3">Borrow Details</h3>
                                @if($selectedHistory->borrow_data)
                                    <div class="overflow-x-auto">
                                        <table class="details-table text-xs">
                                            <thead>
                                                <tr>
                                                    <th>Asset Code</th>
                                                    <th>Asset Name</th>
                                                    <th>Quantity</th>
                                                    <th>Purpose</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedHistory->borrow_data['borrow_items'] as $item)
                                                    <tr>
                                                        <td>{{ $item['asset']['asset_code'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['asset']['name'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['quantity'] }}</td>
                                                        <td>{{ $item['purpose'] ?: 'N/A' }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        <p>No borrow data available</p>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Return Details -->
                            <div class="return-details">
                                <h3 class="text-lg font-semibold mb-3">Return Details</h3>
                                @if($selectedHistory->return_data)
                                    <div class="overflow-x-auto">
                                        <table class="details-table text-xs">
                                            <thead>
                                                <tr>
                                                    <th>Asset Code</th>
                                                    <th>Asset Name</th>
                                                    <th>Quantity</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedHistory->return_data as $item)
                                                    <tr>
                                                        <td>{{ $item['borrow_item']['asset']['asset_code'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['borrow_item']['asset']['name'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['borrow_item']['quantity'] }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y') }}</td>
                                                        <td>
                                                            <span class="status-badge {{ strtolower($item['status']) }}">
                                                                {{ $item['status'] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="fas fa-undo text-4xl mb-3"></i>
                                        <p>Not Yet Returned</p>
                                    </div>
                                @endif
                            </div>
                        </div>
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

        <!-- Delete Confirmation Modal -->
        <div class="modal-backdrop" x-data="{ show: @entangle('showDeleteModal') }" x-show="show">
            <div class="modal" x-on:click.away="$wire.showDeleteModal = false">
                <div class="modal-header">
                    <h2 class="modal-title">Confirm Deletion</h2>
                    <button wire:click="$set('showDeleteModal', false)" class="modal-close">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="text-center p-6">
                        <p class="text-lg mb-4">Are you sure you want to delete this history record?</p>
                        <p class="text-danger font-bold">This action cannot be undone!</p>
                        <p class="mt-4">Borrow Code: <strong>{{ $selectedHistory->borrow_code ?? 'N/A' }}</strong></p>
                        <p class="mt-2">Return Code: <strong>{{ $selectedHistory->return_code ?? 'N/A' }}</strong></p>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button 
                        wire:click="$set('showDeleteModal', false)" 
                        class="btn btn-secondary"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="deleteHistory" 
                        class="btn btn-danger ml-4"
                    >
                        Yes, Delete Record
                    </button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .details-table {
            width: 100%;
            font-size: 11px;
        }

        .details-table th, 
        .details-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
        }

        .details-table th {
            background-color: #f7fafc;
        }

        .borrow-details, 
        .return-details {
            max-height: 400px;
            overflow-y: auto;
        }

        .status-badge {
            @apply px-2 py-1 rounded-full text-xs font-medium;
        }

        .status-badge.approved-borrow,
        .status-badge.approved-return {
            @apply bg-green-100 text-green-800;
        }

        .status-badge.rejected-borrow,
        .status-badge.rejected-return {
            @apply bg-red-100 text-red-800;
        }

        .status-badge.pending-return {
            @apply bg-yellow-100 text-yellow-800;
        }
    </style>
</div>

