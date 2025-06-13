<!-- resources/views/livewire/super-admin/approve-return-requests.blade.php -->
<div>
    <div class="superadmin-container">
        <h1 class="page-title main-title">Approve Return Requests</h1>
        
        <!-- Real-time polling -->
        <div wire:poll.5s>
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
        </div>

        <!-- Search Bar -->
        <div class="search-bar mb-6 w-full md:w-1/3 relative">
            <input 
                type="text" 
                placeholder="Search codes, name..." 
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
                        <th>Return Code</th>
                        <th>Returnee</th>
                        <th>Borrow Code</th>
                        <th>Returned At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returnRequests as $request)
                        @php
                            // Get first item to represent the return request
                            $firstItem = \App\Models\AssetReturnItem::with('borrowItem.transaction')
                                ->where('return_code', $request->return_code)
                                ->first();
                        @endphp
                        <tr>
                            <td data-label="Return Code" class="text-center">{{ $request->return_code }}</td>
                            <td data-label="Returnee" class="text-center">
                                {{ $request->returnedBy->name ?? 'N/A' }}
                            </td>
                            <td data-label="Borrow Code" class="text-center">
                                {{ $firstItem->borrowItem->transaction->borrow_code ?? 'N/A' }}
                            </td>
                            <td data-label="Returned At" class="text-center">
                                {{ $request->returned_at->format('M d, Y H:i') }}
                            </td>
                            <td data-label="Status" class="text-center">
                                <span class="status-badge {{ strtolower($request->status) }}">
                                    {{ $request->status }}
                                </span>
                            </td>
                            <td data-label="Actions" class="text-center">
                                <button 
                                    wire:click="showDetails('{{ $request->return_code }}')"
                                    class="view-btn bg-blue-500 hover:bg-blue-600 text-white py-1 px-2 rounded-md transition mr-1"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($request->status === 'Pending')
                                    <button 
                                        wire:click="confirmApprove('{{ $request->return_code }}')"
                                        class="approve-btn bg-green-500 hover:bg-green-600 text-white py-1 px-2 rounded-md transition mr-1"
                                    >
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button 
                                        wire:click="confirmDeny('{{ $request->return_code }}')"
                                        class="deny-btn bg-red-500 hover:bg-red-600 text-white py-1 px-2 rounded-md transition"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-software-row">No return requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="mt-4 pagination-container">
                {{ $returnRequests->links() }}
            </div>
        </div>

        <!-- Details Modal -->
        @if($showDetailsModal && $selectedReturn)
            <div class="modal-backdrop" x-data="{ show: @entangle('showDetailsModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showDetailsModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Return Details: {{ $selectedReturn->first()->return_code }}</h2>
                        <button wire:click="$set('showDetailsModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="font-medium">Returnee:</p>
                                <p>{{ $selectedReturn->first()->returnedBy->name }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Department:</p>
                                <p>{{ $selectedReturn->first()->returnedBy->department->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Borrow Code:</p>
                                <p>{{ $selectedReturn->first()->borrowItem->transaction->borrow_code }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Return Date:</p>
                                <p>{{ $selectedReturn->first()->returned_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Asset Code</th>
                                    <th>Asset Name</th>
                                    <th>Quantity</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedReturn as $item)
                                    <tr>
                                        <td data-label="Asset Code" class="text-center">
                                            {{ $item->borrowItem->asset->asset_code }}
                                        </td>
                                        <td data-label="Asset Name" class="text-center">
                                            {{ $item->borrowItem->asset->name }}
                                        </td>
                                        <td data-label="Quantity" class="text-center">
                                            {{ $item->borrowItem->quantity }}
                                        </td>
                                        <td data-label="Remarks" class="text-center">
                                            {{ $item->remarks ?: 'N/A' }}
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
        @if($showApproveModal && $selectedReturn)
            <div class="modal-backdrop" x-data="{ show: @entangle('showApproveModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showApproveModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Approve Return Request</h2>
                        <button wire:click="$set('showApproveModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="text-center p-6">
                            <p class="text-lg mb-4">Are you sure you want to approve this return request?</p>
                            <p class="text-danger font-bold">Return Code: <strong>{{ $selectedReturn->first()->return_code }}</strong></p>
                            <p class="mt-4">This will restock the assets and mark them as available.</p>
                            
                            <div class="mt-6">
                                <label for="approve-remarks" class="block text-sm font-medium text-gray-700 mb-1">
                                    Remarks (Optional)
                                </label>
                                <textarea 
                                    id="approve-remarks" 
                                    wire:model="approveRemarks" 
                                    rows="3" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Add any remarks for this approval..."
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
                            class="btn btn-success ml-4"
                        >
                            Confirm Approval
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Deny Confirmation Modal -->
        @if($showDenyModal && $selectedReturn)
            <div class="modal-backdrop" x-data="{ show: @entangle('showDenyModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showDenyModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Deny Return Request</h2>
                        <button wire:click="$set('showDenyModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="text-center p-6">
                            <p class="text-lg mb-4">Are you sure you want to deny this return request?</p>
                            <p class="text-danger font-bold">Return Code: <strong>{{ $selectedReturn->first()->return_code }}</strong></p>
                            
                            <div class="mt-6">
                                <label for="deny-remarks" class="block text-sm font-medium text-gray-700 mb-1">
                                    Reason for Denial (Required)
                                </label>
                                <textarea 
                                    id="deny-remarks" 
                                    wire:model="denyRemarks" 
                                    rows="3" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Explain why this return is being denied..."
                                    required
                                ></textarea>
                                @error('denyRemarks') <span class="error">{{ $message }}</span> @enderror
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
                        >
                            Confirm Denial
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>