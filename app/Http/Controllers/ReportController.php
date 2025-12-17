<?php

namespace App\Http\Controllers;

use App\Services\FinancialReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;

class ReportController extends Controller
{
    public function __construct(
        protected FinancialReportService $reportService
    ) {}

    /**
     * Income report
     */
    public function income(Request $request)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $accountId = $request->get('account_id');

        $data = $this->reportService->getReportData($store, $startDate, $endDate, 'income', $accountId);

        return view('reports.income', $data);
    }

    /**
     * Expense report
     */
    public function expense(Request $request)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $accountId = $request->get('account_id');

        $data = $this->reportService->getReportData($store, $startDate, $endDate, 'expense', $accountId);

        return view('reports.expense', $data);
    }

    /**
     * Profit & Loss report
     */
    public function profitLoss(Request $request)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $data = $this->reportService->getProfitLoss($store, $startDate, $endDate);

        return view('reports.profit-loss', $data);
    }

    /**
     * Cash flow report
     */
    public function cashflow(Request $request)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $data = $this->reportService->getCashflow($store, $startDate, $endDate);

        return view('reports.cashflow', $data);
    }

    /**
     * Export report
     */
    public function export(Request $request, $type, $format)
    {
        $store = Auth::user()->currentStore();

        if (!$store) {
            return redirect()->route('stores.create');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $transactionType = null;
        if ($type === 'income' || $type === 'expense') {
            $transactionType = $type;
        }

        $transactions = $this->reportService->getExportData($store, $startDate, $endDate, $transactionType);

        $filename = "laporan_{$type}_{$startDate}_{$endDate}";

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.export-pdf', [
                'transactions' => $transactions,
                'store' => $store,
                'type' => $type,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);

            return $pdf->download($filename . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(
                new TransactionsExport($transactions, $store, $type, $startDate, $endDate),
                $filename . '.xlsx'
            );
        }

        abort(400, 'Format tidak didukung');
    }
}
