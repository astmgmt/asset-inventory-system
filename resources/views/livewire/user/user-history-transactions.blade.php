<div class="superadmin-container" wire:poll.10s>
    <h1 class="page-title main-title">Transaction History</h1>

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
                    <th>Remarks</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $record)

                    @php
                        if (Str::startsWith($record->return_code ?? '', 'HIDDEN')) {
                            $record->return_code = 'N/A';
                        }
                    @endphp
                    
                    <tr>
                        <td data-label="Borrow Code" class="text-center">
                            {{ $record->borrow_code ?? 'N/A' }}
                        </td>
                        <td data-label="Return Code" class="text-center">                            
                            @if($record->status === 'Return Denied')
                                N/A
                            @else
                                {{ $record->return_code ?? 'N/A' }}
                            @endif
                        </td>
                        <td data-label="Status" class="text-center">
                            @php
                                $statusClass = match($record->status) {
                                    'Borrow Approved' => 'bg-indigo-100 text-indigo-800',
                                    'Borrow Denied' => 'bg-yellow-100 text-yellow-800',
                                    'Return Approved' => 'bg-green-100 text-green-800',
                                    'Return Denied' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold w-[120px] justify-center {{ $statusClass }}">
                                {{ $record->status }}
                            </span>
                        </td>

                        <td data-label="Remarks" class="text-center">
                            <div class="remarks-container">
                                @php
                                    $remarks = 'N/A';
                                                                                                                    
                                    if ($record->status === 'Borrow Approved') {
                                        $remarks = $record->borrow_data['remarks_from_admin'] ?? "For Return";
                                    } 
                                    elseif ($record->status === 'Return Approved') {
                                        $remarks = $record->return_data['remarks_from_admin'] ?? "Successful Return";
                                    }
                                    
                                    elseif ($record->status === 'Borrow Denied') {
                                        $remarks = $record->borrow_data['remarks'] ?? $record->remarks ?? 'N/A';
                                    }
                                    elseif ($record->status === 'Return Denied') {
                                        $remarks = $record->return_data['remarks'] ?? $record->remarks ?? 'N/A';
                                    }
                                @endphp
                                <div class="truncated-remarks" title="{{ $remarks }}">
                                    {{ \Illuminate\Support\Str::limit($remarks, 25) }}
                                </div>
                            </div>
                        </td>                                               

                        <td data-label="Date" class="text-center">
                            {{ $record->action_date->format('M d, Y') }}
                        </td>
                        <td data-label="Actions" class="text-center">
                            <div class="flex justify-center gap-3">
                                <!-- View Button -->
                                <button 
                                    wire:click="showDetails({{ $record->id }})"
                                    class="w-11 h-11 flex items-center justify-center text-blue-600 hover:text-blue-800 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded-full transition"
                                    title="View"
                                    aria-label="View"
                                >
                                    <i class="fas fa-eye text-sm" aria-hidden="true"></i>
                                </button>

                                @if($this->canPrint($record->status) && $record->status !== 'Return Denied')
                                    <!-- Print Button (active) -->
                                    <button 
                                        wire:click="generateHistoryPdf({{ $record->id }})"
                                        class="w-11 h-11 flex items-center justify-center text-green-600 hover:text-green-800 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-300 rounded-full transition"
                                        title="Print"
                                        aria-label="Print"
                                    >
                                        <i class="fas fa-print text-sm" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <!-- Print Button (disabled) -->
                                    <button 
                                        class="w-11 h-11 flex items-center justify-center text-gray-400 bg-gray-100 cursor-not-allowed rounded-full transition"
                                        title="Cannot print this record"
                                        aria-label="Cannot print this record"
                                        disabled
                                    >
                                        <i class="fas fa-print text-sm" aria-hidden="true"></i>
                                    </button>
                                @endif

                                <!-- Delete Button -->
                                <button 
                                    wire:click="confirmDelete({{ $record->id }})"
                                    class="w-11 h-11 flex items-center justify-center text-red-600 hover:text-red-800 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-300 rounded-full transition"
                                    title="Delete"
                                    aria-label="Delete"
                                >
                                    <i class="fas fa-trash-alt text-sm" aria-hidden="true"></i>
                                </button>
                            </div>
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
                                            <tr>
                                                <th class="text-center">Asset Code</th>
                                                <th class="text-center">Brand</th>
                                                <th class="text-center">Model</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-center">Purpose</th>
                                                <th class="text-center">Date</th>
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
                                                            {{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y H:i A') }}
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
                                                <th class="text-center">Asset Code</th>
                                                <th class="text-center">Brand</th>
                                                <th class="text-center">Model</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-center">Date</th>
                                                {{-- <th class="text-center">Status</th> --}}
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
                                                            {{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y H:i A') }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    {{-- <td>
                                                        <span class="status-badge {{ strtolower($item['status'] ?? '') }}">
                                                            {{ $item['status'] ?? 'N/A' }}
                                                        </span>
                                                    </td> --}}
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
    <div class="modal-backdrop" style="z-index: 1000;" x-data="{ show: @entangle('showDeleteModal') }" x-show="show">
        <div class="modal modal-delete" x-on:click.away="$wire.showDeleteModal = false">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Deletion</h2>
                <button wire:click="$set('showDeleteModal', false)" class="modal-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">    
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Are you sure you want to delete this history record?
                                <strong class="font-medium">This action cannot be undone.</strong>
                            </p>
                            <p class="text-sm text-yellow-700 mt-2">
                                Borrow Code: <strong>{{ $selectedHistory->borrow_code ?? 'N/A' }}</strong>
                            </p>
                            <p class="text-sm text-yellow-700 mt-1">
                                Status: <strong>{{ $selectedHistory->status ?? 'N/A' }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer flex justify-center space-x-4 mt-4">
                <button 
                    wire:click="$set('showDeleteModal', false)" 
                    class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white font-medium py-1.5 px-6 rounded text-sm transition duration-150 ease-in-out"
                >
                    Cancel
                </button>

                <button 
                    wire:click="deleteHistory" 
                    wire:loading.attr="disabled" wire:target="deleteHistory"
                    wire:loading.class="opacity-50 cursor-not-allowed" wire:target="deleteHistory"
                    class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-6 rounded text-sm transition duration-150 ease-in-out"
                >
                    <i class="fas fa-trash-alt mr-2"></i> 
                    <span wire:loading.class.add="hidden" wire:target="deleteHistory">
                        Delete Record
                    </span>
                    <span wire:loading.class.remove="hidden" wire:target="deleteHistory" class="hidden">
                        Deleting...
                    </span>
                </button>
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

        .dark .details-table {
            background-color: #fcfdfd;
            color: #cbd5e0;
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
            color: #28a745 !important; /
            font-weight: 600;
            background-color: #e6f4ea !important;
        }

        .remarks-container {
            max-width: 200px;
            margin: 0 auto;
        }
        
        .truncated-remarks {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .remarks-container {
                max-width: 150px;
            }
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