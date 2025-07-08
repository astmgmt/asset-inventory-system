<div>
    <div class="mb-6">
        <h5 class="text-xl font-bold text-gray-800 mb-2">Contact Users</h5>
        <p class="text-gray-600 mb-4">Send email to users or administrators</p>
    </div>

    @if(count($recipients) >= 5)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4">
            <strong>Recipient Limit:</strong> You can only send email up to 5 addresses only! This is to prevent email spamming.
        </div>
    @endif

    @if ($successMessage)
        <div class="success-message mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => { show = false; $wire.resetMessages(); }, 5000)"
             class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ $successMessage }}</span>
        </div>
    @endif

    @if ($errorMessage)
        <div class="error-message mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => { show = false; $wire.resetMessages(); }, 5000)"
             class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ $errorMessage }}</span>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <form wire:submit.prevent="submit">
            <!-- Recipients Field -->
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    Email To 
                    <span class="text-sm font-normal text-gray-500">
                        (Max 5 recipients)
                    </span>
                </label>
                
                <div class="text-right text-sm mb-1 text-gray-500">
                    {{ count($recipients) }} of 5 selected
                </div>
                
                <!-- Search Input -->
                <div class="relative w-full">
                    <input                
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search by name or email..."
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
                
                <!-- Search Results -->
                @if (count($searchResults) > 0)
                    <div class="z-10 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 max-h-60 overflow-y-auto">
                        <ul>
                            @foreach ($searchResults as $user)
                                <li 
                                    wire:click="selectRecipient('{{ $user['id'] }}', '{{ $user['email'] }}')"
                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                                >
                                    <div class="font-medium">{{ $user['name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $user['email'] }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @error('recipients') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                
                <!-- Selected Recipients -->
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($recipients as $email)
                        <div class="bg-gray-100 border border-gray-300 rounded-full px-3 py-1 flex items-center">
                            {{ $email }}
                            <button 
                                type="button"
                                wire:click="removeRecipient('{{ $email }}')"
                                class="ml-2 text-gray-500 hover:text-gray-700"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Subject Field -->
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Subject</label>
                <input 
                    wire:model="subject" 
                    type="text" 
                    placeholder="Enter subject" 
                    class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                @error('subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Message Field -->
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Message</label>
                <textarea 
                    wire:model="message" 
                    rows="8" 
                    placeholder="Type your message here..." 
                    class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
                @error('message') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end mt-8">
                <button 
                    type="submit" 
                    wire:loading.attr="disabled" wire:target="submit"
                    wire:loading.class="opacity-50 cursor-not-allowed" wire:target="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline flex items-center gap-2"
                >
                    <span wire:loading.class.add="hidden" wire:target="submit">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </span>
                    <span wire:loading.class.remove="hidden" wire:target="submit" class="hidden flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin"></i>
                        Sending...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>