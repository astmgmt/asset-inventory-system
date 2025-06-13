<div class="superadmin-container">
    <h1 class="page-title main-title">Borrow Assets</h1>
    
    <!-- Success Message -->
    @if ($successMessage)
        <div class="success-message mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)">
            {{ $successMessage }}
        </div>
    @endif

    <!-- Error Message -->
    @if ($errorMessage)
        <div class="error-message mb-4" 
             x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Search and Cart Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="search-bar w-full md:w-1/3 relative">
            <input 
                type="text" 
                placeholder="Search assets by name, code or condition..." 
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

    <!-- Assets Table -->
    <div class="overflow-x-auto">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Name</th>
                    <th>Available Qty</th>
                    <th>Condition</th>
                    <th class="actions-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    @php
                        $condition = \App\Models\AssetCondition::find($asset->condition_id);
                    @endphp
                    <tr>
                        <td data-label="Asset Code" class="text-center">{{ $asset->asset_code }}</td>
                        <td data-label="Name" class="text-center">{{ $asset->name }}</td>
                        <td data-label="Available Qty" class="text-center">{{ $asset->available_quantity }}</td>
                        <td data-label="Condition" class="text-center">{{ $condition ? $condition->condition_name : 'N/A' }}</td>
                        <td data-label="Actions" class="text-center">
                            <button 
                                wire:click="addToCart({{ $asset->id }})" 
                                class="borrow_icon bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-indigo-600 dark:text-indigo-400 font-medium py-1 px-3 rounded-md shadow-sm transition-colors duration-200"
                                @disabled($asset->available_quantity < 1)
                                title="Borrow this asset"
                            >
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="no-software-row">No available assets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4 pagination-container">
            {{ $assets->links() }}
        </div>
    </div>

    <!-- Cart Modal -->
    @if($showCartModal)
        <div class="modal-backdrop">
            <div class="modal" x-data x-on:click.away="$wire.set('showCartModal', false)">
                <div class="modal-header">
                    <h2 class="modal-title">Borrowing Cart</h2>
                    <button wire:click="$set('showCartModal', false)" class="modal-close">&times;</button>
                </div>
                
                <div class="modal-body">
                    @if(count($selectedAssets))
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th class="w-12">
                                        <input 
                                            type="checkbox" 
                                            wire:click="toggleSelectAll"
                                            @if(count($selectedForBorrow) === count($selectedAssets)) checked @endif
                                            class="checkbox-success"
                                        >
                                    </th>
                                    <th>Asset</th>
                                    <th>Quantity</th>
                                    <th>Purpose</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedAssets as $asset)
                                    <tr>
                                        <td class="text-center">
                                            <input 
                                                type="checkbox" 
                                                wire:model="selectedForBorrow"
                                                value="{{ $asset['id'] }}"
                                                class="checkbox-item"
                                            >
                                        </td>
                                        <td data-label="Asset" class="text-center">
                                            {{ $asset['name'] }} ({{ $asset['code'] }})
                                        </td>

                                        <td data-label="Quantity" class="text-center">
                                            <input 
                                                type="number" 
                                                min="1" 
                                                max="{{ $asset['max_quantity'] }}"
                                                value="{{ $asset['quantity'] }}"
                                                wire:change="updateCartQuantity({{ $asset['id'] }}, $event.target.value)"
                                                class="form-input w-20 text-center"
                                            >
                                        </td>

                                        <td data-label="Purpose" class="text-center">
                                            <input 
                                                type="text" 
                                                placeholder="Purpose..." 
                                                wire:model="selectedAssets.{{ $asset['id'] }}.purpose"
                                                class="form-input w-full"
                                            >
                                        </td>
                                        <td data-label="Action" class="text-center">
                                            <button 
                                                wire:click="removeFromCart('{{ $asset['id'] }}')" 
                                                class="text-red-600 hover:text-red-800"
                                            >
                                                &times;
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <!-- Remarks section -->
                        <div class="mt-6">
                            <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks (Optional)</label>
                            <textarea 
                                id="remarks" 
                                wire:model="remarks" 
                                rows="3" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Add any remarks for the admin..."
                            ></textarea>
                        </div>
                    @else
                        <p class="text-center py-4">Your cart is empty</p>
                    @endif
                    
                    @if ($errorMessage)
                        <div class="error-message mt-4">
                            {{ $errorMessage }}
                        </div>
                    @endif
                </div>
                
                <div class="modal-footer">
                    @if(count($selectedAssets))
                        <button 
                            class="btn btn-danger"
                            x-data
                            x-on:click="if(confirm('Are you sure you want to remove all items from the cart?')) { $wire.clearCart(); } else { return false; }"
                        >
                            Remove All
                        </button>
                    @endif
                    <div class="flex-1"></div>
                        <button 
                            wire:click="borrow" 
                            class="btn btn-secondary btn-update flex items-center gap-2"
                        >
                            <i class="fas fa-paper-plane"></i>
                            Proceed
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