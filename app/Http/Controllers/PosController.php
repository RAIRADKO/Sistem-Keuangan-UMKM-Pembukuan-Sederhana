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

        return view('pos.index', compact('store', 'products', 'categories', 'incomeAccount'));
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

            // Create transaction
            $transaction = Transaction::create([
                'store_id' => $store->id,
                'account_id' => $incomeAccount->id,
                'user_id' => Auth::id(),
                'type' => 'income',
                'amount' => $total,
                'transaction_date' => now(),
                'description' => $validated['notes'] ?? 'Penjualan POS',
            ]);

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

            // Calculate change using bcmath for precision
            $change = bcsub((string)$validated['payment_amount'], (string)$total, 2);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'total' => round((float)$total, 2),
                    'payment' => round($validated['payment_amount'], 2),
                    'change' => round((float)$change, 2),
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
