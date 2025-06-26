<div class="relative" x-data="{ open: false }" wire:poll.10s="refreshNotifications">
    <!-- Bell Icon -->
    <button 
        @click="open = !open"
        class="relative p-2 text-gray-700 hover:bg-gray-100 rounded-full dark:text-gray-300 dark:hover:bg-gray-700 focus:outline-none mr-4"
        wire:loading.class="opacity-50 cursor-not-allowed"
    >
        <i class="fas fa-bell text-lg"></i>
        @if($count > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ $count }}
            </span>
        @endif
    </button>

    <!-- Loading Indicator -->
    <div wire:loading.flex wire:target="markAsRead, markAllAsRead" class="absolute top-0 left-0 right-0 bottom-0 bg-gray-200 bg-opacity-50 z-50 items-center justify-center" style="display: none">
        <div class="text-center p-4">
            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
            <p class="mt-2 text-gray-700">Updating...</p>
        </div>
    </div>

    <!-- Dropdown -->
    <div 
        x-show="open"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 z-50 max-h-96 overflow-y-auto"
        style="display: none;"
        x-cloak
        wire:loading.class="opacity-50"
    >
        
        @forelse($notifications as $notification)
            <a 
                href="{{ $this->getRoute($notification['status']) }}" 
                x-on:click.prevent="
                    open = false;
                    $wire.markAsRead('{{ $notification['id'] }}').then((id) => {
                        window.location = '{{ $this->getRoute($notification['status']) }}';
                    });
                "
                class="block px-4 py-3 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700"
            >
                <div class="flex justify-between">
                    <span class="font-semibold">
                        @if($notification['status'] === 'Borrow Approved')
                            Borrow Request Approved
                        @elseif($notification['status'] === 'Borrow Denied')
                            Borrow Request Rejected
                        @elseif($notification['status'] === 'Return Approved')
                            Return Request Approved
                        @elseif($notification['status'] === 'Return Denied')
                            Return Request Rejected
                        @endif
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($notification['action_date'])->diffForHumans() }}
                    </span>
                </div>
                <p class="text-sm mt-1">
                    @if($notification['status'] === 'Borrow Approved')
                        Click to view approved assets
                    @elseif($notification['status'] === 'Borrow Denied')
                        Click to view history
                    @elseif($notification['status'] === 'Return Approved')
                        Click to view return history
                    @else
                        Click to return assets again
                    @endif
                </p>
            </a>
        @empty
            <div class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                No new notifications
            </div>
        @endforelse


        @if($count > 0)
            <button 
                wire:click="markAllAsRead"
                wire:loading.attr="disabled"
                class="w-full text-center px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 dark:text-blue-400 dark:hover:bg-gray-700 disabled:opacity-50"
            >
                Clear All
            </button>
        @endif
    </div>
</div>