<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    // Dashboard (admin only)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('role:admin');

    // POS
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [PosController::class, 'searchProducts'])->name('pos.search');
    Route::get('/pos/scan', [PosController::class, 'scanBarcode'])->name('pos.scan');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{id}', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('/customers/search', [\App\Http\Controllers\CustomerController::class, 'search'])->name('customers.search');
    Route::post('/customers/quick', [\App\Http\Controllers\CustomerController::class, 'store'])->name('customers.quick');

    // Admin routes
    Route::middleware('role:admin')->group(function () {

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Brands
        Route::get('/brands', [\App\Http\Controllers\BrandController::class, 'index'])->name('brands.index');
        Route::post('/brands', [\App\Http\Controllers\BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}', [\App\Http\Controllers\BrandController::class, 'show'])->name('brands.show');
        Route::put('/brands/{brand}', [\App\Http\Controllers\BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [\App\Http\Controllers\BrandController::class, 'destroy'])->name('brands.destroy');

        // Products
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/import', [\App\Http\Controllers\ProductImportController::class, 'showForm'])->name('products.import');
        Route::get('/products/import/template', [\App\Http\Controllers\ProductImportController::class, 'downloadTemplate'])->name('products.import.template');
        Route::post('/products/import/preview', [\App\Http\Controllers\ProductImportController::class, 'preview'])->name('products.import.preview');
        Route::post('/products/import/confirm', [\App\Http\Controllers\ProductImportController::class, 'import'])->name('products.import.confirm');
        Route::get('/products/{product}/barcodes', [\App\Http\Controllers\ProductController::class, 'barcodes'])->name('products.barcodes');
        Route::get('/products/barcodes/batch', [\App\Http\Controllers\ProductController::class, 'barcodesBatch'])->name('products.barcodes.batch');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
        Route::put('/variants/{variant}', [ProductController::class, 'updateVariant'])->name('variants.update');
        Route::delete('/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('variants.destroy');

        // Inventory
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/{variant}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('/inventory/{variant}/history', [InventoryController::class, 'history'])->name('inventory.history');

        // Reports
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/sales/csv', [ReportController::class, 'exportSalesCsv'])->name('reports.sales.csv');
        Route::get('/reports/sales/pdf', [ReportController::class, 'exportSalesPdf'])->name('reports.sales.pdf');
        Route::get('/reports/inventory/csv', [ReportController::class, 'exportInventoryCsv'])->name('reports.inventory.csv');

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Transactions (void)
        Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [\App\Http\Controllers\TransactionController::class, 'show'])->name('transactions.show');
        Route::post('/transactions/{transaction}/void', [\App\Http\Controllers\TransactionController::class, 'void'])->name('transactions.void');

        // Suppliers
        Route::get('/suppliers', [\App\Http\Controllers\SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [\App\Http\Controllers\SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'destroy'])->name('suppliers.destroy');

        // Purchase Orders
        Route::get('/purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/create', [\App\Http\Controllers\PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchaseOrder}', [\App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::post('/purchase-orders/{purchaseOrder}/receive', [\App\Http\Controllers\PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [\App\Http\Controllers\PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

        // Expenses
        Route::get('/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
        Route::put('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');
        // Settings
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

        // Customers
        Route::get('/customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');
        Route::post('/customers', [\App\Http\Controllers\CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'destroy'])->name('customers.destroy');

        // Raffle
        Route::get('/raffle', [\App\Http\Controllers\RaffleController::class, 'index'])->name('raffle.index');
        Route::post('/raffle/period/start', [\App\Http\Controllers\RaffleController::class, 'startPeriod'])->name('raffle.period.start');
        Route::post('/raffle/period/{period}/end', [\App\Http\Controllers\RaffleController::class, 'endPeriod'])->name('raffle.period.end');
        Route::post('/raffle/draw', [\App\Http\Controllers\RaffleController::class, 'draw'])->name('raffle.draw');
        Route::post('/raffle/{entry}/winner', [\App\Http\Controllers\RaffleController::class, 'markWinner'])->name('raffle.winner');
    });
});
