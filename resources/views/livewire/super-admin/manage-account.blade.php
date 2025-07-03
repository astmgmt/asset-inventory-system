<div>  
    <div class="superadmin-container">          
        <h1 class="page-title main-title">Manage Accounts</h1>     
        <!-- USER TABLE -->
        <div class="user-table-container">
            <!-- Success Message -->
            @if ($successMessage)
                <div class="success-message" 
                     x-data="{ show: true }" 
                     x-show="show"
                     x-init="setTimeout(() => show = false, 3000)">
                    {{ $successMessage }}
                </div>
            @endif

            <!-- SEARCH BAR -->
            <div class="relative w-full lg:w-1/3 mb-5">
                <input                
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search by name or username..."
                    class="w-full p-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                
                @if($search)
                    <button 
                        wire:click="clearSearch"
                        class="absolute right-3 top-1/2 -translate-y-1/2 transform text-gray-400 hover:text-gray-600 focus:outline-none"
                        title="Clear search"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                @else
                    <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 transform text-gray-400 pointer-events-none"></i>
                @endif
            </div>


            <table class="user-table">
                <thead>
                    <tr>
                        <th>No.</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td data-label="No.">
                                {{ $loop->index + 1 + ($users->currentPage() - 1) * $users->perPage() }}
                            </td>
                            <td data-label="Name">{{ $user->name }}</td>
                            <td data-label="Username">{{ $user->username }}</td>
                            <td data-label="Email">{{ $user->email }}</td>
                            <td data-label="Role">{{ $user->role->name ?? 'N/A' }}</td>
                            <td data-label="Status">
                                <select
                                    wire:model="userStatusMap.{{ $user->id }}"
                                    wire:change="updateStatus({{ $user->id }}, $event.target.value)"
                                    class="status-dropdown"
                                    wire:key="status-select-{{ $user->id }}"
                                >
                                    <option value="Approved">Approved</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Blocked">Blocked</option>
                                </select>
                            </td>
                            <td data-label="Actions" class="text-center">
                                <div class="flex justify-center gap-3">
                                    <button wire:click="viewUser({{ $user->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="editUser({{ $user->id }})" class="text-yellow-500 hover:text-yellow-600 p-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $user->id }})" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="no-users-row">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>            

            <!-- PAGINATION -->
            <div class="mt-4 pagination-container">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        @if ($confirmingUserDeletion)
            <div class="modal-backdrop">
                <div class="modal modal-delete">
                    <h2 class="text-lg font-semibold mb-2 text-red-700">
                        Confirm Deletion
                    </h2>
                    <div class="bg-danger border-l-4 border-red-500 p-3 sm:p-4 rounded-md shadow-sm mb-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-0.5">
                                <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 text-sm text-red-800 leading-tight">                                
                                Enter your password to permanently delete this account. This action cannot be undone.
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <input
                            type="password"
                            wire:model.defer="password"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500"
                            placeholder="Super Admin Password"
                        />
                        
                        @error('password')
                            <div class="flex items-start gap-2 mt-2 text-sm text-red-700 bg-red-100 p-2 rounded-md">
                                <svg class="w-4 h-4 text-red-600 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01M12 5c-3.866 0-7 3.134-7 7s3.134 7 7 7 7-3.134 7-7-3.134-7-7-7z"/>
                                </svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                    <!-- Action Buttons -->
                    <div class="modal-actions mt-4">
                        <div class="flex items-center space-x-4">
                            <button
                                wire:click="deleteUser"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                            >
                                <span wire:loading.class.add="hidden" class="flex items-center">
                                    <i class="fas fa-trash-alt mr-2"></i>
                                    Confirm
                                </span>
                                <span wire:loading.class.remove="hidden" class="hidden flex items-center">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Deleting...
                                </span>
                            </button>

                            <button
                                wire:click="$set('confirmingUserDeletion', false)"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition"
                            >
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Status Change Confirmation Modal -->
        @if ($confirmingStatusChange)
            <div class="modal-backdrop">
                <div class="modal max-w-md">
                    <h4 class="text-md font-semibold mb-2">
                        Confirm Status Change
                    </h4>
                    
                    <div class="text-center mx-auto p-6 bg-red-50">
                        <i class="fas fa-info-circle text-red-700 text-5xl mb-4"></i>
                        <h4 class="text-md font-semibold mb-3 text-gray-800">Status Update</h4>
                        <p class="text-gray-700 leading-relaxed">
                            {{ $statusMessage }}
                        </p>
                    </div>


                    
                    <div class="modal-actions mt-4">
                        <div class="flex items-center space-x-4">
                            <button
                                wire:click="changeUserStatus"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                            >
                                <span wire:loading.class.add="hidden" class="flex items-center">
                                    <i class="fas fa-check mr-2"></i>
                                    Confirm
                                </span>
                                <span wire:loading.class.remove="hidden" class="hidden flex items-center">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Processing...
                                </span>
                            </button>

                            <button
                                wire:click="cancelStatusChange"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition"
                            >
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('clear-message', () => {
                setTimeout(() => {
                    @this.set('successMessage', '');
                }, 3000);
            });
            
            Livewire.on('userStatusUpdated', (userId) => {
                // Force Livewire to update the UI
                @this.get('userStatusMap');
            });
            
            Livewire.on('userDeleted', (userId) => {
                // Force Livewire to update the UI
                @this.get('userStatusMap');
            });
        });
    </script>
</div>