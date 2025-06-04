<x-layouts.app>
    <div class="main-content-area">

        <!-- Header Section -->
        <div class="header flex justify-between items-center mb-6">
            <h2>Asset Overview</h2>
            <a href="#" class="btn">Add New Asset</a>
        </div>

        <!-- Charts Row: two cards side by side on desktop, stacked on mobile -->
        <div class="charts-row">
            <!-- Asset Statuses Chart -->
            <div class="content-card chart-card">
                <h3>Asset Statuses</h3>
                <canvas id="assetStatusChart"></canvas>
            </div>

            <!-- Warranty Expiry Chart -->
            <div class="content-card chart-card">
                <h3>Warranty Expiry</h3>
                <canvas id="warrantyExpiryChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="content-card mt-6 bg-light p-4 rounded-lg">
            <h3 class="text-accent">Recent Activity</h3>
            <ul class="mt-3">
                <li class="py-2 border-b border-gray-100">✓ Asset #A-2834 assigned to John Doe</li>
                <li class="py-2 border-b border-gray-100">✓ New category "Network Equipment" added</li>
                <li class="py-2">✓ Maintenance completed on Printer #P-9982</li>
            </ul>
        </div>

    </div>
</x-layouts.app>
