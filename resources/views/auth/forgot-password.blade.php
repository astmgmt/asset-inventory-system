<x-layouts.guest>
    <div class="login-gradient-bg w-full">
        
        <div class="relative z-10 w-full max-w-7xl flex flex-col items-center">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <img src="{{ asset('images/inventory.png') }}" alt="Custom Logo" class="h-48 w-48 object-contain" />
            </div>

            <!-- Status Message -->
            @if (session('status'))
                <div class="mb-4 w-[90%] md:w-[40%] bg-green-200 border border-green-400 text-green-900 px-4 py-3 rounded shadow-md text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Validation Errors -->
            <x-validation-errors class="mb-4 text-gray-800 w-[90%] md:w-[40%]" />

            <form method="POST" action="{{ route('password.email') }}" class="login-form-glass w-[90%] md:w-[40%] space-y-4 p-6 rounded-xl shadow-lg backdrop-blur-sm bg-white/70 dark:bg-gray-800/60 border border-white/30">
                @csrf

                <div>
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" name="email" type="email"
                             class="block mt-1 w-full text-lg py-3"
                             :value="old('email')" required autofocus autocomplete="username" />
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <x-button
                        type="submit"
                        class="w-full sm:w-auto text-lg px-6 py-3 text-center normal-case rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition inline-flex items-center justify-center">
                        {{ __('Email Password Reset Link') }}
                    </x-button>

                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                           class="w-full sm:w-auto text-sm px-6 py-3 text-center font-bold normal-case rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition inline-flex items-center justify-center">
                            {{ __('Back to Login') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
