<x-layouts.app>
    <div class="main-content">
        <div class="flex justify-between items-center mb-6">
            <h2>Asset Overview</h2>
            <a href="#" class="btn">Add New Asset</a>
        </div>
        
        <div class="asset-card">
            <h3>Laptop Inventory <span class="status-indicator status-active"></span></h3>
            <p><strong>Total:</strong> 142 items | <strong>Available:</strong> 28</p>
            <p class="text-light">Last updated: Today, 10:42 AM</p>
        </div>
        
        <div class="asset-card">
            <h3>Office Furniture <span class="status-indicator status-inactive"></span></h3>
            <p><strong>Total:</strong> 87 items | <strong>In maintenance:</strong> 3</p>
            <p class="text-light">Last updated: Yesterday, 3:15 PM</p>
        </div>
        
        <div class="bg-light p-4 rounded-lg mt-6">
            <h3 class="text-accent">Recent Activity</h3>
            <ul class="mt-3">
                <li class="py-2 border-b border-gray-100">✓ Asset #A-2834 assigned to John Doe</li>
                <li class="py-2 border-b border-gray-100">✓ New category "Network Equipment" added</li>
                <li class="py-2">✓ Maintenance completed on Printer #P-9982</li>
            </ul>
        </div>
    </div>
    
    {{-- <div class="sidebar">
        <div class="bg-light p-4 rounded-lg mb-5">
            <h3>Quick Stats</h3>
            <div class="mt-3 space-y-2">
                <p><strong>Total Assets:</strong> 429</p>
                <p><strong>Active Users:</strong> 24</p>
                <p><strong>Pending Actions:</strong> 3</p>
            </div>
        </div>
        
        <div>
            <h3>Upcoming Audits</h3>
            <div class="mt-3 space-y-3">
                <div>
                    <p class="font-medium">IT Equipment Audit</p>
                    <p class="text-sm text-light">June 15, 2023</p>
                </div>
                <div>
                    <p class="font-medium">Facility Assets</p>
                    <p class="text-sm text-light">June 22, 2023</p>
                </div>
            </div>
        </div>
    </div> --}}
</x-layouts.app>