<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $period    = $request->period ?? 'daily';
        $startDate = $request->start_date ?? now()->toDateString();
        $endDate   = $request->end_date ?? now()->toDateString();

        if ($period === 'daily') {
            $startDate = $endDate = $request->date ?? now()->toDateString();
        } elseif ($period === 'weekly') {
            $startDate = now()->startOfWeek()->toDateString();
            $endDate   = now()->endOfWeek()->toDateString();
        } elseif ($period === 'monthly') {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate   = now()->endOfMonth()->toDateString();
        }

        $transactions = Transaction::with(['items', 'user'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $expenses = \App\Models\Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        $totalRevenue   = $transactions->sum('total');
        $totalExpenses  = $expenses->sum('amount');
        $netProfit      = $totalRevenue - $totalExpenses;

        // Cost of goods sold from transaction items (unit_price * qty — but we need cost_price)
        // Use variant cost_price joined through transaction items
        $cogs = \App\Models\TransactionItem::whereIn('transaction_id', $transactions->pluck('id'))
            ->join('product_variants', 'transaction_items.product_variant_id', '=', 'product_variants.id')
            ->selectRaw('SUM(transaction_items.quantity * product_variants.cost_price) as total_cogs')
            ->value('total_cogs') ?? 0;

        $grossProfit = $totalRevenue - $cogs;

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_revenue'      => $totalRevenue,
            'total_discount'     => $transactions->sum('discount'),
            'total_items'        => $transactions->sum(fn($t) => $t->items->sum('quantity')),
            'total_expenses'     => $totalExpenses,
            'cogs'               => $cogs,
            'gross_profit'       => $grossProfit,
            'net_profit'         => $netProfit,
        ];

        return view('reports.sales', compact(
            'transactions', 'expenses', 'summary', 'period', 'startDate', 'endDate'
        ));
    }

    public function inventory(Request $request)
    {
        $query = ProductVariant::with('product.category');
        if ($request->low_stock) {
            $query->where('stock_quantity', '<=', 5);
        }
        $variants = $query->orderBy('stock_quantity')->get();
        $lowStockCount = ProductVariant::where('stock_quantity', '<=', 5)->count();
        return view('reports.inventory', compact('variants', 'lowStockCount'));
    }

    public function exportSalesCsv(Request $request)
    {
        $startDate = $request->start_date ?? now()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $transactions = Transaction::with(['items', 'user'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="sales_report.csv"'];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Transaction #', 'Date', 'Cashier', 'Items', 'Subtotal', 'Discount', 'Tax', 'Total', 'Paid', 'Change']);
            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->transaction_number,
                    $t->created_at->format('Y-m-d H:i'),
                    $t->user->name,
                    $t->items->sum('quantity'),
                    $t->subtotal,
                    $t->discount,
                    $t->tax,
                    $t->total,
                    $t->amount_paid,
                    $t->change_amount,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportSalesPdf(Request $request)
    {
        $startDate = $request->start_date ?? now()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        $transactions = Transaction::with(['items', 'user'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_revenue' => $transactions->sum('total'),
        ];

        $pdf = Pdf::loadView('reports.sales_pdf', compact('transactions', 'summary', 'startDate', 'endDate'));
        return $pdf->download('sales_report.pdf');
    }

    public function exportInventoryCsv()
    {
        $variants = ProductVariant::with('product.category')->orderBy('stock_quantity')->get();
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="inventory_report.csv"'];

        $callback = function () use ($variants) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Product', 'Category', 'SKU', 'Size', 'Color', 'Price', 'Stock', 'Status']);
            foreach ($variants as $v) {
                fputcsv($file, [
                    $v->product->name,
                    $v->product->category->name,
                    $v->sku,
                    $v->size,
                    $v->color,
                    $v->price,
                    $v->stock_quantity,
                    $v->stock_quantity <= 5 ? 'Low Stock' : 'OK',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
