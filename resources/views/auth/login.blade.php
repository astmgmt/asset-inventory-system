<x-layouts.guest>
    <div class="login-gradient-bg w-full">      

        <div class="relative z-10 w-full max-w-7xl flex flex-col items-center">
            <!-- Image -->
            <div class="flex justify-center mb-1">
                <img src="{{ asset('images/company.png') }}" alt="Custom Logo" class="h-60 w-60 md:h-40 md:w-40 object-contain" />
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="w-[90%] md:w-[60%] lg:w-[40%] mb-4">
                    <div class="bg-red-200 border border-red-400 text-red-900 px-4 py-3 rounded shadow-md text-sm font-medium">
                        <x-validation-errors />
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 w-[90%] md:w-[60%] lg:w-[40%] bg-green-200 border border-green-400 text-green-900 px-4 py-3 rounded shadow-md text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mb-4 px-4 py-3 rounded bg-blue-100 text-blue-800 border border-blue-300 w-[90%] md:w-[60%] lg:w-[40%]" role="alert">
                    {{ session('info') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form-glass w-[90%] md:w-[60%] lg:w-[40%] space-y-4 p-6 md:p-4 rounded-xl shadow-lg backdrop-blur-sm bg-white/70 dark:bg-gray-800/60 border border-white/30">
                @csrf

                <!-- Email or Username -->
                <div>
                    <x-label for="login" value="{{ __('Email or Username') }}" />
                    <x-input id="login" name="login" type="text"
                             class="block mt-1 w-full text-lg md:text-base py-3 md:py-2"
                             :value="old('login')" required autofocus autocomplete="username" />
                </div>

                <!-- Password -->
                <div>
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" name="password" type="password"
                             class="block mt-1 w-full text-lg md:text-base py-3 md:py-2"
                             required autocomplete="current-password" />
                </div>

                <!-- Remember + Forgot -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    @if (Route::has('password.request'))
                        <a class="text-sm text-indigo-600 hover:underline" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>            

                <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-4">
                    <button type="submit"
                        class="w-full sm:w-auto text-base px-6 py-3 text-center font-semibold normal-case rounded-md bg-indigo-600 text-white hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 transition inline-flex items-center justify-center">
                        {{ __('Login') }}
                    </button>

                    <a href="{{ route('register') }}"
                    class="w-full sm:w-auto text-base px-6 py-3 text-center font-semibold normal-case rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition inline-flex items-center justify-center">
                        {{ __('Register') }}
                    </a>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('contact') }}"
                        class="text-sm font-semibold text-gray-600 hover:text-indigo-600 underline">
                        Need help? Contact Us
                    </a>
                </div>

           
            </form>
        </div>
    </div>
</x-layouts.guest>
