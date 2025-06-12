<div x-data="{ tab: @entangle('activeTab') }" class="p-6">

    <!-- Tab Header -->
    <div class="tab-header-wrapper flex space-x-2">
        <button
            @click="tab = 'borrow'"
            :class="[
                'user-tab-button py-2 px-4 text-sm font-medium focus:outline-none transition-colors duration-200',
                tab === 'borrow' 
                    ? 'user-tab-button-active dark:user-tab-button-active'
                    : 'hover:text-amber-500 dark:hover:text-amber-300'
            ]"
        >
            Borrowed
        </button>

        <button
            @click="tab = 'return'"
            :class="[
                'user-tab-button py-2 px-4 text-sm font-medium focus:outline-none transition-colors duration-200',
                tab === 'return' 
                    ? 'user-tab-button-active dark:user-tab-button-active'
                    : 'hover:text-amber-500 dark:hover:text-amber-300'
            ]"
        >
            Returned
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
    </div>
</div>
