<x-app-layout>
    <div class="page-container max-w-2xl">
        <!-- Page Header -->
        <div class="page-header animate-fade-in">
            <div class="flex items-center gap-4">
                <a href="{{ route('debts.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="page-title">Catat {{ $type === 'payable' ? 'Hutang' : 'Piutang' }}</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ $store->name }}</p>
                </div>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="mb-6 p-4 rounded-xl {{ $type === 'payable' ? 'bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800' : 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800' }} animate-fade-in-up" style="opacity: 0; animation-delay: 0.05s;">
            <p class="text-sm {{ $type === 'payable' ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                @if($type === 'payable')
                    <strong>Hutang</strong> adalah uang yang Anda pinjam dari supplier. Anda harus membayar kembali.
                @else
                    <strong>Piutang</strong> adalah uang yang dipinjam pelanggan dari Anda. Pelanggan harus membayar kembali.
                @endif
            </p>
        </div>

        <!-- Form -->
        <div class="card animate-fade-in-up" style="opacity: 0; animation-delay: 0.1s;">
            <form method="POST" action="{{ route('debts.store') }}" class="p-6 space-y-6">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">

                <div>
                    <label class="form-label">Data {{ $type === 'payable' ? 'Supplier' : 'Pelanggan' }} <span class="text-rose-500">*</span></label>
                    <div class="space-y-3">
                        <input type="text" name="contact_name" value="{{ old('contact_name') }}" required 
                               placeholder="Nama {{ $type === 'payable' ? 'Supplier' : 'Pelanggan' }}"
                               class="form-input @error('contact_name') border-rose-500 @enderror">
                        @error('contact_name')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror

                        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" 
                               placeholder="Nomor Telepon (Opsional)"
                               class="form-input @error('contact_phone') border-rose-500 @enderror">
                    </div>
                </div>

                <div>
                    <label for="total_amount" class="form-label">Jumlah <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">Rp</span>
                        <input type="number" id="total_amount" name="total_amount" value="{{ old('total_amount') }}" required min="0" step="1"
                               class="form-input pl-12 @error('total_amount') border-rose-500 @enderror"
                               placeholder="0">
                    </div>
                    @error('total_amount')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="debt_date" class="form-label">Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" id="debt_date" name="debt_date" value="{{ old('debt_date', date('Y-m-d')) }}" required
                               class="form-input @error('debt_date') border-rose-500 @enderror">
                        @error('debt_date')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="due_date" class="form-label">Jatuh Tempo</label>
                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}"
                               class="form-input @error('due_date') border-rose-500 @enderror">
                        @error('due_date')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="form-label">Keterangan</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-input @error('description') border-rose-500 @enderror"
                              placeholder="Keterangan tambahan (opsional)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <a href="{{ route('debts.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn {{ $type === 'payable' ? 'btn-danger' : 'btn-success' }}">
                        Simpan {{ $type === 'payable' ? 'Hutang' : 'Piutang' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
