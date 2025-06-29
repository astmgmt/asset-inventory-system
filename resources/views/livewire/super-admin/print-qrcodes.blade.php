<div class="superadmin-container">
    <h1 class="page-title main-title">Asset QR Code Printing</h1>

    <!-- Generating Message -->
    <div wire:loading.delay class="success-message mb-4">
        @if($successMessage)
            {{ $successMessage }}
        @else
            <span w-full>Generating PDF for download, please wait...</span>
        @endif
    </div>

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

    <!-- Print Form Card -->
    <div class="section-wrapper shadow-md rounded-lg p-6 mb-6 border border-gray-200">
        <form wire:submit.prevent="printQRCodes" class="space-y-6">
            <div>
                <label class="block text-sm font-semibold mb-2">Filter Options</label>
                <div class="flex items-center space-x-6">
                    <label class="inline-flex items-center text-sm">
                        <input 
                            type="radio" 
                            wire:model.live="filterOption" 
                            value="by_date"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        >
                        <span class="ml-2">By Date</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input 
                            type="radio" 
                            wire:model.live="filterOption" 
                            value="select_all"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                        >
                        <span class="ml-2">Select All</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="dateFrom" class="block text-sm font-medium mb-1">Date From</label>
                    <input 
                        type="date" 
                        id="dateFrom" 
                        class="form-input block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 @if($filterOption == 'select_all') opacity-50 cursor-not-allowed @endif"
                        wire:model="dateFrom"
                        @if($filterOption == 'select_all') disabled @endif
                    >
                    @error('dateFrom') 
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>
                <div>
                    <label for="dateTo" class="block text-sm font-medium mb-1">Date To</label>
                    <input 
                        type="date" 
                        id="dateTo" 
                        class="form-input block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 @if($filterOption == 'select_all') opacity-50 cursor-not-allowed @endif"
                        wire:model="dateTo"
                        @if($filterOption == 'select_all') disabled @endif
                    >
                    @error('dateTo') 
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button 
                    type="submit" 
                    class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition"
                >
                    <i class="fas fa-qrcode mr-2"></i> Generate QR Codes
                </button>
            </div>
        </form>
    </div>


    <!-- Print Logs Card -->
    <div class="section-wrapper shadow-md rounded-lg border border-gray-200 p-6">
        <div class="mb-6 text-center">
            <h3 class="text-xl font-semibold">QR Code Print History</h3>
        </div>

        <!-- Search Bar -->
        <div class="mb-6 w-full md:w-1/3 relative">
            <input 
                type="text" 
                placeholder="Search by print code..." 
                wire:model.live.debounce.300ms="search"
                class="form-input w-full px-4 py-2 pr-10 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            @if($search)
                <button 
                    wire:click="$set('search', '')" 
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 text-lg"
                    aria-label="Clear search"
                >
                    &times;
                </button>
            @else
                <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="user-table min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-center font-semibold">Print Code</th>
                        <th class="px-4 py-3 text-center font-semibold">Date From</th>
                        <th class="px-4 py-3 text-center font-semibold">Date To</th>
                        <th class="px-4 py-3 text-center font-semibold">Printed At</th>
                        <th class="px-4 py-3 text-center font-semibold">Printed By</th>
                        <th class="px-4 py-3 text-center font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($printLogs as $log)
                        <tr>
                            <td data-label="Print Code" class="px-4 py-2">{{ $log->print_code }}</td>
                            <td data-label="Date From" class="px-4 py-2">
                                {{ $log->date_from ? \Carbon\Carbon::parse($log->date_from)->format('M d, Y') : 'All' }}
                            </td>
                            <td data-label="Date To" class="px-4 py-2">
                                {{ $log->date_to ? \Carbon\Carbon::parse($log->date_to)->format('M d, Y') : 'All' }}
                            </td>
                            <td data-label="Printed At" class="px-4 py-2">{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td data-label="Printed By" class="px-4 py-2">{{ $log->user->name }}</td>
                            <td data-label="Actions" class="px-4 py-2 text-center">
                                <div class="flex flex-col items-center space-y-2">
                                    <button 
                                        wire:click="printAgain({{ $log->id }})"
                                        class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium transition mb-1"
                                    >
                                        <i class="fas fa-print mr-1"></i> Print
                                    </button>
                                    <button 
                                        wire:click="confirmDelete({{ $log->id }})"
                                        class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-sm font-medium transition"
                                    >
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">No print history found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $printLogs->links() }}
        </div>
    </div>


    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
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
                                Are you sure you want to delete this print log?
                                <strong class="font-medium">This action cannot be undone. All associated records will be permanently removed.</strong>
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
                        wire:click="deletePrintLog" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-6 rounded text-sm transition duration-150 ease-in-out"
                    >
                        <i class="fas fa-trash-alt mr-2"></i> 
                        <span wire:loading.remove>Confirm</span>
                        <span wire:loading>Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>