<x-layouts.app>
    <div class="content">
        <div class="content-card">
            <h1 class="text-xl font-bold mb-4 text-center">Manage User Accounts</h1>

            <div class="user-table-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td data-label="ID">{{ $user->id }}</td>
                                <td data-label="Name">{{ $user->name }}</td>
                                <td data-label="Username">{{ $user->username }}</td>
                                <td data-label="Email">{{ $user->email }}</td>
                                <td data-label="Role">{{ $user->role->name ?? 'N/A' }}</td>

                                <!-- Status dropdown -->
                                <td data-label="Status">
                                    <form action="{{ route('superadmin.manage.status', $user) }}" method="POST">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="status-dropdown">
                                            <option value="Pending" {{ $user->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="Approved" {{ $user->status === 'Approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="Blocked" {{ $user->status === 'Blocked' ? 'selected' : '' }}>Blocked</option>
                                        </select>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td data-label="Actions" class="action-buttons">
                                    <a href="{{ route('superadmin.manage.show', $user) }}" class="view-button">View</a>
                                    <a href="{{ route('superadmin.manage.edit', $user) }}" class="edit-button">Edit</a>

                                    <!-- Delete button triggers modal -->
                                    <button
                                        @click="openModal('deleteModal-{{ $user->id }}')"
                                        class="delete-button"
                                        type="button"
                                    >Delete</button>

                                    <!-- Delete Modal -->
                                    <div
                                        x-data="{ open: false }"
                                        x-show="open"
                                        x-init="window.openModal = (id) => { if(id === 'deleteModal-{{ $user->id }}') { open = true; } }"
                                        @keydown.escape.window="open = false"
                                        style="display: none;"
                                        class="modal-backdrop"
                                    >
                                        <div class="modal" @click.away="open = false">
                                            <h2>Confirm Delete</h2>
                                            <p>Delete user <strong>{{ $user->name }}</strong>? This cannot be undone.</p>

                                            <form method="POST" action="{{ route('superadmin.manage.destroy', $user) }}">
                                                @csrf
                                                @method('DELETE')

                                                <label for="password-{{ $user->id }}">Your password:</label>
                                                <input
                                                    id="password-{{ $user->id }}"
                                                    name="password"
                                                    type="password"
                                                    required
                                                    autocomplete="current-password"
                                                >

                                                <div class="modal-actions">
                                                    <button type="button" @click="open = false" class="btn btn-secondary">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
