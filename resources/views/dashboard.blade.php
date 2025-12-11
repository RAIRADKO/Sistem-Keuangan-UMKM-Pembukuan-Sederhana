<x-app-layout>
    <div class="page-container">
        <!-- Page Header - Clear hierarchy -->
        <div class="page-header animate-fade-in">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="page-title">{{ $store->name }}</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">Ringkasan keuangan bulan ini</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('transactions.create', ['type' => 'income']) }}" class="btn btn-success">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Pemasukan
                    </a>
                    <a href="{{ route('transactions.create', ['type' => 'expense']) }}" class="btn btn-danger">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                        Pengeluaran
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Grid - Premium Design -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 section">
            <!-- Total Income -->
            <div class="stat-card stat-card-income animate-fade-in-up stagger-1" style="opacity: 0;">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="stat-label">Pemasukan</p>
                        <p class="stat-value text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($totalIncome, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-income">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Expense -->
            <div class="stat-card stat-card-expense animate-fade-in-up stagger-2" style="opacity: 0;">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="stat-label">Pengeluaran</p>
                        <p class="stat-value text-rose-600 dark:text-rose-400">
                            Rp {{ number_format($totalExpense, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-expense">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Net Balance -->
            <div class="stat-card stat-card-balance animate-fade-in-up stagger-3" style="opacity: 0;">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="stat-label">Saldo Bersih</p>
                        <p class="stat-value {{ $balance >= 0 ? 'text-cyan-600 dark:text-cyan-400' : 'text-orange-600 dark:text-orange-400' }}">
                            Rp {{ number_format($balance, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="stat-icon stat-icon-balance">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section - Clean with generous whitespace -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 section">
            <!-- Trend Chart - Takes more space (hierarchy) -->
            <div class="lg:col-span-3 card animate-fade-in-up stagger-4" style="opacity: 0;">
                <div class="card-body">
                    <h2 class="section-title mb-6">Tren 6 Bulan Terakhir</h2>
                    <div class="h-72">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Expenses - Secondary importance -->
            <div class="lg:col-span-2 card animate-fade-in-up stagger-4" style="opacity: 0;">
                <div class="card-body">
                    <h2 class="section-title mb-6">Pengeluaran Terbesar</h2>
                    @if($topExpenses->count() > 0)
                        <div class="space-y-5">
                            @foreach($topExpenses as $expense)
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $expense->account->name }}</span>
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($expense->total, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-bar-fill expense" style="width: {{ $totalExpense > 0 ? ($expense->total / $totalExpense * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state py-12">
                            <p class="text-slate-400">Belum ada pengeluaran</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Transactions - Clean table -->
        <div class="card animate-fade-in-up" style="opacity: 0; animation-delay: 0.5s;">
            <div class="card-header flex items-center justify-between">
                <h2 class="section-title">Transaksi Terbaru</h2>
                <a href="{{ route('transactions.index') }}" class="text-sm text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 font-semibold transition-colors">
                    Lihat Semua →
                </a>
            </div>
            
            @if($recentTransactions->count() > 0)
                <div class="table-container border-0 rounded-none">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach($recentTransactions as $transaction)
                                <tr>
                                    <td class="text-slate-500 dark:text-slate-400">
                                        {{ $transaction->transaction_date->format('d M Y') }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $transaction->type === 'income' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $transaction->account->name }}
                                        </span>
                                    </td>
                                    <td class="text-slate-600 dark:text-slate-300 max-w-xs truncate">
                                        {{ $transaction->description ?? '-' }}
                                    </td>
                                    <td class="text-right {{ $transaction->type === 'income' ? 'money-positive' : 'money-negative' }}">
                                        {{ $transaction->type === 'income' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="empty-state-title">Belum ada transaksi</p>
                    <p class="empty-state-text">Mulai catat pemasukan dan pengeluaran Anda</p>
                    <a href="{{ route('transactions.create') }}" class="btn btn-primary">Tambah Transaksi Pertama</a>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        const trendData = @json($monthlyTrend);
        
        const ctx = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: trendData.map(d => d.month),
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: trendData.map(d => d.income),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                    },
                    {
                        label: 'Pengeluaran',
                        data: trendData.map(d => d.expense),
                        backgroundColor: 'rgba(244, 63, 94, 0.8)',
                        borderColor: 'rgba(244, 63, 94, 1)',
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 24,
                            font: { size: 12, weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        padding: 14,
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 12 },
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            font: { size: 11 },
                            callback: function(value) {
                                if (value >= 1000000) return (value/1000000).toFixed(0) + 'jt';
                                if (value >= 1000) return (value/1000).toFixed(0) + 'rb';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
