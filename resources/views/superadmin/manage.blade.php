<x-layouts.app>
    <div class="content">
        <div class="content-card">
            <h1 class="text-xl font-bold mb-4 text-center">Manage User Accounts</h1>

            @if (session('success'))
                <div class="w-full max-w-5xl mx-auto mb-4 px-4 py-2 rounded border border-green-300 bg-green-100 text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="w-full max-w-5xl mx-auto mb-4 px-4 py-2 rounded border border-red-300 bg-red-100 text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif


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
                            <tr x-data="{ open: false }">
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
                                        @click="open = true"
                                        class="delete-button"
                                        type="button"
                                    >Delete</button>

                                    <!-- Delete Modal -->
                                    <div
                                        x-show="open"
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

                                                <div class="form-group-inline">
                                                    <label for="password-{{ $user->id }}">Password:</label>
                                                    <input
                                                        id="password-{{ $user->id }}"
                                                        name="password"
                                                        type="password"
                                                        required
                                                        autocomplete="current-password"
                                                    >
                                                </div>

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
