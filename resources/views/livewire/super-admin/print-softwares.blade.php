<div class="superadmin-container">
    <h1 class="page-title main-title">Software Printing</h1>

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
    <div class="card mb-6">
        <div class="card-body">
            <form wire:submit.prevent="printSoftwares">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="dateFrom" class="form-label">Date From</label>
                        <input 
                            type="date" 
                            id="dateFrom" 
                            class="form-input"
                            wire:model="dateFrom"
                        >
                        @error('dateFrom') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="dateTo" class="form-label">Date To</label>
                        <input 
                            type="date" 
                            id="dateTo" 
                            class="form-input"
                            wire:model="dateTo"
                        >
                        @error('dateTo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <button 
                    type="submit" 
                    class="btn-generate inline-flex items-center px-4 py-2 border border-transparent text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 rounded-md text-sm font-medium transition"
                >
                    <i class="fas fa-print mr-2"></i> Generate PDF
                </button>
            </form>
        </div>
    </div>

    <!-- Print Logs Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title text-center">Print History</h3>
        </div>
        <div class="card-body">
            <!-- Search Bar -->
            <div class="search-bar mb-6 w-full md:w-1/3 relative">
                <input 
                    type="text" 
                    placeholder="Search by print code..." 
                    wire:model.live.debounce.300ms="search"
                    class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                        &times;
                    </button>
                @else
                    <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                @endif
            </div>

            <!-- Print Logs Table -->
            <div class="overflow-x-auto">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Print Code</th>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th>Printed At</th>
                            <th>Printed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($printLogs as $log)
                            <tr>
                                <td data-label="Print Code" class="text-center">
                                    {{ $log->print_code }}
                                </td>
                                <td data-label="Date From" class="text-center">
                                    {{ \Carbon\Carbon::parse($log->date_from)->format('M d, Y') }}
                                </td>
                                <td data-label="Date To" class="text-center">
                                    {{ \Carbon\Carbon::parse($log->date_to)->format('M d, Y') }}
                                </td>
                                <td data-label="Printed At" class="text-center">
                                    {{ $log->created_at->format('M d, Y H:i') }}
                                </td>
                                <td data-label="Printed By" class="text-center">
                                    {{ $log->user->name }}
                                </td>
                                <td data-label="Actions" class="text-center space-x-2">
                                    <button 
                                        wire:click="printAgain({{ $log->id }})"
                                        class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded text-sm transition duration-150 ease-in-out"
                                    >
                                        <i class="fas fa-print mr-1"></i> Print
                                    </button>

                                    <button 
                                        wire:click="confirmDelete({{ $log->id }})"
                                        class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 px-3 rounded text-sm transition duration-150 ease-in-out"
                                    >
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="no-software-row">No print history found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div class="mt-4 pagination-container">
                    {{ $printLogs->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal-backdrop" style="z-index: 1000;" x-data="{ show: @entangle('showDeleteModal') }" x-show="show">
            <div class="modal" x-on:click.away="$wire.showDeleteModal = false">
                <div class="modal-header">
                    <h2 class="modal-title">Confirm Deletion</h2>
                    <button wire:click="$set('showDeleteModal', false)" class="modal-close">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium mb-2">Are you sure you want to delete this print log?</h3>
                        <p class="mb-4">This action cannot be undone. All associated records will be permanently removed.</p>
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
                        <i class="fas fa-trash mr-2"></i> 
                        <span wire:loading.remove>Delete Log</span>
                        <span wire:loading>Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>