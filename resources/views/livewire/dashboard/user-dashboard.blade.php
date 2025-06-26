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

            <!-- Always show canvas -->
            <canvas id="userTransactionChart" height="250"></canvas>

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

            <div class="box-container bg-gray-100 p-4 rounded-lg shadow">
                <div class="box-header">
                    <h2 class="box-title text-center">Recent Transactions</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="user-table w-full bg-gray-50">
                        <thead>
                            <tr>
                                <th class="bg-gray-50">Date</th>
                                <th class="bg-gray-50">Activity</th>
                                <th class="bg-gray-50">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentLogs as $log)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->action_date)->format('M d, Y h:i A') }}</td>
                                    <td>
                                        @switch($log->status)
                                            @case('Borrow Approved')
                                                Borrow Request Approved
                                                @break
                                            @case('Borrow Denied')
                                                Borrow Request Rejected
                                                @break
                                            @case('Return Approved')
                                                Return Request Approved
                                                @break
                                            @case('Return Denied')
                                                Return Request Rejected
                                                @break
                                            @default
                                                {{ $log->status }}
                                        @endswitch
                                    </td>
                                    <td>
                                        @if(in_array($log->status, ['Borrow Approved', 'Return Approved']))
                                            <span class="status-badge approved">Approved</span>
                                        @else
                                            <span class="status-badge rejected">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-500">No transactions yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            
        </div>

        <!-- RECENT LOGS SECTION -->
        <div class="box-container bg-orange-100 p-4 text-center rounded-lg shadow">
            <div class="box-header">
                <h2 class="box-title">📅 Recent Logs</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="user-table w-full bg-orange-50">
                    <thead>
                        <tr>
                            <th class="bg-orange-50">Date</th>
                            <th class="bg-orange-50">Activity</th>
                            <th class="bg-orange-50">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentActivities as $activity)
                           <tr>
                                <td>{{ $activity->created_at->format('M d, Y h:i A') }}</td>

                                <td>
                                    @switch($activity->activity_name)
                                        @case('login')
                                            You're logged in now 😊
                                            @break

                                        @case('logout')
                                            You logged out 😢
                                            @break

                                        @case('password_changed')
                                            Password changed 🔐
                                            @break

                                        @case('profile_updated')
                                            Profile updated 🖊️
                                            @break

                                        @case('email_updated')
                                            Email updated 📧
                                            @break

                                        @case('2fa_enabled')
                                            2FA enabled 🔒
                                            @break

                                        @case('2fa_disabled')
                                            2FA disabled 🚫🔒
                                            @break

                                        @default
                                            {{ ucfirst(str_replace('_', ' ', $activity->activity_name)) }}
                                    @endswitch
                                </td>
                                <td>
                                    @if ($activity->activity_name === 'login' && $activity->status === 'active')
                                        🟢 
                                    @elseif ($activity->activity_name === 'logout' && $activity->status === 'inactive')
                                        ⚪  
                                    @elseif ($activity->activity_name === 'password_changed')
                                        🟠 
                                    @elseif ($activity->activity_name === 'email_updated')
                                        🟡 
                                    @else
                                        🔴
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-gray-500">No activity yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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

            const isEmpty = this.data.borrowed === 0 && this.data.returned === 0;

            const labels = isEmpty ? ['No Data'] : ['Borrowed', 'Returned'];
            const dataValues = isEmpty ? [1] : [this.data.borrowed, this.data.returned];
            const backgroundColors = isEmpty ? ['#e5e7eb'] : ['#93c5fd', '#6ee7b7'];  
            const borderColors = isEmpty ? ['#d1d5db'] : ['#60a5fa', '#34d399'];

            this.chart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataValues,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            display: !isEmpty 
                        },
                        tooltip: {
                            enabled: !isEmpty, 
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

            const isEmpty = this.data.borrowed === 0 && this.data.returned === 0;

            this.chart.data.labels = isEmpty ? ['No Data'] : ['Borrowed', 'Returned'];
            this.chart.data.datasets[0].data = isEmpty ? [1] : [this.data.borrowed, this.data.returned];
            this.chart.data.datasets[0].backgroundColor = isEmpty ? ['#f9fafb'] : ['#93c5fd', '#6ee7b7'];
            this.chart.data.datasets[0].borderColor = isEmpty ? ['#d1d5db'] : ['#60a5fa', '#34d399'];
            this.chart.options.plugins.legend.display = !isEmpty;
            this.chart.options.plugins.tooltip.enabled = !isEmpty;

            this.chart.update();
        }
    };
}
</script>
