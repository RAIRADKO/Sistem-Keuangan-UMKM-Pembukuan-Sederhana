<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $query = Transaction::where('store_id', $store->id)
            ->with(['account', 'user']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by account
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Filter by month/year
        if ($request->filled('month')) {
            $query->whereMonth('transaction_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('transaction_date', $request->year);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $accounts = Account::where('store_id', $store->id)->get();

        // Summary for filtered results
        $summary = [
            'total_income' => (clone $query)->where('type', 'income')->sum('amount'),
            'total_expense' => (clone $query)->where('type', 'expense')->sum('amount'),
        ];

        return view('transactions.index', compact('transactions', 'accounts', 'store', 'summary'));
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create(Request $request)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $type = $request->get('type', 'income');
        $accounts = Account::where('store_id', $store->id)
            ->where('type', $type)
            ->get();

        return view('transactions.create', compact('store', 'accounts', 'type'));
    }

    /**
     * Store a newly created transaction
     */
    public function store(StoreTransactionRequest $request)
    {
        $store = Auth::user()->currentStore();
        $validated = $request->validated();

        $validated['store_id'] = $store->id;
        $validated['user_id'] = Auth::id();

        // Handle file upload
        if ($request->hasFile('proof_file')) {
            $path = $request->file('proof_file')->store('proofs/' . $store->id, 'public');
            $validated['proof_file'] = $path;
        }

        Transaction::create($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dicatat!');
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing a transaction
     */
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $store = Auth::user()->currentStore();
        $accounts = Account::where('store_id', $store->id)
            ->where('type', $transaction->type)
            ->get();

        return view('transactions.edit', compact('transaction', 'store', 'accounts'));
    }

    /**
     * Update the specified transaction
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $store = Auth::user()->currentStore();
        $validated = $request->validated();

        // Handle file upload
        if ($request->hasFile('proof_file')) {
            // Delete old file
            if ($transaction->proof_file) {
                Storage::disk('public')->delete($transaction->proof_file);
            }
            $path = $request->file('proof_file')->store('proofs/' . $store->id, 'public');
            $validated['proof_file'] = $path;
        }

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui!');
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        // Delete proof file
        if ($transaction->proof_file) {
            Storage::disk('public')->delete($transaction->proof_file);
        }

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus!');
    }
}
