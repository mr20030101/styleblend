<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
        ]);
        $data['slug'] = Str::slug($data['name']);
        Category::create($data);
        return response()->json(['success' => true, 'message' => 'Category created.']);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $data['slug'] = Str::slug($data['name']);
        $category->update($data);
        return response()->json(['success' => true, 'message' => 'Category updated.']);
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete category with products.'], 422);
        }
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }
}
