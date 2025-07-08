<div class="relative" x-data="{ open: false }" wire:poll.10s="refreshCounts">
    <button 
        @click="open = !open"
        class="btn-notification relative p-2 text-gray-700 hover:bg-gray-100 rounded-full focus:outline-none mr-4"
    >
        <i class="fas fa-bell text-lg"></i>
        @php
            $totalCount = $borrowCount + $returnCount + $emailCount;
            if ($isSuperAdmin) {
                $totalCount += $pendingUserCount;
            }
        @endphp
        
        @if($totalCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ $totalCount }}
            </span>
        @endif
    </button>

    <div 
        x-show="open"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 z-50"
        style="display: none;"
        x-cloak
    >
        @if($emailCount > 0)
            <div class="flex flex-col">
                <a 
                    href="{{ $this->getEmailProviderUrl(auth()->user()->email) }}" 
                    target="_blank"
                    class="flex justify-between items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                    @click="
                        open = false;
                        $wire.call('markEmailNotificationsAsRead');
                    "
                >
                    <span>New Email Notifications</span>
                    <span class="bg-purple-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                        {{ $emailCount }}
                    </span>
                </a>
                <div class="text-xs text-gray-500 px-4 pb-2">
                    @if($isSuperAdmin)
                        View admin emails
                    @else
                        View emails in your inbox
                    @endif
                </div>
            </div>
        @endif
        
        @if($borrowCount > 0)
            <a 
                href="{{ route('approve.requests') }}" 
                class="flex justify-between items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                <span>Borrow Requests</span>
                <span class="bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                    {{ $borrowCount }}
                </span>
            </a>
        @endif

        @if($returnCount > 0)
            <a 
                href="{{ route('approve.return') }}" 
                class="flex justify-between items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                <span>Return Requests</span>
                <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                    {{ $returnCount }}
                </span>
            </a>
        @endif

        @if($isSuperAdmin && $pendingUserCount > 0)
            <a 
                href="{{ route('superadmin.manage') }}" 
                class="flex justify-between items-center px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
            >
                <span>Pending Users</span>
                <span class="bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                    {{ $pendingUserCount }}
                </span>
            </a>
        @endif

        @if(($isSuperAdmin && ($emailCount + $borrowCount + $returnCount + $pendingUserCount) == 0) ||
            (!$isSuperAdmin && ($emailCount + $borrowCount + $returnCount) == 0))
            <div class="px-4 py-2 text-gray-500 text-sm text-center">
                No new notifications
            </div>
        @endif
    </div>
</div>