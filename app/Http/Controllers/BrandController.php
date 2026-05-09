<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->orderBy('name')->paginate(20);
        return view('brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active');

        Brand::create($data);

        return response()->json(['success' => true, 'message' => 'Brand created successfully.']);
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->has('is_active');

        $brand->update($data);

        return response()->json(['success' => true, 'message' => 'Brand updated successfully.']);
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return response()->json([
                'success' => false, 
                'message' => 'Cannot delete brand with associated products.'
            ], 422);
        }

        // Delete logo file
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return response()->json(['success' => true, 'message' => 'Brand deleted successfully.']);
    }
}