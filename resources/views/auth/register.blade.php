<x-layouts.guest>
    <div class="login-gradient-bg w-full min-h-screen overflow-y-auto relative pt-20">
        
        <!-- Registration Form Wrapper -->
        <div class="relative z-10 w-full max-w-7xl mx-auto min-h-screen flex flex-col items-center px-4 py-10">
            
            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
                class="mt-16 w-full max-w-xl space-y-6 p-8 rounded-2xl shadow-2xl bg-white/80 dark:bg-gray-800/70 backdrop-blur-md border border-white/30 transition-all" style="font-family: 'Inter', sans-serif;">
                @csrf

                <!-- Title -->
                <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-4">
                    {{ __('Register') }}
                </h2>

                <!-- Validation Errors -->
                <x-validation-errors class="mb-6" />

                <!-- Name -->
                <div>
                    <x-label for="name" value="{{ __('Name') }}" />
                    <x-input id="name" name="name" type="text" class="block mt-1 w-full text-base py-3" :value="old('name')" required autofocus autocomplete="name" />
                </div>

                <!-- Username -->
                <div>
                    <x-label for="username" value="Username" />
                    <x-input id="username" name="username" type="text" class="block mt-1 w-full text-base py-3" :value="old('username')" required />
                </div>

                <!-- Email -->
                <div>
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" name="email" type="email" class="block mt-1 w-full text-base py-3" :value="old('email')" required autocomplete="username" />
                </div>

                <!-- Password -->
                <div>
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" name="password" type="password" class="block mt-1 w-full text-base py-3" required autocomplete="new-password" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                    <x-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full text-base py-3" required autocomplete="new-password" />
                </div>

                <!-- Contact Number -->
                <div>
                    <x-label for="contact_number" value="Contact Number" />
                    <x-input id="contact_number" name="contact_number" type="text" class="block mt-1 w-full text-base py-3" :value="old('contact_number')" />
                </div>

                <!-- Address -->
                <div>
                    <x-label for="address" value="Address" />
                    <x-input id="address" name="address" type="text" class="block mt-1 w-full text-base py-3" :value="old('address')" />
                </div>

                <!-- Profile Photo -->
                <div>
                    <x-label for="profile_photo" value="Profile Photo" />
                    <x-input id="profile_photo" name="profile_photo" type="file" class="block mt-1 w-full text-base py-3 file:mr-4 file:py-2 file:px-4 file:border file:rounded-lg file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200" />
                </div>

                <!-- Terms and Privacy Policy -->
                @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                    <div>
                        <x-label for="terms">
                            <div class="flex items-center">
                                <x-checkbox name="terms" id="terms" required />
                                <div class="ml-2 text-sm text-gray-700 dark:text-gray-200">
                                    {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline hover:text-gray-900">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline hover:text-gray-900">'.__('Privacy Policy').'</a>',
                                    ]) !!}
                                </div>
                            </div>
                        </x-label>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row sm:justify-between gap-4 pt-4">
                    <a href="{{ route('login') }}"
                       class="w-full sm:w-auto text-sm px-6 py-3 text-center font-semibold rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 transition">
                        {{ __('Already registered?') }}
                    </a>

                    <x-button type="submit"
                        class="w-full sm:w-auto text-base px-6 py-3 text-white font-semibold bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-center justify-center items-center">
                        {{ __('Register') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
