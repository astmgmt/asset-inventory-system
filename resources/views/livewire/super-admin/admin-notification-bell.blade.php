<div class="relative" x-data="{ open: false }" wire:poll.10s="refreshCounts">
    <!-- Bell Icon -->
    <button 
        @click="open = !open"
        class="btn-notification relative p-2 text-gray-700 hover:bg-gray-100 rounded-full focus:outline-none mr-4"
    >
        <i class="fas fa-bell text-lg"></i>
        @if($borrowCount > 0 || $returnCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ $borrowCount + $returnCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div 
        x-show="open"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 z-50"
        style="display: none;"
        x-cloak
    >
        <a 
            href="{{ route('approve.requests') }}" 
            class="flex justify-between items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
        >
            <span>Borrow Request</span>
            <span class="bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                {{ $borrowCount }}
            </span>
        </a>
        <a 
            href="{{ route('approve.return') }}" 
            class="flex justify-between items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
        >
            <span>Return Request</span>
            <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                {{ $returnCount }}
            </span>
        </a>
    </div>
</div>