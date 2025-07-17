<div class="superadmin-container">
    <h1 class="page-title main-title">Asset Assignment</h1>
    
    @if ($successMessage)
        <div class="success-message mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="error-message mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="search-bar w-full md:w-1/3 relative">
            <input 
                type="text" 
                placeholder="Search code, name, model ..." 
                wire:model.live.debounce.300ms="search"
                class="search-input w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            @if($search)
                <button wire:click="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    &times;
                </button>
            @else
                <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            @endif
        </div>
        
        <div class="cart-button-wrapper w-full flex justify-end mt-2 md:mt-0 md:w-auto mr-4">
            <button 
                wire:click="$set('showCartModal', true)" 
                class="cart-button relative flex items-center justify-center w-12 h-12 bg-indigo-600 text-white rounded-full shadow hover:bg-indigo-700 transition"
            >
                <i class="fas fa-shopping-cart text-xl"></i>
                @if(count($selectedAssets))
                    <span class="cart-badge absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-red-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full shadow">
                        {{ array_sum(array_column($selectedAssets, 'quantity')) }}
                    </span>
                @endif
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Category</th>
                    <th>Condition</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    <tr>
                        <td data-label="Code" class="text-center">{{ $asset->asset_code }}</td>
                        <td data-label="Name" class="text-center">{{ $asset->name }}</td>
                        <td data-label="Model" class="text-center">{{ $asset->model_number }}</td>
                        <td data-label="Model" class="text-center">{{ $asset->category_name }}</td>

                        <td data-label="Condition" class="text-center">
                            @php
                                $conditionName = strtolower($asset->condition_name); 
                                $conditionClass = match($conditionName) {
                                    'new' => 'bg-blue-100 text-blue-800',        
                                    'borrowed' => 'bg-indigo-100 text-indigo-800', 
                                    'available' => 'bg-green-100 text-green-800',  
                                    'defective' => 'bg-red-100 text-red-800',        
                                    'disposed' => 'bg-yellow-100 text-yellow-800', 
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $displayCondition = ucfirst($conditionName);
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $conditionClass }} w-[80px] justify-center">
                                {{ $displayCondition }}
                            </span>
                        </td>

                        <td data-label="Actions" class="text-center">
                            <button 
                                wire:click="addToCart({{ $asset->id }})" 
                                class="borrow_icon bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-indigo-600 dark:text-indigo-400 font-medium py-1 px-3 rounded-md shadow-sm transition-colors duration-200"
                                @disabled($asset->available_quantity < 1)
                                title="Add to assignment cart"
                            >
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-software-row">No available assets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4 pagination-container">
            {{ $assets->links() }}
        </div>
    </div>

    @if($showCartModal)
        <div class="modal-backdrop">
            <div class="modal" x-data x-on:click.away="$wire.set('showCartModal', false)">
                <div class="modal-header">
                    <h2 class="modal-title">Assignment Cart</h2>
                    <button wire:click="$set('showCartModal', false)" class="modal-close">&times;</button>
                </div>
                
                <div class="modal-body">
                    <div class="bg-green-50 p-4 rounded-md shadow-sm mb-6">
                        
                        <div class="mb-4" x-data="{ showSuggestions: false }">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                User or Administrator Name
                            </label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="userIdentifier"
                                    placeholder="Enter user's email or username"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    x-on:focus="showSuggestions = true"
                                    x-on:blur="setTimeout(() => showSuggestions = false, 200)"
                                />
                                
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button 
                                        x-show="$wire.userIdentifier" 
                                        wire:click="clearUserSearch"
                                        type="button"
                                        class="text-gray-400 hover:text-gray-600 focus:outline-none"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                
                                <div x-show="showSuggestions && $wire.userSearchResults.length > 0" 
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-300 max-h-60 overflow-auto">
                                    <ul>
                                        @foreach($userSearchResults as $user)
                                            <li wire:click="selectUser({{ $user['id'] }})" 
                                                @click="showSuggestions = false"
                                                class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                                                <div class="font-medium">{{ $user['name'] }}</div>
                                                <div class="text-sm text-gray-600">{{ $user['email'] }}</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <!-- No results message -->
                                <div x-show="showSuggestions && $wire.userIdentifier.length >= 2 && $wire.userSearchResults.length === 0" 
                                    class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-300 px-4 py-2 text-gray-500">
                                    No matching users found
                                </div>
                            </div>
                        </div>

                        @if(count($selectedAssets))
                            <table class="user-table w-full border-collapse">
                                <thead>
                                    <tr class="bg-blue-100 text-blue-800">
                                        <th class="w-12 px-2 py-1 text-center">
                                            <input 
                                                type="checkbox" 
                                                wire:click="toggleSelectAll"
                                                @if(count($selectedForBorrow) === count($selectedAssets)) checked @endif
                                                class="checkbox-success"
                                            >
                                        </th>
                                        <th class="px-2 py-1 text-left">Asset</th>
                                        <th class="px-2 py-1 text-center">Quantity</th>
                                        <th class="px-2 py-1 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedAssets as $asset)
                                        <tr class="border-t border-blue-200">
                                            <td class="text-center px-2 py-1">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model="selectedForBorrow"
                                                    value="{{ $asset['id'] }}"
                                                    class="checkbox-item"
                                                >
                                            </td>
                                            <td data-label="Asset" class="text-left px-2 py-1">
                                                {{ $asset['name'] }} {{ $asset['model'] }} ({{ $asset['code'] }})
                                            </td>
                                            <td data-label="Quantity" class="text-center px-2 py-1">
                                                <input 
                                                    type="number" 
                                                    min="1" 
                                                    max="{{ $asset['max_quantity'] }}"
                                                    value="{{ $asset['quantity'] }}"
                                                    readonly
                                                    wire:change="updateCartQuantity({{ $asset['id'] }}, $event.target.value)"
                                                    class="form-input w-20 text-center"
                                                >
                                            </td>
                                            <td data-label="Action" class="text-center px-2 py-1">
                                                <button 
                                                    wire:click="removeFromCart('{{ $asset['id'] }}')" 
                                                    class="text-red-600 hover:text-red-800"
                                                    aria-label="Remove asset"
                                                >
                                                    &times;
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-3 text-left">
                                <label for="approve-remarks" class="block text-xs font-medium text-gray-700 mb-1">
                                    Remarks <span class="text-gray-400">(Optional)</span>
                                </label>
                                <textarea 
                                    id="approve-remarks" 
                                    wire:model="approveRemarks" 
                                    rows="3" 
                                    class="w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm leading-tight resize-none"
                                    placeholder="Add any remarks for the user..."
                                ></textarea>
                            </div>
                        @else
                            <p class="text-center py-4 text-blue-900 font-medium">Your cart is empty</p>
                        @endif

                        @if ($errorMessage)
                            <div class="mt-4 text-red-700 font-semibold bg-red-100 p-2 rounded-md shadow-sm">
                                {{ $errorMessage }}
                            </div>
                        @endif
                    </div>



                </div>
                
                <div class="modal-footer flex items-center space-x-4 pt-4 border-t border-gray-200">
                    @if(count($selectedAssets))
                        <button 
                            x-data 
                            x-on:click="if(confirm('Remove all items?')) { $wire.clearCart(); }"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                            type="button"
                        >
                            Clear Cart
                        </button>
                    @endif

                    <div class="flex-1"></div>

                    <button 
                        wire:click="assign" 
                        wire:loading.attr="disabled" wire:target="assign"
                        wire:loading.class="opacity-50 cursor-not-allowed" wire:target="assign"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition"
                        type="button"
                    >
                        <span wire:loading.class.add="hidden" wire:target="assign" class="flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Assign Assets
                        </span>
                        <span wire:loading.class.remove="hidden" wire:target="assign" class="hidden flex items-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Processing...
                        </span>
                    </button>

                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('clear-message', () => {
            setTimeout(() => {
                @this.set('successMessage', '');
                @this.set('errorMessage', '');
            }, 3000);
        });
    });
</script>