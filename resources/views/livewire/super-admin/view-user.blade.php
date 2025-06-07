<div class="relative z-10 w-full max-w-7xl mx-auto min-h-screen flex flex-col items-center px-4 py-8">
    <div class="mt-1 w-full max-w-[52rem] p-8 bg-white/80 dark:bg-gray-800/70 backdrop-blur-md rounded-lg transition-all">
        <!-- Header with title and close button -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                User Details: {{ $user->name }}
            </h2>
            <button wire:click="closeView" 
                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm transition">
                <i class="fas fa-times mr-1"></i> Close
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column - User Information -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 mb-4">
                        Account Information
                    </h3>
                    
                    <!-- User Details Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Name -->
                        <div class="col-span-2">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $userDetails['name'] }}</p>
                        </div>
                        
                        <!-- Username -->
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Username</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $userDetails['username'] }}</p>
                        </div>
                        
                        <!-- Role -->
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $userDetails['role'] }}</p>
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                {{ $userDetails['status'] === 'Approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                {{ $userDetails['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                {{ $userDetails['status'] === 'Blocked' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                {{ $userDetails['status'] }}
                            </span>
                        </div>
                        
                        <!-- Email -->
                        <div class="col-span-2">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $userDetails['email'] }}</p>
                        </div>
                        
                        <!-- Contact Number -->
                        <div class="flex flex-col justify-start">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Number</p>
                            <p class="text-justify text-gray-900 dark:text-white font-medium">
                                {{ $userDetails['contact_number'] }}
                            </p>
                        </div>
                        
                        <!-- Created At -->
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Created</p>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $userDetails['created_at'] }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Address Section -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 mb-4">
                        Address
                    </h3>
                    <p class="text-gray-900 dark:text-white">
                        {{ $userDetails['address'] }}
                    </p>
                </div>
            </div>
            
            <!-- Right Column - Profile Photo and Stats -->
            <div class="space-y-6 flex flex-col h-full">
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 mb-4">
                        Profile Photo
                    </h3>
                    
                    <div class="flex flex-col items-center">
                        <div class="relative">
                            @if($user->profile_photo_path)
                                <img src="{{ $userDetails['profile_photo'] }}" 
                                    alt="{{ $user->name }}" 
                                    class="w-64 h-64 rounded-lg object-cover border-4 border-white shadow-lg">
                            @else
                                <div class="w-64 h-64 rounded-lg bg-gray-200 border-4 border-white flex items-center justify-center">
                                    <i class="fas fa-user-circle text-9xl text-gray-400"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Account Stats -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6 flex-1 flex flex-col justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 border-b pb-3 mb-4">
                        Account Statistics
                    </h3>
                    
                    <div class="grid gap-4">
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-200">12</p>
                            <p class="text-sm text-blue-600 dark:text-blue-300">Borrowed</p>
                        </div>
                        
                        <div class="text-center p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                            <p class="text-2xl font-bold text-green-700 dark:text-green-200">47</p>
                            <p class="text-sm text-green-600 dark:text-green-300">Returned</p>
                        </div>
                        
                        <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg">
                            <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-200">8</p>
                            <p class="text-sm text-yellow-600 dark:text-yellow-300">Penalty</p>
                        </div>                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
