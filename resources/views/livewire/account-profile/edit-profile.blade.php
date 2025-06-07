<div class="relative z-10 w-full max-w-7xl mx-auto min-h-screen flex flex-col items-center px-4 py-8">
    <form wire:submit.prevent="updateProfile"
        class="mt-1 w-full max-w-[52rem] space-y-6 p-8 bg-white/80 dark:bg-gray-800/70 backdrop-blur-md rounded-lg transition-all">
        
        <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-4">
            Edit Your Profile
        </h2>

        <!-- Success Message -->
        @if ($successMessage)
            <div class="success-message" 
                 x-data="{ show: true }" 
                 x-show="show"
                 x-init="setTimeout(() => show = false, 3000)">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $successMessage }}
            </div>
        @endif

        <!-- Error Message -->
        @if ($errorMessage)
            <div class="error-message" 
                 x-data="{ show: true }" 
                 x-show="show"
                 x-init="setTimeout(() => show = false, 3000)">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5c-3.866 0-7 3.134-7 7s3.134 7 7 7 7-3.134 7-7-3.134-7-7-7z"></path>
                </svg>
                {{ $errorMessage }}
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
                            Username <span class="text-blue-600">(permanent and cannot be changed)</span>
                        </label>
                        <input 
                            id="username" 
                            type="text" 
                            wire:model="username"
                            disabled
                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        >
                        @error('email') 
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
                    
                    <!-- Password Section -->
                    <div class="pt-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-2">
                            Change Password
                        </h3>
                        
                        <div class="mt-4 space-y-4">
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Current Password
                                </label>
                                <input 
                                    id="current_password" 
                                    type="password" 
                                    wire:model="current_password"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Leave blank to keep current password"
                                >
                                @error('current_password') 
                                    <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                                @enderror
                            </div>
                            
                            <!-- New Password -->
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    New Password
                                </label>
                                <input 
                                    id="new_password" 
                                    type="password" 
                                    wire:model="new_password"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Enter new password"
                                >
                                @error('new_password') 
                                    <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> 
                                @enderror
                            </div>
                            
                            <!-- Confirm New Password -->
                            <div>
                                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Confirm New Password
                                </label>
                                <input 
                                    id="new_password_confirmation" 
                                    type="password" 
                                    wire:model="new_password_confirmation"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    placeholder="Confirm new password"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="pt-6 flex flex-col sm:flex-row justify-end gap-4">
                    <a href="{{ route('dashboard.superadmin') }}"
                    class="inline-flex items-center px-6 py-3 rounded-lg bg-gray-200 text-gray-800 text-sm font-medium hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition"
                    >
                        <i class="fas fa-arrow-left mr-2"></i> Cancel
                    </a>

                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-800 transition"
                    >
                        <i class="fas fa-save mr-2"></i> Update
                    </button>
                </div>

            </div>
        </div>
    </form>
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