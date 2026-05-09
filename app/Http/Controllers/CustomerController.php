<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('transactions')
            ->withSum(['transactions' => fn($q) => $q->where('status', 'completed')], 'total');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->load(['transactions.items', 'raffleEntries']);
        return view('customers.show', compact('customer'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|string|max:20|unique:customers,phone',
            'email'    => 'nullable|email|max:100|unique:customers,email',
            'address'  => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
        ]);
        $customer = Customer::create($data);
        return response()->json(['success' => true, 'customer' => $customer]);
    }
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'phone'     => 'nullable|string|max:20|unique:customers,phone,' . $customer->id,
            'email'     => 'nullable|email|max:100|unique:customers,email,' . $customer->id,
            'address'   => 'nullable|string|max:255',
            'birthday'  => 'nullable|date',
            'is_active' => 'boolean',
        ]);
        $customer->update($data);
        return response()->json(['success' => true, 'message' => 'Customer updated.']);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['success' => true]);
    }

    /** AJAX search for POS customer lookup */
    public function search(Request $request)
    {
        $q = $request->q;
        $customers = Customer::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                      ->orWhere('phone', 'like', '%' . $q . '%');
            })
            ->select('id', 'name', 'phone', 'email')
            ->limit(10)->get();
        return response()->json($customers);
    }
}
