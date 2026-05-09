<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductImportController extends Controller
{
    public function showForm()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('products.import', compact('categories'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // Header row
            fputcsv($file, [
                'product_name',   // required
                'category',       // required — must match existing category name
                'sku',            // required — unique
                'barcode',        // optional
                'description',    // optional
                'size',           // required — XS, S, M, L, XL
                'color',          // required
                'price',          // required
                'cost_price',     // optional
                'stock_quantity', // required
            ]);
            // Example rows — same SKU = same product, different rows = different variants
            fputcsv($file, ['Floral Dress', 'Women', 'WD-002', '', 'Summer dress', 'S', 'Red', '29.99', '15.00', '10']);
            fputcsv($file, ['Floral Dress', 'Women', 'WD-002', '', 'Summer dress', 'M', 'Red', '29.99', '15.00', '8']);
            fputcsv($file, ['Floral Dress', 'Women', 'WD-002', '', 'Summer dress', 'L', 'Blue', '34.99', '15.00', '5']);
            fputcsv($file, ['Classic T-Shirt', 'Men', 'MT-002', '1234567890', '', 'S', 'Black', '14.99', '7.00', '20']);
            fputcsv($file, ['Classic T-Shirt', 'Men', 'MT-002', '1234567890', '', 'M', 'Black', '14.99', '7.00', '25']);
            fputcsv($file, ['Classic T-Shirt', 'Men', 'MT-002', '1234567890', '', 'L', 'Navy', '14.99', '7.00', '15']);
            fputcsv($file, ['Kids Polo', 'Kids', 'KP-002', '', '', 'XS', 'Yellow', '12.99', '6.00', '10']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function preview(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:2048']);

        $file    = $request->file('csv_file');
        $handle  = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle); // skip header row

        $rows    = [];
        $errors  = [];
        $rowNum  = 1;

        $categories = Category::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $existingSkus = Product::pluck('sku')->map(fn($s) => strtolower($s))->toArray();

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row)) === 0) continue; // skip empty rows

            $data = [
                'product_name'   => trim($row[0] ?? ''),
                'category'       => trim($row[1] ?? ''),
                'sku'            => trim($row[2] ?? ''),
                'barcode'        => trim($row[3] ?? '') ?: null,
                'description'    => trim($row[4] ?? '') ?: null,
                'size'           => strtoupper(trim($row[5] ?? '')),
                'color'          => trim($row[6] ?? ''),
                'price'          => trim($row[7] ?? ''),
                'cost_price'     => trim($row[8] ?? '') ?: 0,
                'stock_quantity' => trim($row[9] ?? ''),
            ];

            $rowErrors = [];

            if (empty($data['product_name'])) $rowErrors[] = 'Product name required';
            if (empty($data['sku']))          $rowErrors[] = 'SKU required';
            if (empty($data['size']))         $rowErrors[] = 'Size required';
            if (empty($data['color']))        $rowErrors[] = 'Color required';
            if (!is_numeric($data['price']) || $data['price'] < 0) $rowErrors[] = 'Invalid price';
            if (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) $rowErrors[] = 'Invalid stock';

            $catId = $categories[strtolower($data['category'])] ?? null;
            if (!$catId) $rowErrors[] = "Category '{$data['category']}' not found";

            if (!in_array($data['size'], ['XS', 'S', 'M', 'L', 'XL'])) {
                $rowErrors[] = "Invalid size '{$data['size']}' (use XS/S/M/L/XL)";
            }

            $data['category_id'] = $catId;
            $data['row']         = $rowNum;
            $data['errors']      = $rowErrors;
            $data['is_new_sku']  = !in_array(strtolower($data['sku']), $existingSkus);

            $rows[] = $data;
            if (!empty($rowErrors)) $errors[] = "Row {$rowNum}: " . implode(', ', $rowErrors);
        }

        fclose($handle);

        // Store parsed rows in session for import
        session(['import_rows' => $rows]);

        return view('products.import_preview', compact('rows', 'errors'));
    }

    public function import(Request $request)
    {
        $rows = session('import_rows', []);

        if (empty($rows)) {
            return redirect()->route('products.import')->with('error', 'No data to import. Please upload again.');
        }

        $imported = 0;
        $skipped  = 0;
        $categories = Category::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);

        foreach ($rows as $row) {
            if (!empty($row['errors'])) { $skipped++; continue; }

            $catId = $categories[strtolower($row['category'])] ?? null;
            if (!$catId) { $skipped++; continue; }

            // Find or create product by SKU
            $product = Product::firstOrCreate(
                ['sku' => $row['sku']],
                [
                    'category_id' => $catId,
                    'name'        => $row['product_name'],
                    'sku'         => $row['sku'],
                    'barcode'     => $row['barcode'] ?: null,
                    'description' => $row['description'] ?: null,
                ]
            );

            // Update category/name if product already existed
            if (!$product->wasRecentlyCreated) {
                $product->update(['category_id' => $catId, 'name' => $row['product_name']]);
            }

            // Find or create variant
            $variantSku = $row['sku'] . '-' . $row['size'] . '-' . strtoupper(substr($row['color'], 0, 3));

            ProductVariant::updateOrCreate(
                ['product_id' => $product->id, 'size' => $row['size'], 'color' => $row['color']],
                [
                    'price'          => $row['price'],
                    'cost_price'     => $row['cost_price'] ?: 0,
                    'stock_quantity' => $row['stock_quantity'],
                    'sku'            => $variantSku,
                ]
            );

            $imported++;
        }

        session()->forget('import_rows');

        return redirect()->route('products.index')
            ->with('success', "Import complete: {$imported} variants imported, {$skipped} rows skipped.");
    }
}
