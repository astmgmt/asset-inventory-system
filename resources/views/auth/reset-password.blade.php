<x-layouts.guest>
    <div class="login-gradient-bg w-full">

        <div class="relative z-10 w-full max-w-7xl flex flex-col items-center">
            <!-- Image -->
            <div class="flex justify-center mb-1">
                <img src="{{ asset('images/company.png') }}" alt="Custom Logo" class="h-60 w-60 object-contain" />
            </div>

            <!-- Status Message -->
            @if (session('status'))
                <div class="mb-4 w-[90%] md:w-[40%] bg-green-200 border border-green-400 text-green-900 px-4 py-3 rounded shadow-md text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Validation Errors -->
             @if ($errors->any())
                <div class="p-4 bg-red-100 text-red-700 rounded-md w-[90%] md:w-[40%] mb-2">
                    <x-validation-errors class="mb-2" />
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="login-form-glass w-[90%] md:w-[40%] space-y-4 p-6 rounded-xl shadow-lg backdrop-blur-sm bg-white/70 dark:bg-gray-800/60 border border-white/30">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email Address -->
                <div>
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" name="email" type="email"
                             class="block mt-1 w-full text-lg py-3"
                             :value="old('email', request()->input('email'))" required autofocus autocomplete="username" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" name="password" type="password"
                             class="block mt-1 w-full text-lg py-3"
                             required autocomplete="new-password" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                    <x-input id="password_confirmation" name="password_confirmation" type="password"
                             class="block mt-1 w-full text-lg py-3"
                             required autocomplete="new-password" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-button type="submit" class="text-lg px-6 py-3 text-center normal-case rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition inline-flex items-center justify-center">
                        {{ __('Reset Password') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>