<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::where('is_active', true)->count();
        $totalStock    = ProductVariant::sum('stock_quantity');

        $today = now()->toDateString();

        $salesToday   = Transaction::whereDate('created_at', $today)->where('status', 'completed')->count();
        $revenueToday = Transaction::whereDate('created_at', $today)->where('status', 'completed')->sum('total');
        $expensesToday = \App\Models\Expense::whereDate('expense_date', $today)->sum('amount');
        $netProfitToday = $revenueToday - $expensesToday;

        $lowStock = ProductVariant::with('product')
            ->where('stock_quantity', '<=', 5)
            ->where('is_active', true)
            ->orderBy('stock_quantity')
            ->take(5)->get();

        $recentTransactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)->get();

        $bestSelling = TransactionItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)->get();

        return view('dashboard', compact(
            'totalProducts', 'totalStock', 'salesToday', 'revenueToday',
            'expensesToday', 'netProfitToday',
            'lowStock', 'recentTransactions', 'bestSelling'
        ));
    }
}
