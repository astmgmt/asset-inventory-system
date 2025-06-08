<x-layouts.guest>
    <div class="login-gradient-bg w-full">      

        <div class="relative z-10 w-full max-w-7xl flex flex-col items-center">
            <!-- Logo -->
            <div class="flex justify-center mb-1">
                <img src="{{ asset('images/company.png') }}" alt="Custom Logo" class="h-60 w-60 object-contain" />
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="w-[90%] md:w-[40%] mb-4">
                    <div class="bg-red-200 border border-red-400 text-red-900 px-4 py-3 rounded shadow-md text-sm font-medium">
                        <x-validation-errors />
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 w-[90%] md:w-[40%] bg-green-200 border border-green-400 text-green-900 px-4 py-3 rounded shadow-md text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            {{-- SUCCESS REGISTRATION AND WAITING FOR APPROVAL MESSAGE --}}
            @if(session('info'))
                <div class="mb-4 px-4 py-3 rounded bg-blue-100 text-blue-800 border border-blue-300" role="alert">
                    {{ session('info') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form-glass w-[90%] md:w-[40%] space-y-4 p-6 rounded-xl shadow-lg backdrop-blur-sm bg-white/70 dark:bg-gray-800/60 border border-white/30">
                @csrf

                <!-- Email or Username -->
                <div>
                    <x-label for="login" value="{{ __('Email or Username') }}" />
                    <x-input id="login" name="login" type="text"
                             class="block mt-1 w-full text-lg py-3"
                             :value="old('login')" required autofocus autocomplete="username" />
                </div>

                <!-- Password -->
                <div>
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" name="password" type="password"
                             class="block mt-1 w-full text-lg py-3"
                             required autocomplete="current-password" />
                </div>

                <!-- Remember + Forgot -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    {{-- <label for="remember_me" class="flex items-center text-sm mb-2 sm:mb-0">
                        <x-checkbox id="remember_me" name="remember" />
                        <span class="ml-2 text-gray-600">{{ __('Remember me') }}</span>
                    </label> --}}

                    @if (Route::has('password.request'))
                        <a class="text-sm text-indigo-600 hover:underline" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>            

                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-4">
                    <x-button
                        type="submit"
                        class="w-full sm:w-auto text-lg px-6 py-3 text-center normal-case rounded-md bg-indigo-600 text-white hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 transition inline-flex items-center justify-center">
                        {{ __('Login') }}
                    </x-button>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                        class="w-full sm:w-auto text-sm px-6 py-3 text-center font-bold normal-case rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition inline-flex items-center justify-center">
                            {{ __('Register') }}
                        </a>
                    @endif
                </div>
           
            </form>
        </div>
    </div>
</x-layouts.guest>
