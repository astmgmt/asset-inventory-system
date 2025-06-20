<div class="superadmin-container">    
    <h1 class="page-title main-title">Transaction History</h1>
    <div wire:poll.10s>

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
                    <tr class="text-center">
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
                                    $statusClass = match($record->status) {
                                        'Borrow Approved' => 'approved-borrow',
                                        'Borrow Denied' => 'rejected-borrow',
                                        'Return Approved' => 'approved-return',
                                        'Return Denied' => 'rejected-return',
                                        default => ''
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $record->status }}
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
                <div class="flex items-center">
                    <button 
                        wire:click="generatePdf({{ $selectedHistory->id }})"
                        class="print-btn bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded-md transition mr-2"
                    >
                        <i class="fas fa-print mr-1"></i> Print PDF
                    </button>
                    <button wire:click="$set('showDetailsModal', false)" class="modal-close">&times;</button>
                </div>
            </div>

            <div class="modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                        $borrowItems = $selectedHistory->borrow_data['borrow_items'] ?? [];
                        $returnItems = $selectedHistory->return_data['return_items'] ?? [];

                        $borrowAssetCodes = collect($borrowItems)
                            ->map(fn ($item) => strtoupper(trim($item['asset']['asset_code'] ?? '')))
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                        $returnAssetCodes = collect($returnItems)
                            ->map(fn ($item) => strtoupper(trim($item['borrow_item']['asset']['asset_code'] ?? '')))
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                        $matchedAssetCodes = array_intersect($borrowAssetCodes, $returnAssetCodes);
                    @endphp

                    <!-- Borrow Details -->
                    <div class="borrow-details">
                        <h3 class="text-lg font-semibold mb-3">Borrow Details</h3>
                        @if(count($borrowItems))
                            <div class="overflow-x-auto">
                                <table class="details-table text-xs">
                                    <thead class="thead-center">
                                        <tr class="text-center">
                                            <th>Asset Code</th>
                                            <th>Brand</th>
                                            <th>Model</th>
                                            <th>Qty</th>
                                            <th>Purpose</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($borrowItems as $item)
                                            @php
                                                $asset = $item['asset'] ?? [];
                                                $code = strtoupper(trim($asset['asset_code'] ?? ''));
                                                $isMatched = in_array($code, $matchedAssetCodes);
                                            @endphp
                                            <tr class="{{ $isMatched ? 'text-match font-semibold' : '' }}">
                                                <td>{{ $code ?: 'N/A' }}</td>
                                                <td>{{ $asset['name'] ?? 'N/A' }}</td>
                                                <td>{{ $asset['model_number'] ?? 'N/A' }}</td>
                                                <td>{{ $item['quantity'] ?? 'N/A' }}</td>
                                                <td>{{ $item['purpose'] ?? 'N/A' }}</td>
                                                <td>
                                                    @if(isset($item['created_at']))
                                                        {{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <p>No borrow items found</p>
                            </div>
                        @endif
                    </div>

                    <!-- Return Details -->
                    <div class="return-details">
                        <h3 class="text-lg font-semibold mb-3">Return Details</h3>
                        @if(count($returnItems))
                            <div class="overflow-x-auto">
                                <table class="details-table text-xs">
                                    <thead class="thead-center">
                                        <tr>
                                            <th>Asset Code</th>
                                            <th>Brand</th>
                                            <th>Model</th>
                                            <th>Qty</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($returnItems as $item)
                                            @php
                                                $borrowItem = $item['borrow_item'] ?? [];
                                                $asset = $borrowItem['asset'] ?? [];
                                                $code = strtoupper(trim($asset['asset_code'] ?? ''));
                                                $isMatched = in_array($code, $matchedAssetCodes);
                                            @endphp
                                            <tr class="{{ $isMatched ? 'text-match font-semibold' : '' }}">
                                                <td>{{ $code ?: 'N/A' }}</td>
                                                <td>{{ $asset['name'] ?? 'N/A' }}</td>
                                                <td>{{ $asset['model_number'] ?? 'N/A' }}</td>
                                                <td>{{ $borrowItem['quantity'] ?? 'N/A' }}</td>
                                                <td>
                                                    @if(isset($item['created_at']))
                                                        {{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="status-badge {{ strtolower($item['status'] ?? '') }}">
                                                        {{ $item['status'] ?? 'N/A' }}
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
                <button wire:click="$set('showDetailsModal', false)" class="btn btn-secondary">
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
                        <p class="text-lg mb-4">Do you really want to delete this history record?</p>
                        <p class="text-danger font-bold">This will be deleted permanently from your account!</p>
                        <p class="mt-4">Borrow Code: <strong>{{ $selectedHistory->borrow_code ?? 'N/A' }}</strong></p>
                        <p class="mt-2">Status: <strong>{{ $selectedHistory->status ?? 'N/A' }}</strong></p>
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
                        Yes, Delete Permanently
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

        .details-table th, .details-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .details-table th {
            background-color: #f7fafc;
            text-align: center;
        }
        .borrow-details, .return-details {
            max-height: 400px;
            overflow-y: auto;
        }
        .text-match {
            color: #28a745 !important; /* Bootstrap green */
            font-weight: 600;
            background-color: #e6f4ea !important;
        }
        .status-badge {
            @apply px-2 py-1 rounded-full text-xs font-medium;
        }
        .status-badge.approved-borrow { @apply bg-green-100 text-green-800; }
        .status-badge.rejected-borrow { @apply bg-red-100 text-red-800; }
        .status-badge.approved-return { @apply bg-green-100 text-green-800; }
        .status-badge.rejected-return { @apply bg-red-100 text-red-800; }
    </style>
</div>