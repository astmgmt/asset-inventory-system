<div class="superadmin-container">
    <h1 class="page-title main-title">
        Warranty & Subscription Monitoring
    </h1>

    <!-- First Section: Asset Monitoring -->
    <div class="section-wrapper p-6 rounded-lg mb-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Asset Table -->
            <div class="box-container p-3 rounded-md">
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
                                @if($hasExpiredAssets)
                                    <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expiringAssets as $asset)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="Brand" class="text-center">{{ $asset->name }}</td>
                                    <td data-label="Model" class="text-center">{{ $asset->model_number }}</td>
                                    <td data-label="Asset Code" class="text-center">{{ $asset->asset_code }}</td>
                                    <td data-label="Expiration Date" class="text-center">
                                        {{ $asset->warranty_expiration->format('M d, Y') }}
                                    </td>
                                    <td data-label="Status" class="text-center">
                                        @if($asset->expiry_status === 'expired')
                                            <span class="status-badge bg-red-500 text-white">Expired</span>
                                        @elseif($asset->expiry_status === 'warning_3m')
                                            <span class="status-badge bg-orange-300 text-orange-900">⚠️ 3 months left</span>
                                        @elseif($asset->expiry_status === 'warning_2m')
                                            <span class="status-badge bg-orange-400 text-orange-900">⚠️ 2 months left</span>
                                        @elseif($asset->expiry_status === 'warning_1m')
                                            <span class="status-badge bg-orange-500 text-white">⚠️ 1 month left</span>
                                        @endif
                                    </td>
                                    <!-- Action Column for Remove Button -->
                                    @if($hasExpiredAssets)
                                        <td data-label="Action" class="text-center">
                                            @if($asset->expiry_status === 'expired' && ($user->isAdmin() || $user->isSuperAdmin()))
                                                <button 
                                                    wire:click="removeAsset({{ $asset->id }})" 
                                                    class="inline-flex items-center text-red-600 hover:text-red-800 text-sm font-medium focus:outline-none"
                                                    title="Remove Asset"
                                                >
                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                </button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="no-software-row">No assets found</td>
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
            <div class="box-container p-3 rounded-md" wire:ignore>
                <div class="box-header">
                    <h2 class="box-title">Asset Expiration Overview</h2>
                </div>
                <div class="box-body"
                    x-data="assetChart(@js($assetCounts))"
                    x-init="init()"
                    wire:key="asset-chart"
                    x-on:chartDataUpdated.window="updateData($event.detail.assetCounts)">
                    <canvas id="assetExpiryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Section: Software Monitoring -->
    <div class="section-wrapper p-6 rounded-lg">
        <h2 class="text-lg font-semibold text-gray-800 mb-4"><!-- Software Monitoring --></h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Software Table -->
            <div class="box-container p-3 rounded-md">
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
                                @if($hasExpiredSoftware)
                                    <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expiringSoftware as $software)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="Software Name" class="text-center">{{ $software->software_name }}</td>
                                    <td data-label="Software Code" class="text-center">{{ $software->software_code }}</td>
                                    <td data-label="Expiration Date" class="text-center">
                                        {{ $software->expiry_date->format('M d, Y') }}
                                    </td>
                                    <td data-label="Status" class="text-center">
                                        @if($software->expiry_status === 'expired')
                                            <span class="status-badge bg-red-500 text-white">Expired</span>
                                        @elseif($software->expiry_status === 'warning_3m')
                                            <span class="status-badge bg-orange-300 text-orange-900">⚠️ 3 months left</span>
                                        @elseif($software->expiry_status === 'warning_2m')
                                            <span class="status-badge bg-orange-400 text-orange-900">⚠️ 2 months left</span>
                                        @elseif($software->expiry_status === 'warning_1m')
                                            <span class="status-badge bg-orange-500 text-white">⚠️ 1 month left</span>
                                        @endif
                                    </td>
                                    <!-- Action Column for Remove Button -->
                                    @if($hasExpiredSoftware)
                                        <td data-label="Action" class="text-center">
                                            @if($software->expiry_status === 'expired' && ($user->isAdmin() || $user->isSuperAdmin()))
                                                <button 
                                                    wire:click="removeSoftware({{ $software->id }})"
                                                    class="inline-flex items-center text-red-600 hover:text-red-800 text-sm font-medium focus:outline-none"
                                                    title="Remove Software" 
                                                >
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="no-software-row">No software found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div mt-6 mb-4 pagination-container>
                        {{ $expiringSoftware->links() }}
                    </div>
                </div>
            </div>

            <!-- Software Chart -->
            <div class="box-container p-3 rounded-md" wire:ignore>
                <div class="box-header">
                    <h2 class="box-title">Software Expiration Overview</h2>
                </div>
                <div class="box-body"                    
                    x-data="softwareChart(@js($softwareCounts))"
                    x-init="init()"
                    wire:key="software-chart"
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

                window.addEventListener('theme-changed', () => {
                    if (this.chart) {
                        this.chart.destroy();
                    }
                    this.renderChart();
                });
            },
            renderChart() {
                const ctx = document.getElementById('assetExpiryChart');
                if (!ctx) return;

                const isDarkMode = document.documentElement.classList.contains('dark');
                const textColor = isDarkMode ? '#e2e8f0' : '#1a202c';
                const gridColor = isDarkMode ? '#4a5568' : '#e5e7eb';
                const tooltipBg = isDarkMode ? '#2d3748' : '#ffffff';

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
                        plugins: {
                            legend: {
                                labels: {
                                    color: textColor
                                }
                            },
                            tooltip: {
                                backgroundColor: tooltipBg,
                                titleColor: textColor,
                                bodyColor: textColor,
                                footerColor: textColor
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
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

                window.addEventListener('theme-changed', () => {
                    if (this.chart) {
                        this.chart.destroy();
                    }
                    this.renderChart();
                });
            },
            renderChart() {
                const ctx = document.getElementById('softwareExpiryChart');
                if (!ctx) return;

                const isDarkMode = document.documentElement.classList.contains('dark');
                const textColor = isDarkMode ? '#e2e8f0' : '#1a202c';
                const gridColor = isDarkMode ? '#4a5568' : '#e5e7eb';
                const tooltipBg = isDarkMode ? '#2d3748' : '#ffffff';

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
                        plugins: {
                            legend: {
                                labels: {
                                    color: textColor
                                }
                            },
                            tooltip: {
                                backgroundColor: tooltipBg,
                                titleColor: textColor,
                                bodyColor: textColor,
                                footerColor: textColor
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
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
        }, 60000); 
    });
</script>

