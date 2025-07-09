<div class="superadmin-container">
    <h1 class="page-title main-title">Software Assignment Tracking</h1>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="search-bar w-full md:w-1/3 relative">
            <input 
                type="text" 
                placeholder="Search asgn. no, user..." 
                wire:model.live.debounce.300ms="search"
                class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            @if($search)
                <button wire:click="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    &times;
                </button>
            @else
                <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Assignment No</th>
                    <th>Assigned To</th>
                    <th>Assigned By</th>
                    <th>Date Assigned</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                    <tr>
                        <td data-label="Assignment No" class="text-center">{{ $batch->assignment_no }}</td>
                        <td data-label="Assigned To" class="text-center">
                            {{ $batch->user->name }}
                        </td>
                        <td data-label="Assigned By" class="text-center">
                            {{ $batch->assignedByUser->name }} 
                        </td>
                        <td data-label="Date Assigned" class="text-center">
                            {{ $batch->date_assigned->format('M d, Y h:i A') }}
                        </td>
                        <td data-label="Status" class="text-center">
                            <span class="status-badge {{ strtolower($batch->status) }} px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                                {{ $batch->status }}
                            </span>
                        </td>
                        <td data-label="Actions" class="text-center space-x-2">
                            <div class="flex justify-center items-center space-x-2">
                                <button 
                                    wire:click="viewBatch({{ $batch->id }})" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-colors duration-200"
                                    title="View Details"
                                >
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button 
                                    wire:click="printBatch({{ $batch->id }})" 
                                    class="bg-green-500 hover:bg-green-600 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-colors duration-200"
                                    title="Print PDF"
                                >
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                            
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-software-row">No assignment records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4 pagination-container">
            {{ $batches->links() }}
        </div>
    </div>

    @if($selectedBatch)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" x-data x-on:keydown.escape.window="close">
            <div class="bg-gray-50 rounded-lg shadow-lg w-full max-w-4xl p-6 relative max-h-[90vh] overflow-y-auto" x-on:click.away="close">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b pb-4 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800">
                        Assignment Details - {{ $selectedBatch->assignment_no }}
                    </h2>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-red-600 text-2xl font-bold focus:outline-none">&times;</button>
                </div>

                {{-- Modal Body --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="asset-details p-4 rounded-lg border">
                        <h3 class="text-sm font-medium mb-1">Assigned To</h3>
                        <p class="font-semibold text-blue-600">{{ $selectedBatch->user->name }}</p>
                        <p class="text-sm text-blue-600">{{ $selectedBatch->user->email }}</p>
                    </div>

                    <div class="asset-details p-4 rounded-lg border">
                        <h3 class="text-sm font-medium mb-1">Assigned By</h3>
                        <p class="font-semibold text-blue-600">{{ $selectedBatch->assignedByUser->name }}</p>
                        <p class="text-sm text-blue-600">{{ $selectedBatch->assignedByUser->email }}</p>
                    </div>

                    <div class="asset-details p-4 rounded-lg border">
                        <h3 class="text-sm font-medium mb-1">Date Assigned</h3>
                        <p class="text-blue-600">{{ $selectedBatch->date_assigned->format('M d, Y h:i A') }}</p>
                    </div>

                    <div class="asset-details p-4 rounded-lg border">
                        <h3 class="text-sm font-medium mb-1">Status</h3>
                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800
                            {{ strtolower($selectedBatch->status) === 'active' ? 'bg-green-500' : 'bg-gray-400' }}">
                            {{ $selectedBatch->status }}
                        </span>
                    </div>

                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="asset-details p-4 rounded-lg border">
                            <h3 class="text-sm font-medium mb-1">Purpose</h3>
                            <p class="text-blue-600">{{ $selectedBatch->purpose }}</p>
                        </div>

                        <div class="asset-details p-4 rounded-lg border">
                            <h3 class="text-sm font-medium mb-1">Remarks</h3>
                            <p class="text-blue-600">{{ $selectedBatch->remarks ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-gray-700 mb-3">Assigned Software</h3>
                <div class="overflow-x-auto mb-6">
                    <table class="user-table w-full text-sm text-left border border-gray-200 rounded">
                        <thead class="bg-gray-100 text-gray-700 font-semibold">
                            <tr>
                                <th class="px-4 py-2 border">Software Code</th>
                                <th class="px-4 py-2 border">Software Name</th>
                                <th class="px-4 py-2 border">License Key</th>
                                <th class="px-4 py-2 border text-center">Quantity</th>
                                <th class="px-4 py-2 border text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedBatch->assignmentItems as $item)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $item->software->software_code ?? 'N/A (Deleted)' }}</td>
                                    <td class="px-4 py-2 border">{{ $item->software->software_name ?? 'N/A (Deleted)' }}</td>
                                    <td class="px-4 py-2 border">{{ $item->software->license_key ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 border text-center">{{ $item->quantity }}</td>
                                    <td class="px-4 py-2 border text-center">
                                        <span class="inline-block px-2 py-1 text-xs font-medium rounded
                                            {{ strtolower($item->status) === 'active' ? 'bg-green-400 text-white' : 'bg-gray-300 text-gray-700' }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Modal Footer --}}
                <div class="flex justify-end mt-4">
                    <button 
                        wire:click="closeModal" 
                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 focus:outline-none"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>