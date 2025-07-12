<div>
    @if($nullSerialsCount >= 2)
        <button wire:click="openModal" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md shadow-sm inline-flex items-center transition-colors duration-200 ml-2">
            <i class="fas fa-list mr-2"></i> Add Missing Serial Numbers ({{ $nullSerialsCount }})
        </button>
    @endif

    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Add Missing Serial Numbers 
                        <span class="text-sm text-gray-500">({{ count($assets) }} of {{ $nullSerialsCount }} remaining)</span>
                    </h2>
                    
                    <div class="mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 tracking-wider">Asset Code</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 tracking-wider">Brand</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 tracking-wider">Model</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 tracking-wider">Serial #</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if(count($assets) > 0)
                                        @foreach($assets as $index => $asset)
                                            <tr class="text-center">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $asset['asset_code'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $asset['name'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $asset['model_number'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input 
                                                        type="text" 
                                                        wire:model="serialNumbers.{{ $index }}"
                                                        class="block w-full bg-gray-50 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        placeholder="Enter serial number"
                                                    >
                                                    @error("serialNumbers.{$index}") 
                                                        <span class="mt-1 text-sm text-red-600">
                                                            {{ str_replace("serialNumbers.{$index}", 'serial # ', $message) }}
                                                        </span> 
                                                    @enderror
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-700">
                                                No assets found without serial numbers.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button 
                            wire:click="saveSerials" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                            wire:loading.attr="disabled"
                            wire:target="saveSerials"
                        >
                            <span wire:loading.remove wire:target="saveSerials">Save and Close</span>
                            <span wire:loading wire:target="saveSerials">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Saving...
                            </span>
                        </button>

                        <button 
                            wire:click="closeModal" 
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showContinueModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Continue Adding Serial Numbers?</h3>
                    <p class="mb-6 text-gray-700">You have successfully added serial numbers for this batch. There are {{ $nullSerialsCount }} more assets without serial numbers.</p>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            wire:click="continueAdding" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                        >
                            Yes, Continue
                        </button>
                        <button 
                            wire:click="closeAll" 
                            class="inline-flex items-center px-4 py-2 bg-yellow-200 border border-yellow-300 rounded-md font-semibold text-xs text-yellow-700 uppercase tracking-widest shadow-sm hover:bg-yellow-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition"
                        >
                            No, I'm Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session()->has('message'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg"
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
        >
            {{ session('message') }}
        </div>
    @endif
</div>