<div class="superadmin-container">
    <h1 class="page-title main-title">Contact Administrators</h1>
    
    @if ($successMessage)
        <div class="success-message mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             class="mb-4 w-full bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="error-message mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             class="mb-4 w-full bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <form wire:submit.prevent="submit">
            <div class="mb-4">
                <label for="subject" class="block text-gray-700 text-sm font-bold mb-2">
                    Subject
                </label>
                <input 
                    type="text" 
                    id="subject"
                    wire:model="subject"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter subject"
                >
                @error('subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6">
                <label for="message" class="block text-gray-700 text-sm font-bold mb-2">
                    Message
                </label>
                <textarea 
                    id="message"
                    wire:model="message"
                    rows="6"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter your message"
                ></textarea>
                @error('message') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline flex items-center gap-2"
                >
                    <span wire:loading.class.add="hidden">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </span>
                    <span wire:loading.class.remove="hidden" class="hidden flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white text-center" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending...
                    </span>
                </button>

            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('clear-message', () => {
            setTimeout(() => {
                @this.set('successMessage', '');
                @this.set('errorMessage', '');
            }, 3000);
        });
    });
</script>