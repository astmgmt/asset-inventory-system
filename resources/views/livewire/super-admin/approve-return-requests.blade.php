<div class="superadmin-container">
    <h1 class="page-title main-title">Approve Return Requests</h1>

    <div>
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
                placeholder="Search code, borrower..." 
                wire:model.live.debounce.300ms="search"
                class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                name="search"
                id="search-input"
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
                            <td data-label="Actions" class="text-center">
                                <div class="flex justify-center space-x-2">
                                    <button 
                                        wire:click="openApproveModal({{ $transaction->id }})"
                                        class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded text-sm transition duration-150 ease-in-out mb-1"
                                    >
                                        <i class="fas fa-check mr-1"></i> Details
                                    </button>

                                    <button 
                                        wire:click="openRejectModal({{ $transaction->id }})"
                                        class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-3 rounded text-sm transition duration-150 ease-in-out mb-1"
                                    >
                                        <i class="fas fa-times mr-1"></i> Reject
                                    </button>
                                </div>
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
            <div class="modal-backdrop" style="z-index: 1000;" x-data="{ show: @entangle('showApproveModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showApproveModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Approve Return: {{ $selectedTransaction->borrow_code }}</h2>
                        <button wire:click="$set('showApproveModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="bg-green-50 border-l-4 border-green-500 p-3 sm:p-4 rounded-md shadow-sm mb-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 pt-0.5">
                                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 text-sm text-green-800 leading-tight">
                                    Borrow request is ready for approval.
                                    <strong class="block font-medium mt-0.5">Please confirm the borrower's details and asset list below.</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Borrower Info (Inline & Compact) -->
                        <div class="mb-3 text-md leading-tight">
                            <span class="font-medium">Borrower:</span>
                            <span class="font-semibold text-green-600">{{ $selectedTransaction->user->name }}</span>
                        </div>

                        <!-- Asset Table -->
                        <div class="overflow-x-auto rounded-md border border-gray-200 shadow-sm mb-4">
                            <table class="min-w-full text-sm text-left text-gray-700">
                                <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                                    <tr>
                                        <th class="px-4 py-2 text-center">Asset Code</th>
                                        <th class="px-4 py-2 text-center">Brand</th>
                                        <th class="px-4 py-2 text-center">Model</th>
                                        <th class="px-4 py-2 text-center">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @foreach($approveBorrowItems as $item)
                                        <tr class="border-t text-center">
                                            <td class="px-4 py-2">{{ $item->asset->asset_code }}</td>
                                            <td class="px-4 py-2">{{ $item->asset->name }}</td>
                                            <td class="px-4 py-2">{{ $item->asset->model_number }}</td>
                                            <td class="px-4 py-2 text-center">{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @php
                            $returnRemarks = collect();
                            foreach ($selectedTransaction->borrowItems as $borrowItem) {
                                foreach ($borrowItem->returnItems as $returnItem) {
                                    if ($returnItem->approval_status === 'Pending' && $returnItem->remarks) {
                                        $returnRemarks->push($returnItem->remarks);
                                    }
                                }
                            }
                            $uniqueRemarks = $returnRemarks->unique();
                        @endphp
                        
                        @if($uniqueRemarks->isNotEmpty())
                            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
                                <h3 class="font-semibold text-[14px] text-green-600 mb-1">User Remarks / Message:</h3>
                                @foreach($uniqueRemarks as $remark)
                                    <p class="text-gray-700 mb-2 last:mb-0 text-[14px]">{{ $remark }}</p>
                                @endforeach
                            </div>
                        @else
                            <div class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-md">
                                <p class="text-gray-500 italic">No remarks provided by the borrower for this return</p>
                            </div>
                        @endif

                        <!-- Remarks Section -->
                        <div class="mt-4 text-left">
                            <label for="approve-remarks" class="block text-xs font-medium mb-1">
                                Admin Remarks <span class="text-gray-400">(Optional)</span>
                            </label>
                            <textarea 
                                id="approve-remarks" 
                                wire:model="approveRemarks" 
                                rows="3" 
                                name="approveRemarks"
                                class="w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 text-sm leading-tight resize-none"
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
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium py-1.5 px-4 rounded text-sm transition duration-150 ease-in-out"
                        >
                            <span wire:loading.class.add="hidden">
                                <i class="fas fa-check mr-2"></i> Approve Return
                            </span>
                            <span wire:loading.class.remove="hidden" class="hidden flex items-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Reject Modal -->
        @if($showRejectModal && $selectedTransaction)
            <div class="modal-backdrop" style="z-index: 1001;" x-data="{ show: @entangle('showRejectModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showRejectModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Reject Return: {{ $selectedTransaction->borrow_code }}</h2>
                        <button wire:click="$set('showRejectModal', false)" class="modal-close">&times;</button>
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
                                    Rejecting this borrow request will notify the borrower and cancel the transaction.
                                    <strong class="block font-medium mt-0.5">Please review the items and provide a reason below.</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 text-sm leading-tight">
                            <span class="font-medium">Borrower:</span>
                            <span class="font-medium text-red-600">{{ $selectedTransaction->user->name }}</span>
                        </div>

                        <div class="overflow-x-auto rounded-md border border-gray-200 shadow-sm mb-4">
                            <table class="min-w-full text-sm text-left text-gray-700">
                                <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                                    <tr>
                                        <th class="px-4 py-2 text-center">Asset Code</th>
                                        <th class="px-4 py-2 text-center">Brand</th>
                                        <th class="px-4 py-2 text-center">Model</th>
                                        <th class="px-4 py-2 text-center">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @foreach($rejectBorrowItems as $item)
                                        <tr class="border-t">
                                            <td class="px-4 py-2 text-center">{{ $item->asset->asset_code }}</td>
                                            <td class="px-4 py-2 text-center">{{ $item->asset->name }}</td>
                                            <td class="px-4 py-2 text-center">{{ $item->asset->model_number }}</td>
                                            <td class="px-4 py-2 text-center">{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-left">
                            <label for="reject-remarks" class="block text-xs font-medium mb-1">
                                Reason for Rejection <span class="text-red-500">(Required)</span>
                            </label>
                            <textarea 
                                id="reject-remarks" 
                                wire:model="rejectRemarks" 
                                rows="3" 
                                name="rejectRemarks"
                                required
                                class="w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500 text-sm leading-tight resize-none"
                                placeholder="Explain why this return is being rejected..."
                            ></textarea>
                            @error('rejectRemarks') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
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
                            wire:click="openRejectConfirmModal" 
                            class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-4 rounded text-sm transition duration-150 ease-in-out"
                        >
                            <i class="fas fa-times mr-2"></i> Reject Return
                        </button>
                    </div>

                </div>
            </div>
        @endif

        <!-- Reject Confirmation Modal -->
        @if($showRejectConfirmModal)
            <div class="modal-backdrop" style="z-index: 1002;" x-data="{ show: @entangle('showRejectConfirmModal') }" x-show="show">
                <div class="modal" x-on:click.away="$wire.showRejectConfirmModal = false">
                    <div class="modal-header">
                        <h2 class="modal-title">Confirm Rejection</h2>
                        <button wire:click="$set('showRejectConfirmModal', false)" class="modal-close">&times;</button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium mb-2">Are you sure you want to reject this return?</h3>
                            <p class="mb-4">This action cannot be undone. The assets will remain borrowed.</p>
                        </div>
                    </div>
                    
                    <div class="modal-footer flex justify-center space-x-4 mt-4">
                        <button 
                            wire:click="$set('showRejectConfirmModal', false)" 
                            class="inline-flex items-center bg-gray-500 hover:bg-gray-600 text-white font-medium py-1.5 px-6 rounded text-sm transition duration-150 ease-in-out"
                        >
                            Cancel
                        </button>

                        <button 
                            wire:click="rejectReturn" 
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-6 rounded text-sm transition duration-150 ease-in-out"
                        >
                            <span wire:loading.class.add="hidden">
                                <i class="fas fa-times mr-2"></i> Confirm Rejection
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
</div>