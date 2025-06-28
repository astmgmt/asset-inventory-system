<div class="superadmin-container">    
    <h1 class="page-title main-title">Asset Tracking</h1>
    
    <!-- Search Bar -->
    <div class="search-bar mb-6 w-full md:w-1/3 relative">
        <input 
            type="text" 
            placeholder="Search name, dept, code..." 
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
                    <th>Borrower</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Borrow Code</th>
                    <th>Return Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $history)
                    <tr>
                        <td data-label="Borrower">
                            {{ $history->user->name }}
                        </td>
                        <td data-label="Role">
                            {{ $history->user->role->name ?? 'N/A' }}
                        </td>
                        <td data-label="Department">
                            {{ $history->user->department->name ?? 'N/A' }}
                        </td>
                        <td data-label="Borrow Code">
                            {{ $history->borrow_code ?? 'N/A' }}
                        </td>
                        <td data-label="Return Code">
                            @if ($this->isHiddenReturnCode($history->return_code))
                                Not Yet Returned
                            @else
                                {{ $history->return_code ?? 'N/A' }}
                            @endif
                        </td>
                        <td data-label="Actions">
                            <div class="flex justify-center space-x-2">
                                <button 
                                    wire:click="showDetails({{ $history->id }})"
                                    class="view-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md transition"
                                >
                                    <i class="fas fa-eye"></i> View
                                </button>
                                
                                <button 
                                    wire:click="generatePdf({{ $history->id }})" 
                                    class="bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded-md transition"
                                >
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-software-row">No asset history records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="mt-4 pagination-container">
            {{ $histories->links() }}
        </div>
    </div>

    <!-- Details Modal -->
    @if($showDetailsModal && $selectedHistory)
        <div class="modal-backdrop" x-data="{ show: @entangle('showDetailsModal') }" x-show="show">
            <div class="modal max-w-6xl" x-on:click.away="$wire.showDetailsModal = false">
                <div class="modal-header">
                    <h2 class="modal-title">Asset Transaction Details</h2>
                    <div class="flex items-center">                            
                        <button wire:click="$set('showDetailsModal', false)" class="modal-close">&times;</button>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @php
                            // Directly access the array data (no decoding needed)
                            $borrowItems = $selectedHistory->borrow_data['borrow_items'] ?? [];
                            $returnItems = $selectedHistory->return_data['return_items'] ?? [];
                        @endphp

                        <!-- Borrow Details -->
                        <div class="borrow-details">
                            <h3 class="text-lg font-semibold mb-3">Borrow Details</h3>
                            @if(count($borrowItems))
                                <div class="overflow-x-auto">
                                    <table class="details-table">
                                        <thead>
                                            <tr>
                                                <th>Asset Code</th>
                                                <th>Brand</th>
                                                <th>Model</th>
                                                <th>Qty</th>
                                                <th>Purpose</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($borrowItems as $item)
                                                <tr>
                                                    <td>{{ $item['asset']['asset_code'] ?? 'N/A' }}</td>
                                                    <td>{{ $item['asset']['name'] ?? 'N/A' }}</td>
                                                    <td>{{ $item['asset']['model_number'] ?? 'N/A' }}</td>
                                                    <td>{{ $item['quantity'] ?? 'N/A' }}</td>
                                                    <td>{{ $item['purpose'] ?? 'N/A' }}</td>
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
                                    <table class="details-table">
                                        <thead>
                                            <tr>
                                                <th>Asset Code</th>
                                                <th>Brand</th>
                                                <th>Model</th>
                                                <th>Qty</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($returnItems as $item)
                                                <tr>
                                                    <td>{{ $item['borrow_item']['asset']['asset_code'] ?? 'N/A' }}</td>
                                                    <td>{{ $item['borrow_item']['asset']['name'] ?? 'N/A' }}</td>
                                                    <td>{{ $item['borrow_item']['asset']['model_number'] ?? 'N/A' }}</td>
                                                    <td>{{ $item['borrow_item']['quantity'] ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="status-badge {{ $item['status'] === 'Returned' ? 'status-good' : 'status-damaged' }}">
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
</div>

