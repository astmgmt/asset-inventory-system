<x-layouts.guest>
    <div class="login-gradient-bg w-full overflow-y-auto min-h-screen">

        <div class="relative z-10 w-full max-w-6xl flex flex-col items-center mt-12 mb-16">
            <div class="logo-container flex justify-center mb-4">
                <img src="{{ asset('images/company.png') }}" alt="Custom Logo" class="max-h-48 max-w-48 md:max-h-36 md:max-w-36 object-contain" />
            </div>
            
            @if (session()->has('successMessage'))
                <div x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 5000)"
                    class="mb-4 w-[95%] md:w-[70%] xl:w-[50%] bg-green-200 border border-green-400 text-green-900 px-4 py-3 rounded shadow-md text-sm font-medium"
                    role="alert">
                    {{ session('successMessage') }}
                </div>
            @endif

            @if($error)
                <div x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 5000)"
                    class="mb-4 w-[95%] md:w-[70%] xl:w-[50%] bg-red-200 border border-red-400 text-red-900 px-4 py-3 rounded shadow-md text-sm font-medium"
                    role="alert">
                    {{ $errorMessage }}
                </div>
            @endif

            <form wire:submit.prevent="submit"
                  class="login-form-glass w-[95%] md:w-[70%] xl:w-[50%] space-y-4 p-6 md:p-6 rounded-xl shadow-lg backdrop-blur-sm bg-white/80 dark:bg-gray-800/60 border border-white/30">

                <h2 class="text-xl font-bold text-gray-800 dark:text-white text-center">Contact Us</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300 text-center mb-2">
                    Have questions or need assistance? Send us a message below.
                </p>

                <div>
                    <x-label for="name" value="Your Name" />
                    <x-input id="name" name="name" type="text"
                             wire:model="name"
                             class="block mt-1 w-full text-base py-2"
                             placeholder="Enter your name"
                             required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="email" value="Your Email" />
                    <x-input id="email" name="email" type="email"
                             wire:model="email"
                             class="block mt-1 w-full text-base py-2"
                             placeholder="Enter your email"
                             required />
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="subject" value="Subject" />
                    <x-input id="subject" name="subject" type="text"
                             wire:model="subject"
                             class="block mt-1 w-full text-base py-2"
                             placeholder="Enter subject"
                             required />
                    @error('subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <x-label for="message" value="Message" />
                    <textarea id="message" name="message" rows="10"
                              wire:model="message"
                              class="block mt-1 w-full text-base py-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                              placeholder="Enter your message"
                              required></textarea>
                    @error('message') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 pt-2">
                    <a href="{{ route('login') }}"
                       class="w-full sm:w-auto text-sm px-4 py-2 text-center font-medium rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                        ← Back to Login
                    </a>

                    <button 
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="w-full sm:w-auto text-base px-6 py-2 text-center rounded-md bg-indigo-600 text-white hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 transition inline-flex items-center justify-center gap-2"
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
        <style>
            @media screen and (max-width: 1366px) {
                .logo-container {
                    margin: 10rem auto;
                }
            }
        </style>        
    </div>    
</x-layouts.guest>