<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchaseOrders')->orderBy('name')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string|max:255',
        ]);
        $supplier = Supplier::create($data);
        return response()->json(['success' => true, 'supplier' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string|max:255',
            'is_active'      => 'boolean',
        ]);
        $supplier->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return response()->json(['success' => true]);
    }
}
