@extends('layouts.app')
@section('title', 'Import Preview')
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('products.import') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h2 class="text-2xl font-bold text-gray-800">Import Preview</h2>
    </div>

    @php
        $validRows   = collect($rows)->filter(fn($r) => empty($r['errors']))->count();
        $invalidRows = collect($rows)->filter(fn($r) => !empty($r['errors']))->count();
    @endphp

    <!-- Summary -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
            <div class="bg-gray-100 rounded-full w-10 h-10 flex items-center justify-center text-gray-600 flex-shrink-0">
                <i class="fas fa-list"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Total Rows</p>
                <p class="text-2xl font-bold text-gray-800">{{ count($rows) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
            <div class="bg-gray-100 rounded-full w-10 h-10 flex items-center justify-center text-gray-700 flex-shrink-0">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Valid</p>
                <p class="text-2xl font-bold text-gray-800">{{ $validRows }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
            <div class="bg-gray-100 rounded-full w-10 h-10 flex items-center justify-center text-red-500 flex-shrink-0">
                <i class="fas fa-times"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Errors</p>
                <p class="text-2xl font-bold {{ $invalidRows > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $invalidRows }}</p>
            </div>
        </div>
    </div>

    @if(!empty($errors))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="font-semibold text-red-700 mb-2 text-sm"><i class="fas fa-exclamation-triangle mr-1"></i> {{ $invalidRows }} row(s) have errors and will be skipped:</p>
        <ul class="list-disc list-inside space-y-1 text-xs text-red-600">
            @foreach($errors as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-100 font-semibold text-gray-700">
            Data Preview <span class="text-xs font-normal text-gray-400 ml-2">Rows with errors will be skipped</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-3 py-2 font-semibold text-gray-600">Row</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-600">Product</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-600">Type</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-600">Category</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-600">SKU</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-600">Gender</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-600">Size</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-600">Color</th>
                        <th class="text-right px-3 py-2 font-semibold text-gray-600">Price</th>
                        <th class="text-right px-3 py-2 font-semibold text-gray-600">Cost</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-600">Stock</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($rows as $row)
                    <tr class="{{ !empty($row['errors']) ? 'bg-red-50' : 'hover:bg-gray-50' }}">
                        <td class="px-3 py-2 text-gray-400">{{ $row['row'] }}</td>
                        <td class="px-3 py-2 font-medium text-gray-800">{{ $row['product_name'] }}</td>
                        <td class="px-3 py-2">
                            @if(($row['product_type'] ?? 'variable') === 'simple')
                                <span class="px-1.5 py-0.5 rounded text-xs bg-blue-100 text-blue-700">Simple</span>
                            @else
                                <span class="px-1.5 py-0.5 rounded text-xs bg-purple-100 text-purple-700">Variable</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-600">
                            @if(!empty($row['is_uncategorized']))
                                <span class="text-gray-400 italic">Uncategorized</span>
                            @else
                                {{ $row['category'] }}
                            @endif
                        </td>
                        <td class="px-3 py-2 font-mono text-gray-600">
                            {{ $row['sku'] }}
                            @if($row['is_new_sku'])
                                <span class="ml-1 bg-gray-100 text-gray-600 px-1 rounded text-xs">new</span>
                            @else
                                <span class="ml-1 bg-yellow-100 text-yellow-700 px-1 rounded text-xs">update</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-500">
                            @if(!empty($row['gender']))
                                {{ \App\Models\Product::GENDERS[$row['gender']] ?? $row['gender'] }}
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $row['size'] }}</td>
                        <td class="px-3 py-2">{{ $row['color'] }}</td>
                        <td class="px-3 py-2 text-right">₱{{ number_format($row['price'], 2) }}</td>
                        <td class="px-3 py-2 text-right text-gray-500">₱{{ number_format($row['cost_price'], 2) }}</td>
                        <td class="px-3 py-2 text-center">{{ $row['stock_quantity'] }}</td>
                        <td class="px-3 py-2 text-center">
                            @if(!empty($row['errors']))
                                <span class="text-red-600 font-medium" title="{{ implode(', ', $row['errors']) }}">
                                    <i class="fas fa-times-circle"></i> Error
                                </span>
                            @else
                                <span class="text-gray-700"><i class="fas fa-check-circle"></i> OK</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($validRows > 0)
    <form method="POST" action="{{ route('products.import.confirm') }}" class="flex gap-3">
        @csrf
        <button type="submit" class="bg-brand hover:bg-brand-dark text-white px-6 py-2.5 rounded-lg font-medium transition">
            <i class="fas fa-file-import mr-2"></i> Confirm Import ({{ $validRows }} rows)
        </button>
        <a href="{{ route('products.import') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-medium transition">
            Cancel
        </a>
    </form>
    @else
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
        No valid rows to import. Please fix the errors and upload again.
        <a href="{{ route('products.import') }}" class="underline ml-2">Upload again</a>
    </div>
    @endif
</div>
@endsection
