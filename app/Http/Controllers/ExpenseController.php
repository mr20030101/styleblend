<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('user')->orderBy('expense_date', 'desc');

        if ($request->date) {
            $query->whereDate('expense_date', $request->date);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->month) {
            $query->whereYear('expense_date', substr($request->month, 0, 4))
                  ->whereMonth('expense_date', substr($request->month, 5, 2));
        }

        $expenses   = $query->paginate(20)->withQueryString();
        $totalToday = Expense::whereDate('expense_date', today())->sum('amount');
        $totalMonth = Expense::whereYear('expense_date', now()->year)
                             ->whereMonth('expense_date', now()->month)->sum('amount');
        $categories = Expense::categories();

        return view('expenses.index', compact('expenses', 'totalToday', 'totalMonth', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:150',
            'category'     => 'required|string',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:255',
        ]);
        $data['user_id'] = Auth::id();
        $expense = Expense::create($data);
        return response()->json(['success' => true, 'expense' => $expense]);
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:150',
            'category'     => 'required|string',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:255',
        ]);
        $expense->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(['success' => true]);
    }
}
