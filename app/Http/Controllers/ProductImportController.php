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
                'category',       // optional — defaults to Uncategorized if blank; auto-created if new name
                'sku',            // required — unique
                'barcode',        // optional
                'description',    // optional
                'size',           // optional — any value e.g. XS, S, M, L, XL, XXL, 28, 30, Free Size
                'color',          // optional
                'price',          // required
                'cost_price',     // optional
                'stock_quantity', // required
                'gender',         // optional — men, women, kids, unisex
            ]);
            // Example rows — same SKU = same product, different rows = different variants
            fputcsv($file, ['Floral Dress', 'Dress', 'DR-001', '', 'Summer dress', 'S', 'Red', '29.99', '15.00', '10', 'women']);
            fputcsv($file, ['Floral Dress', 'Dress', 'DR-001', '', 'Summer dress', 'M', 'Red', '29.99', '15.00', '8', 'women']);
            fputcsv($file, ['Floral Dress', 'Dress', 'DR-001', '', 'Summer dress', 'L', 'Blue', '34.99', '15.00', '5', 'women']);
            fputcsv($file, ['Classic T-Shirt', 'T-shirt', 'TS-001', '1234567890', '', 'S', 'Black', '14.99', '7.00', '20', 'men']);
            fputcsv($file, ['Classic T-Shirt', 'T-shirt', 'TS-001', '1234567890', '', 'M', '', '14.99', '7.00', '25', 'men']);
            fputcsv($file, ['Classic T-Shirt', 'T-shirt', 'TS-001', '1234567890', '', 'L', '', '14.99', '7.00', '15', 'men']);
            fputcsv($file, ['Kids Polo', 'Polo', 'PL-001', '', '', '2T', 'Yellow', '12.99', '6.00', '10', 'kids']);
            fputcsv($file, ['Slim Pants', 'Pants', 'PT-001', '', '', '28', 'Navy', '49.99', '25.00', '12', 'unisex']);
            fputcsv($file, ['No Category Product', '', 'NC-001', '', '', 'Free Size', 'White', '9.99', '5.00', '30', '']);
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

        $categories   = Category::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $existingSkus = Product::pluck('sku')->map(fn($s) => strtolower($s))->toArray();

        // Ensure Uncategorized exists as fallback
        $uncategorized = Category::firstOrCreate(
            ['slug' => 'uncategorized'],
            ['name' => 'Uncategorized', 'is_active' => true]
        );
        $uncategorizedId = $uncategorized->id;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row)) === 0) continue; // skip empty rows

            $validGenders = array_keys(\App\Models\Product::GENDERS);
            $rawGender    = strtolower(trim($row[10] ?? ''));

            $data = [
                'product_name'   => trim($row[0] ?? ''),
                'category'       => trim($row[1] ?? ''),
                'sku'            => trim($row[2] ?? ''),
                'barcode'        => trim($row[3] ?? '') ?: null,
                'description'    => trim($row[4] ?? '') ?: null,
                'size'           => trim($row[5] ?? ''),
                'color'          => trim($row[6] ?? ''),
                'price'          => trim($row[7] ?? ''),
                'cost_price'     => trim($row[8] ?? '') ?: 0,
                'stock_quantity' => trim($row[9] ?? ''),
                'gender'         => in_array($rawGender, $validGenders) ? $rawGender : null,
            ];

            $rowErrors = [];

            if (empty($data['product_name'])) $rowErrors[] = 'Product name required';
            if (empty($data['sku']))          $rowErrors[] = 'SKU required';
            if (!is_numeric($data['price']) || $data['price'] < 0) $rowErrors[] = 'Invalid price';
            if (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) $rowErrors[] = 'Invalid stock';

            // Resolve category — auto-create if named, fall back to Uncategorized if blank
            $catKey = strtolower($data['category']);
            if (!empty($data['category']) && !isset($categories[$catKey])) {
                $newCat = Category::firstOrCreate(
                    ['name' => $data['category']],
                    ['is_active' => true]
                );
                $categories[$catKey] = $newCat->id;
            }
            $catId = !empty($catKey) ? ($categories[$catKey] ?? $uncategorizedId) : $uncategorizedId;

            $data['category_id']     = $catId;
            $data['is_new_category'] = isset($newCat) && $newCat->wasRecentlyCreated ? $data['category'] : null;
            $data['is_uncategorized'] = empty($data['category']);
            unset($newCat);
            $data['row']        = $rowNum;
            $data['errors']     = $rowErrors;
            $data['is_new_sku'] = !in_array(strtolower($data['sku']), $existingSkus);

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

        $imported   = 0;
        $skipped    = 0;
        $categories = Category::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);

        // Ensure Uncategorized exists as fallback
        $uncategorized = Category::firstOrCreate(
            ['slug' => 'uncategorized'],
            ['name' => 'Uncategorized', 'is_active' => true]
        );
        $uncategorizedId = $uncategorized->id;

        foreach ($rows as $row) {
            if (!empty($row['errors'])) { $skipped++; continue; }

            // Resolve category — auto-create if named, fall back to Uncategorized if blank
            $catKey = strtolower($row['category']);
            if (!empty($row['category']) && !isset($categories[$catKey])) {
                $newCat = Category::firstOrCreate(
                    ['name' => $row['category']],
                    ['is_active' => true]
                );
                $categories[$catKey] = $newCat->id;
            }
            $catId = !empty($catKey) ? ($categories[$catKey] ?? $uncategorizedId) : $uncategorizedId;

            // Find or create product by SKU
            $product = Product::firstOrCreate(
                ['sku' => $row['sku']],
                [
                    'category_id' => $catId,
                    'name'        => $row['product_name'],
                    'sku'         => $row['sku'],
                    'barcode'     => $row['barcode'] ?: null,
                    'description' => $row['description'] ?: null,
                    'gender'      => $row['gender'] ?? null,
                ]
            );

            // Update category/name/gender if product already existed
            if (!$product->wasRecentlyCreated) {
                $updateData = ['category_id' => $catId, 'name' => $row['product_name']];
                if (!empty($row['gender'])) $updateData['gender'] = $row['gender'];
                $product->update($updateData);
            }

            // Find or create variant
            $sizePart   = !empty($row['size'])  ? '-' . $row['size'] : '';
            $colorPart  = !empty($row['color']) ? '-' . strtoupper(substr($row['color'], 0, 3)) : '';
            $variantSku = $row['sku'] . $sizePart . $colorPart;

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
