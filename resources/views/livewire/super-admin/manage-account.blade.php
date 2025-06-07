<div>   

    <div class="superadmin-container">       

        
        <!-- USER TABLE -->
        <div class="user-table-container">
            <!-- Success Message -->
            @if ($successMessage)
                <div class="success-message" 
                     x-data="{ show: true }" 
                     x-show="show"
                     x-init="setTimeout(() => show = false, 5000)">
                    {{ $successMessage }}
                </div>
            @endif

            <!-- SEARCH BAR -->
            <div class="search-bar w-full lg:w-1/3">
                <input                
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search by name or username..."
                    class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-5"
                />
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
                            <td data-label="Actions" class="action-buttons">
                                <button wire:click="viewUser({{ $user->id }})" class="view-button">View</button>
                                <button wire:click="editUser({{ $user->id }})" class="edit-button">Edit</button>
                                <button wire:click="confirmDelete({{ $user->id }})" class="delete-button">Delete</button>
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
                        <button wire:click="deleteUser" class="btn btn-danger">Confirm</button>
                        <button wire:click="$set('confirmingUserDeletion', false)" class="btn btn-secondary">Cancel</button>
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
                }, 5000);
            });
        });
    </script>
</div>
