<div>  
    <div class="superadmin-container">               
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
                <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>             
            </div>


            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th>
                        <th class="actions-column">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td data-label="ID">{{ $user->id }}</td>
                            <td data-label="Name">{{ $user->name }}</td>
                            <td data-label="Username">{{ $user->username }}</td>
                            <td data-label="Email">{{ $user->email }}</td>
                            <td data-label="Role">{{ $user->role->name ?? 'N/A' }}</td>
                            <td data-label="Status">
                                <select
                                    wire:change="updateStatus({{ $user->id }}, $event.target.value)"
                                    class="status-dropdown"
                                >
                                    <option value="Approved" {{ $user->status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Pending" {{ $user->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Blocked" {{ $user->status === 'Blocked' ? 'selected' : '' }}>Blocked</option>
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
                <div class="modal">
                    <h2 class="modal-title">Confirm Deletion</h2>
                    <p class="modal-text">Enter your password to confirm deletion of this account.</p>

                    <input
                        type="password"
                        wire:model.defer="password"
                        class="modal-input mb-4"
                        placeholder="Super Admin Password"
                    />

                    @error('password')
                        <div class="flex items-center gap-2 mt-2 mb-4 text-sm text-red-700 bg-red-100 p-2 rounded-md">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 5c-3.866 0-7 3.134-7 7s3.134 7 7 7 7-3.134 7-7-3.134-7-7-7z"/>
                            </svg>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="modal-actions mt-2">
                        <div class="flex items-center space-x-4 mt-6">
                            <button
                                wire:click="deleteUser"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                            >
                                <i class="fas fa-trash-alt mr-2"></i>
                                Confirm
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
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('clear-message', () => {
                setTimeout(() => {
                    @this.set('successMessage', '');
                }, 3000);
            });
        });
    </script>
</div>