<div class="superadmin-container">
    <div class="dashboard-header flex justify-between items-center">
        <h1 class="page-title main-title text-xl font-bold">
            Warranty & Subscription Monitoring
        </h1>

        <div class="request-monitoring bg-yellow-100 text-yellow-900 border border-yellow-200 shadow-md rounded-lg w-64 mb-4 mr-4">
            <!-- Card Header -->
            <div class="px-4 py-2 border-b border-yellow-300 rounded-t-lg font-semibold">
                Notification Requests
            </div>

            <!-- Card Body -->
            <div class="flex flex-col justify-center px-4 py-3 text-sm">
                <div class="flex items-center">
                <a href="{{ route('approve.requests') }}" class="hover:underline">Borrow Request(s): <span class="ml-1 font-semibold">5</span></a>
                
                </div>
                <div class="flex items-center mt-2">
                <a href="{{ route('approve.return') }}" class="hover:underline">Return Request(s): <span class="ml-1 font-semibold">2</span></a>                
                </div>
            </div>
        </div>




    </div>






    <!-- First Section: Asset Monitoring -->
    <div class="section-wrapper bg-white p-6 rounded-lg mb-10">
        <h2 class="text-lg font-semibold text-gray-800 mb-4"><!-- Asset Monitoring --></h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Asset Table -->
            <div class="box-container">
                <div class="box-header">
                    <h2 class="box-title">Assets Expiring</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="user-table expiration-table mb-2">
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Asset Code</th>
                                <th>Exp. Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expiringAssets as $asset)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="Brand" class="text-center">{{ $asset->name }}</td>
                                    <td data-label="Model" class="text-center">{{ $asset->model_number }}</td>
                                    <td data-label="Asset Code" class="text-center">{{ $asset->asset_code }}</td>
                                    <td data-label="Expiration Date" class="text-center">{{ $asset->warranty_expiration->format('M d, Y') }}</td>
                                    <td data-label="Status" class="text-center">
                                        @if($asset->expiry_status === 'warning_3m')
                                            <span class="status-badge bg-orange-300 text-orange-900">⚠️ 3 months left</span>
                                        @elseif($asset->expiry_status === 'warning_2m')
                                            <span class="status-badge bg-orange-400 text-orange-900">⚠️ 2 months left</span>
                                        @elseif($asset->expiry_status === 'warning_1m')
                                            <span class="status-badge bg-orange-500 text-white">⚠️ 1 month left</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="no-software-row">No assets expiring in the next 3 months</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div mt-6 pagination-container>
                        {{ $expiringAssets->links() }}
                    </div>
                    
                </div>
            </div>

            <!-- Asset Chart -->
            <div class="box-container">
                <div class="box-header">
                    <h2 class="box-title">Asset Expiration Overview</h2>
                </div>
                <div class="box-body"
                    x-data="assetChart(@js($assetCounts))"
                    x-init="init()"
                    wire:key="asset-chart-{{ $expiringAssets->currentPage() }}"
                    x-on:chartDataUpdated.window="updateData($event.detail.assetCounts)">
                    <canvas id="assetExpiryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Section: Software Monitoring -->
    <div class="section-wrapper bg-white p-6 rounded-lg">
        <h2 class="text-lg font-semibold text-gray-800 mb-4"><!-- Software Monitoring --></h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Software Table -->
            <div class="box-container">
                <div class="box-header">
                    <h2 class="box-title">Software Subscriptions Expiring</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="user-table expiration-table mb-2">
                        <thead>
                            <tr>
                                <th>Software Name</th>
                                <th>Software Code</th>
                                <th>Expiration Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expiringSoftware as $software)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="Software Name" class="text-center">{{ $software->software_name }}</td>
                                    <td data-label="Software Code" class="text-center">{{ $software->software_code }}</td>
                                    <td data-label="Expiration Date" class="text-center">{{ $software->expiry_date->format('M d, Y') }}</td>
                                    <td data-label="Status" class="text-center">
                                        @if($software->expiry_status === 'warning_3m')
                                            <span class="status-badge bg-orange-300 text-orange-900">⚠️ 3 months left</span>
                                        @elseif($software->expiry_status === 'warning_2m')
                                            <span class="status-badge bg-orange-400 text-orange-900">⚠️ 2 months left</span>
                                        @elseif($software->expiry_status === 'warning_1m')
                                            <span class="status-badge bg-orange-500 text-white">⚠️ 1 month left</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="no-software-row">No software subscriptions expiring in the next 3 months</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div mt-6 pagination-container>
                        {{ $expiringSoftware->links() }}
                    </div>
                </div>
            </div>

            <!-- Software Chart -->
            <div class="box-container">
                <div class="box-header">
                    <h2 class="box-title">Software Expiration Overview</h2>
                </div>
                <div class="box-body"
                    x-data="softwareChart(@js($softwareCounts))"
                    x-init="init()"
                    wire:key="software-chart-{{ $expiringSoftware->currentPage() }}"
                    x-on:chartDataUpdated.window="updateData($event.detail.softwareCounts)">
                    <canvas id="softwareExpiryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


<script> 
    function assetChart(initialCounts) {
        return {
            chart: null,
            counts: initialCounts,
            init() {
                this.renderChart();
            },
            renderChart() {
                const ctx = document.getElementById('assetExpiryChart');
                if (!ctx) return;
                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['3 Months', '2 Months', '1 Month'],
                        datasets: [{
                            label: 'Assets Expiring',
                            data: [
                                this.counts['3m'] || 0,
                                this.counts['2m'] || 0,
                                this.counts['1m'] || 0
                            ],
                            backgroundColor: ['#f59e0b', '#f97316', '#ef4444'],
                            borderColor: ['#d97706', '#c2410c', '#b91c1c'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            },
            updateData(newCounts) {
                this.counts = newCounts;
                if (this.chart) {
                    this.chart.data.datasets[0].data = [
                        this.counts['3m'] || 0,
                        this.counts['2m'] || 0,
                        this.counts['1m'] || 0
                    ];
                    this.chart.update();
                }
            }
        };
    }

    function softwareChart(initialCounts) {
        return {
            chart: null,
            counts: initialCounts,
            init() {
                this.renderChart();
            },
            renderChart() {
                const ctx = document.getElementById('softwareExpiryChart');
                if (!ctx) return;
                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['3 Months', '2 Months', '1 Month'],
                        datasets: [{
                            label: 'Software Expiring',
                            data: [
                                this.counts['3m'] || 0,
                                this.counts['2m'] || 0,
                                this.counts['1m'] || 0
                            ],
                            backgroundColor: ['#3b82f6', '#06b6d4', '#8b5cf6'],
                            borderColor: ['#1d4ed8', '#0e7490', '#7c3aed'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            },
            updateData(newCounts) {
                this.counts = newCounts;
                if (this.chart) {
                    this.chart.data.datasets[0].data = [
                        this.counts['3m'] || 0,
                        this.counts['2m'] || 0,
                        this.counts['1m'] || 0
                    ];
                    this.chart.update();
                }
            }
        };
    }


    document.addEventListener('livewire:load', () => {
        setInterval(() => {
            Livewire.emit('pollChartData');
        }, 60000); // 1 minute
    });
</script>
