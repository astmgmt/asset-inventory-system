<div class="superadmin-container">
    <h1 class="page-title main-title">Software Assignment</h1>
    
    <!-- Success Message -->
    @if ($successMessage)
        <div class="success-message mb-4" 
            x-data="{ show: true }" 
            x-show="show"
            x-init="setTimeout(() => {
                show = false;
                setTimeout(() => @this.set('successMessage', ''), 300);
            }, 3000)">
            {{ $successMessage }}
        </div>
    @endif

    <!-- Error Message -->
    @if ($errorMessage)
        <div class="error-message mb-4" 
            x-data="{ show: true }" 
            x-show="show"
            x-init="setTimeout(() => {
                show = false;
                setTimeout(() => @this.set('errorMessage', ''), 300);
            }, 5000)">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Action Bar -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="search-bar w-full md:w-1/3">
            <input                
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Search assignments..."
                class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />   
            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>             
        </div>
        
        <button wire:click="openAddModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md shadow-sm inline-flex items-center transition-colors duration-200">
            <i class="fas fa-plus mr-2"></i> Assign Software
        </button>
    </div>

    <!-- Assignments Table -->
    <div class="overflow-x-auto">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Ref. No.</th>
                    <th>Software Code</th>
                    <th>Software Name</th>
                    <th>Assignee</th>
                    <th>Assigned By</th>
                    <th>Date Assigned</th>
                    <th class="actions-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td data-label="Ref. No." class="text-center">{{ $assignment->reference_no }}</td>
                        <td data-label="Software Code" class="text-center">{{ $assignment->software->software_code }}</td>
                        <td data-label="Software Name" class="text-center">{{ $assignment->software->software_name }}</td>
                        <td data-label="Assignee" class="text-center">{{ $assignment->user->name }}</td>
                        <td data-label="Assigned By" class="text-center">{{ $assignment->admin->name }}</td>
                        <td data-label="Date Assigned" class="text-center">
                            {{ $assignment->date_assigned->format('M d, Y') }}
                        </td>
                        <td data-label="Actions" class="text-center">
                            <div class="flex justify-center gap-3">
                                <button wire:click="openViewModal({{ $assignment->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button wire:click="openEditModal({{ $assignment->id }})" class="text-yellow-500 hover:text-yellow-600 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $assignment->id }})" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-assignment-row">No assignments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
        
    <!-- Pagination -->
    <div class="mt-4 pagination-container">
        {{ $assignments->links() }}
    </div>

    <!-- Add Assignment Modal -->
    @if ($showAddModal)
        <div class="modal-backdrop">
            <div class="modal">
                <h2 class="modal-title">Assign Software</h2>
                
                <form wire:submit.prevent="createAssignment">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Assignee *</label>
                            <select wire:model="user_id" class="form-input">
                                <option value="">Select Assignee</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Assigned By</label>
                            <select wire:model="admin_id" class="form-input" disabled>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ $admin->id == auth()->id() ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('admin_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Software *</label>
                            <select wire:model="software_id" class="form-input">
                                <option value="">Select Software</option>
                                @foreach($softwareList as $software)
                                    <option value="{{ $software->id }}">
                                        {{ $software->software_code }} - {{ $software->software_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('software_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Date Assigned *</label>
                            <input type="date" wire:model="date_assigned" class="form-input">
                            @error('date_assigned') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group col-span-2">
                            <label>Purpose *</label>
                            <textarea wire:model="purpose" rows="3" class="form-input" placeholder="Purpose of assignment"></textarea>
                            @error('purpose') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group col-span-2">
                            <label>Remarks</label>
                            <textarea wire:model="remarks" rows="2" class="form-input" placeholder="Additional remarks"></textarea>
                            @error('remarks') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="submit" class="btn btn-primary btn-update">
                            <i class="fas fa-user-check"></i> Assign
                        </button>
                        <button type="button" wire:click="closeModals" class="btn btn-secondary">
                            <i class="fas fa-times-circle"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Edit Assignment Modal -->
    @if ($showEditModal)
        <div class="modal-backdrop">
            <div class="modal">
                <h2 class="modal-title">Edit Assignment</h2>
                
                <form wire:submit.prevent="updateAssignment">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Assignee *</label>
                            <select wire:model="user_id" class="form-input">
                                <option value="">Select Assignee</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $user->id == $user_id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Assigned By</label>
                            <select wire:model="admin_id" class="form-input">
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ $admin->id == $admin_id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('admin_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Software *</label>
                            <select wire:model="software_id" class="form-input">
                                <option value="">Select Software</option>
                                @foreach($softwareList as $software)
                                    <option value="{{ $software->id }}" {{ $software->id == $software_id ? 'selected' : '' }}>
                                        {{ $software->software_code }} - {{ $software->software_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('software_id') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Date Assigned *</label>
                            <input type="date" wire:model="date_assigned" class="form-input">
                            @error('date_assigned') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group col-span-2">
                            <label>Purpose *</label>
                            <textarea wire:model="purpose" rows="3" class="form-input" placeholder="Purpose of assignment"></textarea>
                            @error('purpose') <span class="error">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-group col-span-2">
                            <label>Remarks</label>
                            <textarea wire:model="remarks" rows="2" class="form-input" placeholder="Additional remarks"></textarea>
                            @error('remarks') <span class="error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="submit" class="btn btn-primary btn-update">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <button type="button" wire:click="closeModals" class="btn btn-secondary">
                            <i class="fas fa-times-circle"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- View Assignment Modal -->
    @if ($showViewModal)
        <div class="modal-backdrop">
            <div class="modal">
                <h2 class="modal-title">Assignment Details: {{ $viewAssignment->reference_no ?? '' }}</h2>
                
                @if($viewAssignment)
                <div class="assignment-details-grid">
                    <div class="detail-group">
                        <label>Reference No:</label>
                        <p>{{ $viewAssignment->reference_no }}</p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Software Code:</label>
                        <p>{{ $viewAssignment->software->software_code }}</p>
                    </div>

                    <div class="detail-group">
                        <label>Software Name:</label>
                        <p>{{ $viewAssignment->software->software_name }}</p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Assignee:</label>
                        <p>{{ $viewAssignment->user->name }}</p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Assigned By:</label>
                        <p>{{ $viewAssignment->admin->name }}</p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Date Assigned:</label>
                        <p>{{ $viewAssignment->date_assigned->format('M d, Y') }}</p>
                    </div>
                    
                    <div class="detail-group col-span-2">
                        <label>Purpose:</label>
                        <p>{{ $viewAssignment->purpose }}</p>
                    </div>
                    
                    <div class="detail-group col-span-2">
                        <label>Remarks:</label>
                        <p>{{ $viewAssignment->remarks ?? 'N/A' }}</p>
                    </div>
                </div>
                @endif
                
                <div class="modal-actions">
                    <button type="button" wire:click="closeModals" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>

            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="modal-backdrop">
            <div class="modal modal-delete">
                <h2 class="modal-title">Confirm Deletion</h2>
                <p class="modal-text">Are you sure you want to delete this assignment? This action cannot be undone.</p>
                
                <div class="modal-actions">
                    <button wire:click="deleteAssignment" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                    <button wire:click="closeModals" class="btn btn-secondary">
                        <i class="fas fa-ban"></i> Cancel
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-software-pdf', (event) => {
            window.open(`/software/assignment/pdf/${event.assignmentId}`, '_blank');
        });
    });
</script>