@extends('layouts.app')
@section('title', 'Import Products')
@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('products.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h2 class="text-2xl font-bold text-gray-800">Import Products via CSV</h2>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <!-- Instructions -->
    <div class="bg-white rounded-xl shadow p-6 space-y-4">
        <h3 class="font-semibold text-gray-700 border-b pb-2">Instructions</h3>
        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600">
            <li>Download the CSV template below</li>
            <li><strong class="text-gray-800">One row = one variant.</strong> For a product with multiple sizes/colors, repeat the same SKU on multiple rows — one per variant</li>
            <li>Example: Dress with S/Red, M/Red, L/Blue = 3 rows, all with the same SKU</li>
            <li><strong class="text-gray-800">Category is optional.</strong> Leave it blank to assign to <em>Uncategorized</em>. If a category name is provided that doesn't exist yet, it will be created automatically</li>
            <li>Size can be any value: <code class="bg-gray-100 px-1 rounded">XS, S, M, L, XL, 28, 30, 2T, Free Size</code>, etc.</li>
            <li><strong class="text-gray-800">Gender is optional.</strong> Use: <code class="bg-gray-100 px-1 rounded">men, women, kids, unisex</code> — leave blank if not applicable</li>
            <li>If a SKU already exists, the product info is updated and variants are upserted (not duplicated)</li>
            <li>Upload the file and review the preview before confirming</li>
        </ol>

        <a href="{{ route('products.import.template') }}"
            class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-download"></i> Download CSV Template
        </a>

        <div class="text-sm text-gray-500">
            <p class="font-medium text-gray-700 mb-1">Available Categories:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $cat)
                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs">{{ $cat->name }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4">Upload CSV File</h3>
        <form method="POST" action="{{ route('products.import.preview') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select CSV File</label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-gray-400 transition cursor-pointer"
                    onclick="document.getElementById('csv_file').click()">
                    <i class="fas fa-file-csv text-4xl text-gray-300 mb-3 block"></i>
                    <p class="text-sm text-gray-500">Click to select or drag & drop your CSV file</p>
                    <p class="text-xs text-gray-400 mt-1">Max 2MB · .csv files only</p>
                    <p id="file-name" class="text-sm font-medium text-gray-700 mt-2 hidden"></p>
                </div>
                <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" class="hidden"
                    onchange="document.getElementById('file-name').textContent = this.files[0]?.name; document.getElementById('file-name').classList.remove('hidden');">
                @error('csv_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full bg-brand hover:bg-brand-dark text-white py-2.5 rounded-lg font-medium transition">
                <i class="fas fa-eye mr-2"></i> Preview Import
            </button>
        </form>
    </div>
</div>
@endsection
