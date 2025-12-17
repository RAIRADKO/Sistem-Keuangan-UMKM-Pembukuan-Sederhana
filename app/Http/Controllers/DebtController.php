<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    /**
     * Display a listing of debts
     */
    public function index(Request $request)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $query = Debt::where('store_id', $store->id)
            ->with(['contact', 'user']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by contact
        if ($request->filled('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }

        // Filter overdue only
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('contact', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $debts = $query->orderByDesc('debt_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $contacts = Contact::where('store_id', $store->id)->active()->orderBy('name')->get();

        // Summary
        $summary = [
            'total_payable' => Debt::where('store_id', $store->id)
                ->payable()->unpaid()
                ->sum(DB::raw('total_amount - paid_amount')),
            'total_receivable' => Debt::where('store_id', $store->id)
                ->receivable()->unpaid()
                ->sum(DB::raw('total_amount - paid_amount')),
            'overdue_count' => Debt::where('store_id', $store->id)->overdue()->count(),
        ];

        return view('debts.index', compact('debts', 'contacts', 'store', 'summary'));
    }

    /**
     * Show the form for creating a new debt
     */
    public function create(Request $request)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $type = $request->get('type', 'payable');
        
        // Get contacts based on debt type
        $contactType = $type === 'payable' ? 'supplier' : 'customer';
        $contacts = Contact::where('store_id', $store->id)
            ->where('type', $contactType)
            ->active()
            ->orderBy('name')
            ->get();

        return view('debts.create', compact('store', 'type', 'contacts'));
    }

    /**
     * Store a newly created debt
     */
    public function store(Request $request)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'type' => 'required|in:payable,receivable',
            'total_amount' => 'required|numeric|min:0',
            'debt_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:debt_date',
            'description' => 'nullable|string',
        ]);

        $contactType = $validated['type'] === 'payable' ? 'supplier' : 'customer';

        // Find or Create Contact
        $contact = Contact::where('store_id', $store->id)
            ->where('type', $contactType)
            ->where('name', $validated['contact_name'])
            ->when(!empty($validated['contact_phone']), function($q) use ($validated) {
                return $q->where('phone', $validated['contact_phone']);
            })
            ->first();

        if (!$contact) {
            $contact = Contact::create([
                'store_id' => $store->id,
                'name' => $validated['contact_name'],
                'phone' => $validated['contact_phone'] ?? null,
                'type' => $contactType,
                'is_active' => true,
            ]);
        }

        $data = [
            'store_id' => $store->id,
            'contact_id' => $contact->id,
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'total_amount' => $validated['total_amount'],
            'debt_date' => $validated['debt_date'],
            'due_date' => $validated['due_date'],
            'description' => $validated['description'],
            'paid_amount' => 0,
            'status' => 'unpaid',
        ];

        Debt::create($data);

        $typeLabel = $validated['type'] === 'payable' ? 'Hutang' : 'Piutang';

        return redirect()->route('debts.index', ['type' => $validated['type']])
            ->with('success', "{$typeLabel} berhasil dicatat!");
    }

    /**
     * Display the specified debt
     */
    public function show(Debt $debt)
    {
        $this->authorizeDebt($debt);

        $debt->load(['contact', 'user', 'payments.user', 'payments.transaction']);

        return view('debts.show', compact('debt'));
    }

    /**
     * Show the form for editing a debt
     */
    public function edit(Debt $debt)
    {
        $this->authorizeDebt($debt);

        $store = Auth::user()->currentStore();
        
        $contactType = $debt->type === 'payable' ? 'supplier' : 'customer';
        $contacts = Contact::where('store_id', $store->id)
            ->where('type', $contactType)
            ->active()
            ->orderBy('name')
            ->get();

        return view('debts.edit', compact('debt', 'store', 'contacts'));
    }

    /**
     * Update the specified debt
     */
    public function update(Request $request, Debt $debt)
    {
        $this->authorizeDebt($debt);

        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'total_amount' => 'required|numeric|min:' . $debt->paid_amount,
            'debt_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:debt_date',
            'description' => 'nullable|string',
        ]);

        $debt->update($validated);
        $debt->updateStatus();

        return redirect()->route('debts.show', $debt)
            ->with('success', 'Data berhasil diperbarui!');
    }

    /**
     * Remove the specified debt
     */
    public function destroy(Debt $debt)
    {
        $this->authorizeDebt($debt);

        // Check if debt has payments
        if ($debt->payments()->exists()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus hutang/piutang yang sudah ada pembayaran!');
        }

        $type = $debt->type;
        $debt->delete();

        return redirect()->route('debts.index', ['type' => $type])
            ->with('success', 'Data berhasil dihapus!');
    }

    /**
     * Show form to record a payment
     */
    public function showPaymentForm(Debt $debt)
    {
        $this->authorizeDebt($debt);

        if ($debt->status === 'paid') {
            return redirect()->route('debts.show', $debt)
                ->with('info', 'Hutang/piutang ini sudah lunas.');
        }

        return view('debts.payment', compact('debt'));
    }

    /**
     * Record a payment for the debt
     */
    public function recordPayment(Request $request, Debt $debt)
    {
        $this->authorizeDebt($debt);

        if ($debt->status === 'paid') {
            return redirect()->route('debts.show', $debt)
                ->with('error', 'Hutang/piutang ini sudah lunas.');
        }

        $maxAmount = $debt->remaining_amount;

        $validated = $request->validate([
            'amount' => "required|numeric|min:1|max:{$maxAmount}",
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'create_transaction' => 'boolean',
        ]);

        DB::transaction(function () use ($debt, $validated, $request) {
            $transactionId = null;

            // Optionally create a transaction record
            if ($request->boolean('create_transaction')) {
                $store = Auth::user()->currentStore();
                
                // Determine transaction type based on debt type
                // Paying hutang = expense, receiving piutang = income
                $transactionType = $debt->type === 'payable' ? 'expense' : 'income';
                
                // Find default account or first account of the type
                $account = $store->accounts()
                    ->where('type', $transactionType)
                    ->where('is_default', true)
                    ->first();
                
                if (!$account) {
                    $account = $store->accounts()->where('type', $transactionType)->first();
                }

                if ($account) {
                    $description = $debt->type === 'payable' 
                        ? "Pembayaran hutang ke {$debt->contact->name}"
                        : "Penerimaan piutang dari {$debt->contact->name}";

                    $transaction = Transaction::create([
                        'store_id' => $store->id,
                        'account_id' => $account->id,
                        'user_id' => Auth::id(),
                        'type' => $transactionType,
                        'amount' => $validated['amount'],
                        'transaction_date' => $validated['payment_date'],
                        'description' => $description . ($validated['notes'] ? " - {$validated['notes']}" : ''),
                    ]);

                    $transactionId = $transaction->id;
                }
            }

            // Create the payment record
            DebtPayment::create([
                'debt_id' => $debt->id,
                'user_id' => Auth::id(),
                'transaction_id' => $transactionId,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'],
            ]);
        });

        $message = $debt->fresh()->status === 'paid' 
            ? 'Pembayaran berhasil dicatat. Hutang/Piutang sudah LUNAS!'
            : 'Pembayaran berhasil dicatat.';

        return redirect()->route('debts.show', $debt)
            ->with('success', $message);
    }

    /**
     * Check if user has access to debt's store
     */
    private function authorizeDebt(Debt $debt)
    {
        $store = Auth::user()->currentStore();

        if (!$store || $debt->store_id !== $store->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }
    }
}
