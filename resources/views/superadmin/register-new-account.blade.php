<x-layouts.app>
    <div class="relative z-10 w-full max-w-7xl mx-auto min-h-screen flex flex-col items-center">
        <form method="POST" action="{{ route('superadmin.create') }}" enctype="multipart/form-data"
            class="mt-1 w-full max-w-[42rem] space-y-6 p-8 bg-white/80 dark:bg-gray-800/70 backdrop-blur-md rounded-lg transition-all">
            @csrf

            <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-4">
                Create New Account
            </h2>

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-red-100 text-red-700 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-100 text-red-700 rounded-md">
                    <x-validation-errors class="mb-6" />
                </div>
            @endif
            
            <div>
                <x-label for="name" value="Name" />
                <x-input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="mt-1 w-full" />
            </div>

            <div>
                <x-label for="username" value="Username" />
                <x-input id="username" name="username" type="text" value="{{ old('username') }}" required class="mt-1 w-full" />
            </div>

            <div>
                <x-label for="email" value="Email" />
                <x-input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full" />
            </div>

            <div>
                <x-label for="password" value="Password" />
                <x-input id="password" name="password" type="password" required class="mt-1 w-full" />
            </div>

            <div>
                <x-label for="password_confirmation" value="Confirm Password" />
                <x-input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 w-full" />
            </div>

            <div>
                <x-label for="contact_number" value="Contact Number" />
                <x-input id="contact_number" name="contact_number" type="text" value="{{ old('contact_number') }}" class="mt-1 w-full" />
            </div>

            <div>
                <x-label for="address" value="Address" />
                <x-input id="address" name="address" type="text" value="{{ old('address') }}" class="mt-1 w-full" />
            </div>

            <div>
                <x-label for="role_id" value="Account Role" />
                <select id="role_id" name="role_id" required
                    class="mt-1 w-full">
                    <option value="" disabled>Select Account Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="status" value="Account Status" />
                <select id="status" name="status" required
                    class="mt-1 w-full">
                    <option value="Approved" {{ old('status', 'Approved') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Blocked" {{ old('status') == 'Blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>

            <div>
                <x-label for="profile_photo" value="Profile Photo" />
                <input id="profile_photo" name="profile_photo" type="file"
                    class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-300
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-gray-100 file:text-gray-700
                    hover:file:bg-gray-200" />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Max 2MB. JPG, PNG, GIF only.</p>
            </div>

            <div class="pt-4 flex justify-end space-x-3">
                {{-- Cancel Button (as a link) --}}
                <a href="{{ route('dashboard.superadmin') }}"
                class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition"
                >
                    <i class="fas fa-arrow-left mr-2"></i> Cancel
                </a>

                {{-- Create Account Button --}}
                <x-button
                    type="submit"
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white"
                >
                    <i class="fas fa-user-plus mr-2"></i> Create
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
