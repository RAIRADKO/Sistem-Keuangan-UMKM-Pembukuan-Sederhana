<x-app-layout>
    <div class="min-h-screen bg-slate-100 dark:bg-slate-900">
        <!-- POS Header -->
        <div class="bg-white dark:bg-slate-800 shadow-md px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">Mode Kasir</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $store->name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500 dark:text-slate-400">{{ now()->format('d M Y, H:i') }}</span>
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ Auth::user()->name }}</span>
            </div>
        </div>

        <div class="flex h-[calc(100vh-64px)]">
            <!-- Product Grid (Left Side) -->
            <div class="flex-1 p-4 overflow-y-auto">
                <!-- Search & Filter -->
                <div class="mb-4 flex gap-3">
                    <div class="flex-1 relative">
                        <input type="text" id="searchInput" placeholder="Cari produk..." 
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
                        <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <select id="categoryFilter" class="px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            @if($category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Product Grid -->
                <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($products as $product)
                        <div class="product-card bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-md hover:shadow-xl cursor-pointer transition-all duration-200 hover:-translate-y-1 border-2 border-transparent hover:border-cyan-500"
                             data-id="{{ $product->id }}"
                             data-name="{{ $product->name }}"
                             data-price="{{ $product->selling_price }}"
                             data-stock="{{ $product->stock_quantity }}"
                             data-category="{{ $product->category_id }}"
                             onclick="addToCart(this)">
                            <div class="w-full h-24 rounded-xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900/30 dark:to-teal-900/30 flex items-center justify-center mb-3">
                                <svg class="w-10 h-10 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <h3 class="font-medium text-slate-900 dark:text-white text-sm truncate">{{ $product->name }}</h3>
                            <p class="text-cyan-600 dark:text-cyan-400 font-bold mt-1">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Stok: {{ $product->stock_quantity }}</p>
                        </div>
                    @endforeach
                </div>

                @if($products->isEmpty())
                    <div class="text-center py-16">
                        <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="text-slate-500">Tidak ada produk tersedia</p>
                        <a href="{{ route('products.create') }}" class="btn btn-primary mt-4">Tambah Produk</a>
                    </div>
                @endif
            </div>

            <!-- Cart (Right Side) -->
            <div class="w-96 bg-white dark:bg-slate-800 shadow-xl flex flex-col">
                <!-- Cart Header -->
                <div class="p-4 border-b border-slate-100 dark:border-slate-700">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Keranjang
                        <span id="cartCount" class="ml-auto bg-cyan-500 text-white text-xs font-bold px-2 py-1 rounded-full">0</span>
                    </h2>
                </div>

                <!-- Cart Items -->
                <div id="cartItems" class="flex-1 overflow-y-auto p-4 space-y-3">
                    <div id="emptyCartMessage" class="text-center py-8 text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p>Keranjang kosong</p>
                        <p class="text-sm mt-1">Klik produk untuk menambahkan</p>
                    </div>
                </div>

                <!-- Cart Footer -->
                <div class="p-4 border-t border-slate-100 dark:border-slate-700 space-y-4">
                    <!-- Customer Form (Visible when needed for Debt or optional) -->
                    <div id="customerForm" class="space-y-2" style="display: none;">
                        <label class="block text-sm font-medium text-slate-600 dark:text-slate-400">Data Pelanggan (Wajib jika Hutang)</label>
                        <input type="text" id="customerName" placeholder="Nama Pelanggan" 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500 text-sm">
                        <input type="text" id="customerPhone" placeholder="No. HP (Opsional)" 
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500 text-sm">
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium text-slate-600 dark:text-slate-400">Total</span>
                        <span id="cartTotal" class="text-2xl font-bold text-slate-900 dark:text-white">Rp 0</span>
                    </div>

                    <!-- Payment Mode Toggle -->
                    <div class="grid grid-cols-2 gap-2 mb-4 bg-slate-100 dark:bg-slate-700 p-1 rounded-xl">
                        <button onclick="setPaymentMode('cash')" id="btnModeCash" class="py-2 rounded-lg font-bold text-sm transition-all shadow-sm bg-white dark:bg-slate-600 text-slate-900 dark:text-white">
                            Tunai
                        </button>
                        <button onclick="setPaymentMode('debt')" id="btnModeDebt" class="py-2 rounded-lg font-bold text-sm transition-all text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                            Hutang / Tempo
                        </button>
                    </div>

                    <!-- Payment Input -->
                    <div>
                        <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Bayar</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">Rp</span>
                            <input type="number" id="paymentInput" placeholder="0" 
                                   class="w-full pl-12 pr-4 py-3 text-xl font-bold rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500">
                        </div>
                    </div>

                    <!-- Change / Debt -->
                    <div class="flex justify-between items-center text-lg">
                        <span id="changeLabel" class="font-medium text-slate-600 dark:text-slate-400">Kembalian</span>
                        <span id="changeAmount" class="font-bold text-emerald-600 dark:text-emerald-400">Rp 0</span>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div id="quickAmountButtons" class="grid grid-cols-4 gap-2">
                        <button onclick="quickPay(10000)" class="py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-sm">10K</button>
                        <button onclick="quickPay(20000)" class="py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-sm">20K</button>
                        <button onclick="quickPay(50000)" class="py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-sm">50K</button>
                        <button onclick="quickPay(100000)" class="py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors text-sm">100K</button>
                    </div>
                    <button onclick="payExact()" id="btnExact" class="w-full py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Uang Pas</button>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="clearCart()" class="py-3 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
                            Hapus Semua
                        </button>
                        <button onclick="processCheckout()" id="checkoutBtn" disabled class="py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-teal-500 text-white font-bold hover:from-cyan-600 hover:to-teal-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            Bayar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 max-w-md w-full mx-4 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Transaksi Berhasil!</h3>
            <p id="successMessage" class="text-slate-600 dark:text-slate-400 mb-6"></p>
            <button onclick="closeSuccessModal()" class="w-full py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-teal-500 text-white font-bold hover:from-cyan-600 hover:to-teal-600">
                Transaksi Baru
            </button>
        </div>
    </div>

    <script>
        let cart = [];
        let currentMode = 'cash'; // 'cash' or 'debt'

        function setPaymentMode(mode) {
            currentMode = mode;
            const btnCash = document.getElementById('btnModeCash');
            const btnDebt = document.getElementById('btnModeDebt');
            const customerForm = document.getElementById('customerForm');
            const quickButtons = document.getElementById('quickAmountButtons');
            const btnExact = document.getElementById('btnExact');
            const paymentInput = document.getElementById('paymentInput');

            if (mode === 'cash') {
                // Style Active
                btnCash.className = "py-2 rounded-lg font-bold text-sm transition-all shadow-sm bg-white dark:bg-slate-600 text-slate-900 dark:text-white";
                btnDebt.className = "py-2 rounded-lg font-bold text-sm transition-all text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200";
                
                // Behavior
                customerForm.style.display = 'none';
                quickButtons.style.display = 'grid';
                btnExact.style.display = 'block';
                
                // Auto fill payment with total for convenience
                payExact(); 
            } else {
                // Style Active
                btnDebt.className = "py-2 rounded-lg font-bold text-sm transition-all shadow-sm bg-white dark:bg-slate-600 text-slate-900 dark:text-white";
                btnCash.className = "py-2 rounded-lg font-bold text-sm transition-all text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200";
                
                // Behavior
                customerForm.style.display = 'block';
                quickButtons.style.display = 'none'; // Hide quick pay for debt
                btnExact.style.display = 'none';
                
                // Reset payment to 0 (since it's debt/credit)
                paymentInput.value = 0;
                document.getElementById('customerName').focus(); // Focus on name
            }
            updateChange();
        }

        function formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }

        function addToCart(element) {
            const id = element.dataset.id;
            const name = element.dataset.name;
            const price = parseFloat(element.dataset.price);
            const stock = parseInt(element.dataset.stock);

            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                if (existingItem.quantity < stock) {
                    existingItem.quantity++;
                } else {
                    alert('Stok tidak mencukupi!');
                    return;
                }
            } else {
                cart.push({ id, name, price, stock, quantity: 1 });
            }

            renderCart();
            
            // Re-apply current mode logic (e.g. update totals)
            if (currentMode === 'cash') {
                payExact();
            } else {
                updateChange();
            }
        }

        function updateQuantity(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            const newQty = item.quantity + delta;
            if (newQty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (newQty <= item.stock) {
                item.quantity = newQty;
            } else {
                alert('Stok tidak mencukupi!');
                return;
            }

            renderCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(item => item.id !== id);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            const emptyMessage = document.getElementById('emptyCartMessage');
            const countBadge = document.getElementById('cartCount');
            const totalDisplay = document.getElementById('cartTotal');
            const checkoutBtn = document.getElementById('checkoutBtn');

            if (cart.length === 0) {
                container.innerHTML = `
                    <div id="emptyCartMessage" class="text-center py-8 text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p>Keranjang kosong</p>
                        <p class="text-sm mt-1">Klik produk untuk menambahkan</p>
                    </div>`;
                countBadge.textContent = '0';
                totalDisplay.textContent = 'Rp 0';
                checkoutBtn.disabled = true;
                return;
            }

            let html = '';
            let total = 0;
            let itemCount = 0;

            cart.forEach(item => {
                const subtotal = item.price * item.quantity;
                total += subtotal;
                itemCount += item.quantity;

                html += `
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-medium text-slate-900 dark:text-white text-sm">${item.name}</h4>
                                <p class="text-cyan-600 dark:text-cyan-400 text-sm">${formatRupiah(item.price)}</p>
                            </div>
                            <button onclick="removeFromCart('${item.id}')" class="text-slate-400 hover:text-rose-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <button onclick="updateQuantity('${item.id}', -1)" class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-300 dark:hover:bg-slate-600">-</button>
                                <span class="w-8 text-center font-bold text-slate-900 dark:text-white">${item.quantity}</span>
                                <button onclick="updateQuantity('${item.id}', 1)" class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-300 dark:hover:bg-slate-600">+</button>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white">${formatRupiah(subtotal)}</span>
                        </div>
                    </div>`;
            });

            container.innerHTML = html;
            countBadge.textContent = itemCount;
            totalDisplay.textContent = formatRupiah(total);
            checkoutBtn.disabled = false;

            updateChange();
            
            // Initialize mode state on first render if needed, or keep existing
             if (currentMode === 'cash' && document.getElementById('paymentInput').value === '') {
                 // payExact(); // Don't auto-pay on every render if user is typing
             }
        }

        function getTotal() {
            return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }

        function quickPay(amount) {
            const current = parseFloat(document.getElementById('paymentInput').value) || 0;
            document.getElementById('paymentInput').value = current + amount;
            updateChange();
        }

        function payExact() {
            document.getElementById('paymentInput').value = getTotal();
            updateChange();
        }

        function updateChange() {
            const total = getTotal();
            const payment = parseFloat(document.getElementById('paymentInput').value) || 0;
            const change = payment - total;
            const changeLabel = document.getElementById('changeLabel');
            const changeAmount = document.getElementById('changeAmount');
            const checkoutBtn = document.getElementById('checkoutBtn');
            const customerName = document.getElementById('customerName').value.trim();

            if (change < 0) {
                // Kurang bayar (Potensi Hutang)
                changeLabel.textContent = 'Sisa (Hutang)';
                changeAmount.textContent = formatRupiah(Math.abs(change));
                changeAmount.className = 'font-bold text-rose-600 dark:text-rose-400';
                
                // Check requirements based on mode
                if (currentMode === 'debt' || change < 0) { 
                    // If mode is debt OR implicitly debt due to underpayment
                     if (customerName) {
                        checkoutBtn.disabled = false;
                        checkoutBtn.textContent = 'Catat Hutang';
                        checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                     } else {
                        checkoutBtn.textContent = 'Isi Nama Pelanggan!';
                        checkoutBtn.disabled = true;
                        checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
                     }
                }
            } else {
                changeLabel.textContent = 'Kembalian';
                changeAmount.textContent = formatRupiah(change);
                changeAmount.className = 'font-bold text-emerald-600 dark:text-emerald-400';
                checkoutBtn.disabled = false;
                checkoutBtn.textContent = 'Bayar';
                checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        document.getElementById('customerName').addEventListener('input', updateChange);
        document.getElementById('paymentInput').addEventListener('input', updateChange);

        function processCheckout() {
            if (cart.length === 0) {
                alert('Keranjang kosong!');
                return;
            }

            const total = getTotal();
            const payment = parseFloat(document.getElementById('paymentInput').value) || 0;

            const customerName = document.getElementById('customerName').value.trim();

            if (payment < total) {
                if (!customerName) {
                    alert('Pembayaran kurang! Harap isi Nama Pelanggan untuk mencatat sebagai hutang.');
                    // Highlight input
                    document.getElementById('customerName').focus();
                    document.getElementById('customerName').classList.add('ring-2', 'ring-rose-500');
                    return;
                }
                const confirmDebt = confirm(`Pembayaran kurang Rp ${formatRupiah(total - payment)}. Sisa akan dicatat sebagai hutang atas nama "${customerName}". Lanjutkan?`);
                if (!confirmDebt) return;
            }

            const checkoutBtn = document.getElementById('checkoutBtn');
            checkoutBtn.disabled = true;
            checkoutBtn.textContent = 'Memproses...';

            fetch('{{ route("pos.checkout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    items: cart.map(item => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        unit_price: item.price,
                    })),
                    payment_amount: payment,
                    customer_name: document.getElementById('customerName').value.trim(),
                    customer_phone: document.getElementById('customerPhone').value.trim(),
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const change = payment - total;
                    let msg = `Total: ${formatRupiah(total)}<br>Bayar: ${formatRupiah(payment)}`;
                    
                    if (data.data.debt_amount > 0) {
                        msg += `<br><span class="text-rose-600 font-bold">Piutang: ${formatRupiah(data.data.debt_amount)}</span>`;
                    } else {
                        msg += `<br>Kembalian: ${formatRupiah(data.data.change)}`;
                    }
                    
                    document.getElementById('successMessage').innerHTML = msg;
                    document.getElementById('successModal').classList.remove('hidden');
                } else {
                    alert(data.message || 'Gagal memproses transaksi');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            })
            .finally(() => {
                checkoutBtn.disabled = false;
                checkoutBtn.textContent = 'Bayar';
            });
        }

        function closeSuccessModal() {
            document.getElementById('successModal').classList.add('hidden');
            cart = [];
            document.getElementById('paymentInput').value = '';
            document.getElementById('customerName').value = '';
            document.getElementById('customerPhone').value = '';
            renderCart();
            location.reload(); // Refresh to get updated stock
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const search = this.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name.toLowerCase();
                card.style.display = name.includes(search) ? '' : 'none';
            });
        });

        // Category filter
        document.getElementById('categoryFilter').addEventListener('change', function() {
            const categoryId = this.value;
            document.querySelectorAll('.product-card').forEach(card => {
                if (!categoryId || card.dataset.category === categoryId) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</x-app-layout>
