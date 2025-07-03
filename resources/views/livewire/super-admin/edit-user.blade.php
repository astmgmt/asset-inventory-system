<div class="relative z-10 w-full max-w-7xl mx-auto min-h-screen flex flex-col items-center px-4 py-8">
    <form wire:submit.prevent="openPasswordModal"
        class="mt-1 w-full max-w-[52rem] space-y-6 p-8 bg-white/80 dark:bg-gray-800/70 backdrop-blur-md rounded-lg transition-all">
        
        <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-4">
            User: {{ $user->name }}
        </h2>

        <!-- Success Message -->
        @if ($successMessage)
            <div class="success-message" 
                    x-data="{ show: true }" 
                    x-show="show"
                    x-init="setTimeout(() => show = false, 3000)">
                {{ $successMessage }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                <!-- Profile Photo -->
                <div class="flex flex-col items-center">
                    <div class="relative group">
                        @if($temp_profile_photo)
                            <img src="{{ $temp_profile_photo }}" 
                                class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                        @elseif($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" 
                                alt="{{ $user->name }}" 
                                class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                        @else
                            <div class="w-32 h-32 rounded-full bg-gray-200 border-4 border-white flex items-center justify-center">
                                <i class="fas fa-user-circle text-6xl text-gray-400"></i>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-black bg-opacity-40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-white text-sm font-medium">Change</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 w-full max-w-xs">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Profile Photo
                        </label>
                        <input 
                            type="file" 
                            wire:model="profile_photo"
                            class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100
                                dark:file:bg-blue-900 dark:file:text-blue-100 dark:hover:file:bg-blue-800"
                        >
                        @error('profile_photo') 
                            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-2">
                        Contact Information
                    </h3>
                    
                    <!-- Contact Number -->
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Contact Number
                        </label>
                        <input 
                            id="contact_number" 
                            type="text" 
                            wire:model="contact_number"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                        @error('contact_number') 
                            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Address
                        </label>
                        <textarea 
                            id="address" 
                            wire:model="address"
                            rows="3"
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        ></textarea>
                        @error('address') 
                            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Account Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-2">
                        Account Information
                    </h3>
                    
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Full Name
                        </label>
                        <input 
                            id="name" 
                            type="text" 
                            wire:model="name"
                            required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                        @error('name') 
                            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Username
                        </label>
                        <input 
                            id="username" 
                            type="text" 
                            wire:model="username"
                            required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                        @error('username') 
                            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email
                        </label>
                        <input 
                            id="email" 
                            type="email" 
                            wire:model="email"
                            required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                        @error('email') 
                            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <!-- Role -->
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Role
                        </label>
                        <select 
                            id="role_id" 
                            wire:model="role_id"
                            required
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id') 
                            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Department -->
                    <div>
                        <label for="departmentSearch" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Department
                        </label>
                        <div class="relative">
                            <input 
                                id="departmentSearch"
                                type="text" 
                                wire:model.live="departmentSearch"
                                wire:keydown.escape="showDepartmentDropdown = false"
                                placeholder="Search or add department"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                autocomplete="off"
                                wire:click="showDepartmentDropdown = true"
                            />
                            @if($showDepartmentDropdown)
                                <div class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto dark:bg-gray-700 dark:border-gray-600">
                                    @foreach($departmentSuggestions as $dept)
                                        <div 
                                            wire:click="selectDepartment('{{ $dept }}')"
                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-gray-600 dark:text-white"
                                        >
                                            {{ $dept }}
                                        </div>
                                    @endforeach
                                    @if(!in_array($departmentSearch, $departmentSuggestions) && $departmentSearch)
                                        <div 
                                            wire:click="selectDepartment('{{ $departmentSearch }}')"
                                            class="px-4 py-2 text-blue-500 hover:bg-blue-100 cursor-pointer dark:text-blue-300 dark:hover:bg-gray-600"
                                        >
                                            Add "{{ $departmentSearch }}"
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @error('department_id') 
                            <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                        @enderror
                        
                        <input type="hidden" wire:model="department_id" />
                    </div>


                </div>
                
                <!-- Actions -->
                <div class="pt-6 flex flex-col sm:flex-row justify-end gap-4">
                    <a href="{{ route('superadmin.manage') }}"
                        class="px-6 py-3 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors text-center flex items-center justify-center gap-2">
                        <i class="fas fa-times"></i>
                        Close
                    </a>
                    <button type="submit"
                        class="px-6 py-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-800 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-pen-to-square"></i>
                        Update
                    </button>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Password Confirmation Modal -->
    @if ($showPasswordModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    Confirm Changes
                </h2>
                
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Please enter your password to confirm the changes to this user account.
                </p>
                
                <div class="mb-4">
                    <label for="superAdminPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Your Password
                    </label>
                    <input 
                        id="superAdminPassword"
                        type="password"
                        wire:model="superAdminPassword"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Super Admin Password"
                        autocomplete="current-password"
                    >
                    @error('superAdminPassword') 
                        <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                    @enderror
                </div>
                
                <div class="flex justify-end gap-3 pt-2">
                    <button 
                        wire:click="closePasswordModal"
                        class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 flex items-center gap-2"
                    >
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button 
                        wire:click="updateUser"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-800 flex items-center gap-2"
                    >
                        <span wire:loading.class.add="hidden" class="flex items-center gap-2">
                            <i class="fas fa-check"></i>
                            Confirm
                        </span>
                        <span wire:loading.class.remove="hidden" class="hidden flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Updating...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

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