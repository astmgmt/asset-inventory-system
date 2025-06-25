<div class="relative" x-data="{ open: false }" wire:poll.10s="refreshNotifications">
    <!-- Bell Icon -->
    <button 
        @click="open = !open"
        class="relative p-2 text-gray-700 hover:bg-gray-100 rounded-full dark:text-gray-300 dark:hover:bg-gray-700 focus:outline-none mr-4"
    >
        <i class="fas fa-bell text-lg"></i>
        @if($notificationCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ $notificationCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div 
        x-show="open"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 z-50 max-h-96 overflow-y-auto"
        style="display: none;"
        x-cloak
    >
        @if($notificationCount > 0)
            <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border-b">
                Recent Updates
            </div>
            @foreach($recentNotifications as $notification)
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <div class="text-sm text-gray-800 dark:text-gray-200">
                        {{ $notification['message'] }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $notification['time']->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        @else
            <div class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">
                No new notifications
            </div>
        @endif
        
        <!-- Navigation Links -->
        <div class="border-t border-gray-200 dark:border-gray-700">
            <a 
                href="{{ route('user.borrow.transactions') }}" 
                class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                Borrow Transactions
            </a>
            <a 
                href="{{ route('user.return.transactions') }}" 
                class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                Return Transactions
            </a>
        </div>
    </div>
</div>