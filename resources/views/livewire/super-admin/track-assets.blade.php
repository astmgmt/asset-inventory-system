<div class="superadmin-container">    
    <h1 class="page-title main-title">Asset Tracking</h1>
    
    <!-- Search Bar -->
    <div class="search-bar mb-6 w-full md:w-1/3 relative">
        <input 
            type="text" 
            placeholder="Search assets..." 
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

    <!-- Assets Table -->
    <div class="overflow-x-auto">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Description</th>
                    <th>Serial #</th>
                    <th>Condition</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    <tr>
                        <td data-label="Asset Code">{{ $asset->asset_code }}</td>
                        <td data-label="Asset Name">{{ $asset->name }}</td>
                        <td data-label="Model">{{ $asset->model_number }}</td>
                        <td data-label="Description">{{ $asset->description ?? 'N/A' }}</td>
                        <td data-label="Serial">{{ $asset->serial_number ?? 'N/A' }}</td>
                        <td data-label="Condition">
                            @php
                                $conditionName = strtolower($asset->condition->condition_name);
                                $conditionClass = match($conditionName) {
                                    'defective' => 'bg-red-100 text-red-800',
                                    'new' => 'bg-blue-100 text-blue-800',
                                    'available' => 'bg-green-100 text-green-800',
                                    'borrowed' => 'bg-indigo-100 text-indigo-800',
                                    'disposed' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp

                            <span class="px-3 py-1 text-xs font-semibold rounded-full shadow-sm {{ $conditionClass }}">
                                {{ $asset->condition->condition_name }}
                            </span>
                        </td>
                        <td data-label="Expiry Date">
                            {{ $asset->warranty_expiration->format('M d, Y') }}
                            @if($asset->expiry_status !== 'active')
                                <span class="bg-yellow-100 text-yellow-700 px-2 py-1 text-xs font-semibold rounded-full shadow-sm ml-2">
                                    {{ str_replace('_', ' ', $asset->expiry_status) }}
                                </span>
                            @endif
                        </td>
                        <td data-label="Actions">
                            <button 
                                wire:click="showHistory({{ $asset->id }})"
                                class="view-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md transition"
                            >
                                <i class="fas fa-clipboard-list"></i> Records
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="no-software-row">No assets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="mt-4 pagination-container">
            {{ $assets->links() }}
        </div>
    </div>

    <!-- History Modal -->
    @if($showHistoryModal)
        <div class="modal-backdrop" x-data="{ show: @entangle('showHistoryModal') }" x-show="show">
            <div class="modal max-w-6xl" x-on:click.away="$wire.closeHistoryModal()">
                <div class="modal-header">
                    <h2 class="modal-title">Asset History</h2>
                    <div class="flex items-center">                            
                        <button wire:click="closeHistoryModal" class="modal-close">&times;</button>
                    </div>
                </div>

                <div class="modal-body">

                    <div class="search-bar mb-4 w-full relative">
                        <input 
                            type="text" 
                            placeholder="Search names..." 
                            wire:model.live.debounce.300ms="historySearch"
                            class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        @if($historySearch)
                            <button wire:click="$set('historySearch', '')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                &times;
                            </button>
                        @else
                            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        @endif
                    </div>

                    @if($history && $history->count())
                        <div class="overflow-x-auto">
                            <table class="user-table">
                                <thead>
                                    <tr>
                                        <th>User (Borrower)</th>
                                        <th>Department</th>
                                        <th>Email Address</th>
                                        <th>Borrow Code</th>
                                        <th>Return Code</th>
                                        <th>Borrowed At</th>
                                        <th>Returned At</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $item)
                                        <tr>
                                            <td data-label="User">
                                                {{ $item->transaction->user->name ?? 'N/A' }}
                                            </td>
                                            <td data-label="Department">
                                                {{ $item->transaction->user->department->name ?? 'N/A' }}
                                            </td>
                                            <td data-label="Email">
                                                {{ $item->transaction->user->email ?? 'N/A' }}
                                            </td>
                                            <td data-label="Borrow Code">
                                                {{ $item->transaction->borrow_code }}
                                            </td>
                                            <td data-label="Return Code">
                                                @if($item->returnItem && $item->returnItem->approval_status === 'Approved')
                                                    {{ $item->returnItem->return_code }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td data-label="Borrowed At">
                                                {{ $item->transaction->borrowed_at?->format('M d, Y H:i') ?? 'N/A' }}
                                            </td>
                                            <td data-label="Returned At">
                                                @if($item->returnItem && $item->returnItem->approval_status === 'Approved')
                                                    {{ $item->returnItem->returned_at?->format('M d, Y H:i') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td data-label="Remarks">
                                                {{ $item->returnItem->remarks ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- History Pagination -->
                        <div class="mt-4 pagination-container">
                            {{ $history->links('pagination::bootstrap-4', ['pageName' => 'historyPage']) }}
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-history text-4xl mb-3"></i>
                            <p>No history found for this asset</p>
                        </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button wire:click="closeHistoryModal" class="btn btn-secondary">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif    
</div>