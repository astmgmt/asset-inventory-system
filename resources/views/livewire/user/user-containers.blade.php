<div x-data="{ tab: @entangle('activeTab') }" class="p-6">
    <div class="tab-header-wrapper flex space-x-2">
        <!-- Borrow Tab -->
        <button
            @click="tab = 'borrow'"
            :class="[
                'user-tab-button py-2 px-4 text-sm font-medium focus:outline-none transition-colors duration-200 relative flex items-center',
                tab === 'borrow' 
                    ? 'user-tab-button-active dark:user-tab-button-active'
                    : 'hover:text-amber-500 dark:hover:text-amber-300'
            ]"
        >
            Borrow
            @if ($pendingBorrowCount > 0)
                <span class="ml-2 relative flex items-center">
                    <span class="bg-amber-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                        <!-- Clock Icon -->
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m-4 6a9 9 0 110-18 9 9 0 010 18z" />
                        </svg>
                    </span>
                    <span class="absolute -top-1.5 -right-2 bg-red-500 text-white rounded-full text-[10px] w-4 h-4 flex items-center justify-center">
                        {{ $pendingBorrowCount > 9 ? '9+' : $pendingBorrowCount }}
                    </span>
                </span>
            @endif
        </button>

        <!-- Return Tab -->
        <button
            @click="tab = 'return'"
            :class="[
                'user-tab-button py-2 px-4 text-sm font-medium focus:outline-none transition-colors duration-200 relative flex items-center',
                tab === 'return' 
                    ? 'user-tab-button-active dark:user-tab-button-active'
                    : 'hover:text-amber-500 dark:hover:text-amber-300'
            ]"
        >
            Return
            <span class="ml-2 flex space-x-1 relative">
                {{-- Borrowed Badge --}}
                @if ($pendingReturnBorrowedCount > 0)
                    <span class="relative flex items-center">
                        <span class="bg-green-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                            <!-- Check Circle Icon -->
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m7 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="absolute -top-1.5 -right-2 bg-red-500 text-white rounded-full text-[10px] w-4 h-4 flex items-center justify-center">
                            {{ $pendingReturnBorrowedCount > 9 ? '9+' : $pendingReturnBorrowedCount }}
                        </span>
                    </span>
                @endif

                {{-- Pending Badge --}}
                @if ($pendingReturnPendingCount > 0)
                    <span class="relative flex items-center">
                        <span class="bg-amber-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                            <!-- Clock Icon -->
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m-4 6a9 9 0 110-18 9 9 0 010 18z" />
                            </svg>
                        </span>
                        <span class="absolute -top-1.5 -right-2 bg-red-500 text-white rounded-full text-[10px] w-4 h-4 flex items-center justify-center">
                            {{ $pendingReturnPendingCount > 9 ? '9+' : $pendingReturnPendingCount }}
                        </span>
                    </span>
                @endif

                {{-- Rejected Badge --}}
                @if ($pendingReturnRejectedCount > 0)
                    <span class="relative flex items-center">
                        <span class="bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                            <!-- X Circle Icon -->
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12M12 22a10 10 0 100-20 10 10 0 000 20z" />
                            </svg>
                        </span>
                        <span class="absolute -top-1.5 -right-2 bg-red-700 text-white rounded-full text-[10px] w-4 h-4 flex items-center justify-center">
                            {{ $pendingReturnRejectedCount > 9 ? '9+' : $pendingReturnRejectedCount }}
                        </span>
                    </span>
                @endif
            </span>
        </button>

        <!-- History Tab -->
        <button
            @click="tab = 'history'"
            :class="[
                'user-tab-button py-2 px-4 text-sm font-medium focus:outline-none transition-colors duration-200 relative flex items-center',
                tab === 'history' 
                    ? 'user-tab-button-active dark:user-tab-button-active'
                    : 'hover:text-amber-500 dark:hover:text-amber-300'
            ]"
        >
            History
            <span class="ml-2 flex space-x-1 relative">
                {{-- Borrow Approved --}}
                @if ($borrowApprovedHistoryCount > 0)
                    <span class="relative flex items-center">
                        <span class="bg-yellow-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                            <!-- Clipboard Check Icon -->
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m1-4h-4a2 2 0 00-2 2v0a2 2 0 002 2h4a2 2 0 002-2v0a2 2 0 00-2-2z" />
                            </svg>
                        </span>
                        <span class="absolute -top-1.5 -right-2 bg-red-500 text-white rounded-full text-[10px] w-4 h-4 flex items-center justify-center">
                            {{ $borrowApprovedHistoryCount > 9 ? '9+' : $borrowApprovedHistoryCount }}
                        </span>
                    </span>
                @endif

                {{-- Return Approved --}}
                @if ($returnApprovedHistoryCount > 0)
                    <span class="relative flex items-center">
                        <span class="bg-green-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                            <!-- Check Circle -->
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m7 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="absolute -top-1.5 -right-2 bg-red-500 text-white rounded-full text-[10px] w-4 h-4 flex items-center justify-center">
                            {{ $returnApprovedHistoryCount > 9 ? '9+' : $returnApprovedHistoryCount }}
                        </span>
                    </span>
                @endif

                {{-- Borrow Denied --}}
                @if ($borrowDeniedHistoryCount > 0)
                    <span class="relative flex items-center">
                        <span class="bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">
                            <!-- X Icon -->
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                        <span class="absolute -top-1.5 -right-2 bg-red-700 text-white rounded-full text-[10px] w-4 h-4 flex items-center justify-center">
                            {{ $borrowDeniedHistoryCount > 9 ? '9+' : $borrowDeniedHistoryCount }}
                        </span>
                    </span>
                @endif
            </span>
        </button>




    </div>

    <!-- Tab Content -->
    <div class="mt-6">
        <template x-if="tab === 'borrow'">
            <div class="user-table p-4 rounded shadow-sm">
                <livewire:user.user-borrow-transactions />
            </div>
        </template>

        <template x-if="tab === 'return'">
            <div class="user-table p-4 rounded shadow-sm">
                <livewire:user.user-return-transactions />
            </div>
        </template>

        <template x-if="tab === 'history'">
            <div class="user-table p-4 rounded shadow-sm">
                <livewire:user.user-history-transactions />
            </div>
        </template>
    </div>
</div>
