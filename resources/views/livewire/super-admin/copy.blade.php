<div class="grid grid-cols-1 lg:grid-cols-2 gap-6" wire:poll.60s>

    <!-- Pie Chart Card -->
    <div class="box-container">
        <div class="box-header">
            <h2 class="box-title">My Transaction Overview</h2>
        </div>
        <div class="box-body"
            wire:ignore
            x-data="userTransactionChart({ borrowed: {{ $borrowedCount }}, returned: {{ $returnedCount }} })"
            x-init="init()"
            x-on:chartDataUpdated.window="updateData($event.detail)">

            @if ($borrowedCount == 0 && $returnedCount == 0)
                <div class="text-center text-gray-500 py-12">
                    <div class="flex items-center justify-center h-[250px]">
                        <p class="text-xl font-semibold text-gray-700">No transactions to display</p>
                    </div>
                </div>
            @else
                <canvas id="userTransactionChart" height="250"></canvas>
            @endif
            {{-- <canvas id="userTransactionChart" height="250"></canvas> --}}
        </div>
    </div>

    <!-- Right Column -->
    <div class="grid grid-cols-1 gap-6">
        <!-- Nested 3 Cards -->
        <div class="flex flex-col gap-4">
            <!-- Borrowed -->
            <div class="box-container bg-blue-100 p-4 text-center rounded-lg shadow">
                <h3 class="text-lg font-semibold">Borrowed Items</h3>
                <p class="text-2xl font-bold text-blue-700">{{ $borrowedCount }}</p>
            </div>

            <!-- Returned -->
            <div class="box-container bg-green-100 p-4 text-center rounded-lg shadow">
                <h3 class="text-lg font-semibold">Returned Items</h3>
                <p class="text-2xl font-bold text-green-700">{{ $returnedCount }}</p>
            </div>

            <!-- Recent Logs -->
            <div class="box-container bg-gray-100 p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-2">Recent Activities</h3>
                <ul class="text-sm space-y-1">
                    @foreach ($recentLogs as $log)
                        <li>📅 {{ $log->status }} — {{ \Carbon\Carbon::parse($log->action_date)->diffForHumans() }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Recent Activities Table -->
        <div class="box-container mt-4">
            <div class="box-header">
                <h2 class="box-title">Recent Logs</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="user-table w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Activity</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentActivities as $activity)
                            <tr>
                                <td>{{ $activity->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $activity->activity_name }}</td>
                                <td>{{ $activity->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-gray-500">No activity yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-container mt-2">
                {{ $recentActivities->links() }}
            </div>
        </div>
    </div>
</div>


<script>
function userTransactionChart(initialData) {
    return {
        chart: null,
        data: initialData,
        init() {
            const ctx = document.getElementById('userTransactionChart').getContext('2d');

            if (this.chart !== null) {
                this.chart.destroy();
            }

            this.chart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Borrowed', 'Returned'],
                    datasets: [{
                        data: [this.data.borrowed, this.data.returned],
                        backgroundColor: ['#93c5fd', '#6ee7b7'],
                        borderColor: ['#60a5fa', '#34d399'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return `${context.label}: ${context.parsed}`;
                                }
                            }
                        }
                    }
                }
            });          
          
        },
        updateData(newData) {
            this.data = newData;
            if (this.chart) {
                this.chart.data.datasets[0].data = [
                    this.data.borrowed,
                    this.data.returned
                ];
                this.chart.update();
            }
        }
    };
}
console.log(userTransactionChart)
</script>
