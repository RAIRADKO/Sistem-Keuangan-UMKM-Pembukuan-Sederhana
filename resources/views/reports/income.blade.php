<x-app-layout>
    <div class="page-container">
        <!-- Page Header -->
        <div class="page-header animate-fade-in">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="page-title">📈 Laporan Pemasukan</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm">{{ $store->name }}</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('reports.export', ['type' => 'income', 'format' => 'pdf', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-danger">
                        📄 PDF
                    </a>
                    <a href="{{ route('reports.export', ['type' => 'income', 'format' => 'excel', 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success">
                        📊 Excel
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-8 animate-fade-in-up" style="opacity: 0; animation-delay: 0.1s;">
            <div class="p-6">
                <form method="GET" action="{{ route('reports.income') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <select name="account_id" class="form-input">
                            <option value="">Semua</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="btn btn-primary w-full">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 dark:from-emerald-900/30 dark:via-teal-900/30 dark:to-cyan-900/30 rounded-2xl p-8 mb-8 border border-emerald-100 dark:border-emerald-800 animate-fade-in-up" style="opacity: 0; animation-delay: 0.2s;">
            <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Total Pemasukan ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})</p>
            <p class="text-4xl font-bold text-emerald-700 dark:text-emerald-300 mt-2">Rp {{ number_format($total, 0, ',', '.') }}</p>
        </div>

        <!-- By Category -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="card animate-fade-in-up" style="opacity: 0; animation-delay: 0.3s;">
                <div class="card-body">
                    <h3 class="section-title mb-6">Berdasarkan Kategori</h3>
                    @if($byCategory->count() > 0)
                        <div class="space-y-5">
                            @foreach($byCategory as $item)
                                <div>
                                    <div class="flex justify-between mb-2">
                                        <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $item['account']->name }}</span>
                                        <span class="font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($item['total'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-bar-fill" style="width: {{ $total > 0 ? ($item['total'] / $total * 100) : 0 }}%"></div>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $item['count'] }} transaksi</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 dark:text-slate-400 text-center py-8">Tidak ada data</p>
                    @endif
                </div>
            </div>

            <!-- Chart -->
            <div class="card animate-fade-in-up" style="opacity: 0; animation-delay: 0.35s;">
                <div class="card-body">
                    <h3 class="section-title mb-6">Distribusi Pemasukan</h3>
                    <canvas id="incomeChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="card animate-fade-in-up" style="opacity: 0; animation-delay: 0.4s;">
            <div class="card-body">
                <h3 class="section-title mb-6">Detail Transaksi</h3>
                <div class="table-container">
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
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td class="text-slate-500 dark:text-slate-400">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                    <td class="font-medium text-slate-900 dark:text-slate-100">{{ $transaction->account->name }}</td>
                                    <td class="text-slate-500 dark:text-slate-400">{{ $transaction->description ?? '-' }}</td>
                                    <td class="text-right money-positive">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-12 text-slate-500 dark:text-slate-400">Tidak ada transaksi dalam periode ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const categoryData = @json($byCategory);
        if (categoryData.length > 0) {
            const ctx = document.getElementById('incomeChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(d => d.account.name),
                    datasets: [{
                        data: categoryData.map(d => d.total),
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(6, 182, 212, 0.85)',
                            'rgba(59, 130, 246, 0.85)',
                            'rgba(168, 85, 247, 0.85)',
                            'rgba(249, 115, 22, 0.85)',
                        ],
                        borderWidth: 0,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: { size: 12, weight: '500' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            padding: 14,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
