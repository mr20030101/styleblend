<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['children.products'])
            ->withCount('products')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $parentOptions = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories', 'parentOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|exists:categories,id',
        ]);
        $data['slug'] = Str::slug($data['name']);
        Category::create($data);
        return response()->json(['success' => true, 'message' => 'Category created.']);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'parent_id'   => 'nullable|exists:categories,id',
        ]);

        // Prevent a category from being its own parent
        if (!empty($data['parent_id']) && $data['parent_id'] == $category->id) {
            return response()->json(['success' => false, 'message' => 'A category cannot be its own parent.'], 422);
        }

        $data['slug'] = Str::slug($data['name']);
        $category->update($data);
        return response()->json(['success' => true, 'message' => 'Category updated.']);
    }

    public function destroy(Category $category)
    {
        if ($category->children()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete category with subcategories.'], 422);
        }
        if ($category->products()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete category with products.'], 422);
        }
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }
}
