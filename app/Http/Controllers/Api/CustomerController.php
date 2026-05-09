<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->q ?? '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        return response()->json(
            Customer::where('name', 'like', '%' . $q . '%')
                ->orWhere('phone', 'like', '%' . $q . '%')
                ->orWhere('email', 'like', '%' . $q . '%')
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'phone', 'email'])
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:customers,email',
        ]);

        $customer = Customer::create($data);

        return response()->json(['customer' => $customer], 201);
    }
}
