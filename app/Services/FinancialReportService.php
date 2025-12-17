<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Get report data for income or expense.
     */
    public function getReportData(Store $store, string $startDate, string $endDate, string $type, ?int $accountId = null): array
    {
        $query = Transaction::where('store_id', $store->id)
            ->where('type', $type)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['account', 'user']);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        // Group by category using database aggregation
        $byCategory = Transaction::where('store_id', $store->id)
            ->where('type', $type)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->when($accountId, fn($q) => $q->where('account_id', $accountId))
            ->selectRaw('account_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('account_id')
            ->with('account')
            ->get()
            ->map(function ($item) {
                return [
                    'account' => $item->account,
                    'total' => $item->total,
                    'count' => $item->count,
                ];
            })
            ->values();

        $accounts = Account::where('store_id', $store->id)
            ->where('type', $type)
            ->get();

        $total = $transactions->sum('amount');

        return compact('store', 'transactions', 'byCategory', 'accounts', 'total', 'startDate', 'endDate', 'type');
    }

    /**
     * Get income by category using database aggregation.
     */
    public function getIncomeByCategory(Store $store, string $startDate, string $endDate)
    {
        return Transaction::where('store_id', $store->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();
    }

    /**
     * Get expense by category using database aggregation.
     */
    public function getExpenseByCategory(Store $store, string $startDate, string $endDate)
    {
        return Transaction::where('store_id', $store->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->with('account')
            ->get();
    }

    /**
     * Get profit/loss data.
     */
    public function getProfitLoss(Store $store, string $startDate, string $endDate): array
    {
        $incomeByCategory = $this->getIncomeByCategory($store, $startDate, $endDate);
        $expenseByCategory = $this->getExpenseByCategory($store, $startDate, $endDate);

        $totalIncome = $incomeByCategory->sum('total');
        $totalExpense = $expenseByCategory->sum('total');
        $netProfit = $totalIncome - $totalExpense;

        return compact(
            'store',
            'startDate',
            'endDate',
            'incomeByCategory',
            'expenseByCategory',
            'totalIncome',
            'totalExpense',
            'netProfit'
        );
    }

    /**
     * Get cashflow data using optimized database aggregation.
     * Uses SQL SUM(CASE WHEN...) for better performance with large datasets.
     */
    public function getCashflow(Store $store, string $startDate, string $endDate): array
    {
        // Get previous balance using single aggregated query
        $previousBalance = Transaction::where('store_id', $store->id)
            ->where('transaction_date', '<', $startDate)
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
            ")
            ->first();

        $previousIncome = $previousBalance->total_income ?? 0;
        $previousExpense = $previousBalance->total_expense ?? 0;
        $openingBalance = $previousIncome - $previousExpense;
        $runningBalance = $openingBalance;

        // Get daily cashflow using database aggregation with SUM(CASE WHEN...)
        $dailyCashflow = Transaction::where('store_id', $store->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw("
                transaction_date,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            ")
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get();

        // Calculate running balance
        $cashflowData = [];
        foreach ($dailyCashflow as $day) {
            $dayIncome = (float) $day->income;
            $dayExpense = (float) $day->expense;
            $runningBalance += ($dayIncome - $dayExpense);

            $cashflowData[] = [
                'date' => $day->transaction_date->format('Y-m-d'),
                'income' => $dayIncome,
                'expense' => $dayExpense,
                'balance' => $runningBalance,
            ];
        }

        $closingBalance = $runningBalance;

        return compact(
            'store',
            'startDate',
            'endDate',
            'cashflowData',
            'openingBalance',
            'closingBalance'
        );
    }

    /**
     * Get export data for transactions.
     */
    public function getExportData(Store $store, string $startDate, string $endDate, ?string $type = null)
    {
        return Transaction::where('store_id', $store->id)
            ->when($type && in_array($type, ['income', 'expense']), fn($q) => $q->where('type', $type))
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['account', 'user'])
            ->orderBy('transaction_date')
            ->get();
    }
}
