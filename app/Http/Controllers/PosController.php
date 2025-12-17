<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Get the current store from session.
     */
    protected function getCurrentStore()
    {
        $storeId = session('current_store_id');
        return Auth::user()->stores()->findOrFail($storeId);
    }

    /**
     * Display the POS interface.
     */
    public function index()
    {
        $store = $this->getCurrentStore();
        
        $products = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = $products->pluck('category')->unique()->filter();

        // Get income account for sales (default or first available)
        $incomeAccount = Account::where('store_id', $store->id)
            ->where('type', 'income')
            ->first();

        // Get customers for debt recording
        $customers = \App\Models\Contact::where('store_id', $store->id)
            ->where('type', 'customer')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('store', 'products', 'categories', 'incomeAccount', 'customers'));
    }

    /**
     * Process the checkout and create transaction.
     */
    public function checkout(Request $request)
    {
        $store = $this->getCurrentStore();

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_amount' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Get income account
            $incomeAccount = Account::where('store_id', $store->id)
                ->where('type', 'income')
                ->first();

            if (!$incomeAccount) {
                throw new \Exception('Tidak ada akun pemasukan. Silakan buat akun pemasukan terlebih dahulu.');
            }

            // Calculate total
            $total = 0;
            $itemsData = [];
            
            foreach ($validated['items'] as $item) {
                // Use pessimistic locking to prevent race condition
                $product = Product::where('store_id', $store->id)
                    ->lockForUpdate()
                    ->findOrFail($item['product_id']);

                // Check stock availability
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi. Tersedia: {$product->stock_quantity}");
                }

                // Use bcmath for precise decimal calculations
                $subtotal = bcmul((string)$item['quantity'], (string)$item['unit_price'], 2);
                $total = bcadd((string)$total, $subtotal, 2);

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => round($item['unit_price'], 2),
                    'subtotal' => round((float)$subtotal, 2),
                ];
            }

            // Calculate debt logic
            $totalFloat = round((float)$total, 2);
            $paymentFloat = round($validated['payment_amount'], 2);
            $debtAmount = 0;
            $change = 0;
            $customerId = null;

            if ($paymentFloat < $totalFloat) {
                // Determine debt
                if (empty($validated['customer_name'])) {
                    throw new \Exception('Pembayaran kurang dari total. Harap isi Nama Pelanggan untuk mencatat piutang.');
                }

                $debtAmount = $totalFloat - $paymentFloat;

                // Find or Create Customer
                // Search for existing active customer with same name
                $customer = \App\Models\Contact::where('store_id', $store->id)
                    ->where('type', 'customer')
                    ->where('name', $validated['customer_name'])
                    ->when(!empty($validated['customer_phone']), function($q) use ($validated) {
                         return $q->where('phone', $validated['customer_phone']);
                    })
                    ->first();

                if (!$customer) {
                    $customer = \App\Models\Contact::create([
                        'store_id' => $store->id,
                        'name' => $validated['customer_name'],
                        'phone' => $validated['customer_phone'] ?? null,
                        'type' => 'customer',
                        'is_active' => true,
                        // Address and notes handling could be added if needed
                    ]);
                }
                
                $customerId = $customer->id;

            } else {
                $change = $paymentFloat - $totalFloat;
            }

            // Create transaction (FULL AMOUNT as Revenue)
            $transaction = Transaction::create([
                'store_id' => $store->id,
                'account_id' => $incomeAccount->id,
                'user_id' => Auth::id(),
                'type' => 'income',
                'amount' => $total,
                'transaction_date' => now(),
                'description' => ($validated['notes'] ?? 'Penjualan POS') . ($debtAmount > 0 ? " (Piutang: Rp " . number_format($debtAmount, 0, ',', '.') . ")" : ""),
            ]);

            // Create Debt record if needed
            if ($debtAmount > 0 && $customerId) {
                \App\Models\Debt::create([
                    'store_id' => $store->id,
                    'contact_id' => $customerId,
                    'user_id' => Auth::id(),
                    'type' => 'receivable', // Piutang
                    'total_amount' => $debtAmount,
                    'paid_amount' => 0,
                    'status' => 'unpaid',
                    'debt_date' => now(),
                    'due_date' => null,
                    'description' => 'Piutang dari Transaksi POS #' . $transaction->id,
                ]);
            }

            // Create transaction items and update stock
            foreach ($itemsData as $itemData) {
                $product = $itemData['product'];
                
                // Create transaction item
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['subtotal'],
                ]);

                // Record stock movement
                $stockBefore = $product->stock_quantity;
                $stockAfter = $stockBefore - $itemData['quantity'];

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'transaction_item_id' => null,
                    'type' => 'out',
                    'quantity' => $itemData['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => 'Penjualan POS #' . $transaction->id,
                ]);

                // Update product stock
                $product->update(['stock_quantity' => $stockAfter]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'total' => $totalFloat,
                    'payment' => $paymentFloat,
                    'change' => $change,
                    'debt_amount' => $debtAmount,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get product data for POS (AJAX).
     */
    public function getProducts(Request $request)
    {
        $store = $this->getCurrentStore();
        
        $query = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0);

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        $products = $query->with('category')->orderBy('name')->get();

        return response()->json([
            'products' => $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'price' => $p->selling_price,
                'stock' => $p->stock_quantity,
                'category' => $p->category?->name,
            ]),
        ]);
    }
}
