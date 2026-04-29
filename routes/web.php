<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ConfectionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/pos', [SaleController::class, 'index'])->name('sales.index');
Route::post('/pos', [SaleController::class, 'store'])->name('sales.store');
Route::get('/stock', [StockMovementController::class, 'index'])->name('stock.index');
// ── Point de Vente ──────────────────────────────────────────────────────
Route::prefix('ventes')->name('sales.')->group(function () {
    Route::get('/',           [SaleController::class, 'index'])->name('index');
    Route::post('/store',     [SaleController::class, 'store'])->name('store');
    Route::get('/historique', [SaleController::class, 'history'])->name('history');
    Route::get('/{sale}',     [SaleController::class, 'show'])->name('show');
});

// ── Gestion du Stock ────────────────────────────────────────────────────
Route::prefix('stock')->name('stock.')->group(function () {
    Route::get('/',             [StockMovementController::class, 'index'])->name('index');
    Route::get('/mouvements',   [StockMovementController::class, 'movements'])->name('movements');
    Route::post('/mouvement',   [StockMovementController::class, 'storeMovement'])->name('movement.store');
});

// ── Facturation ─────────────────────────────────────────────────────────
Route::prefix('factures')->name('invoices.')->group(function () {
    Route::get('/',         [InvoiceController::class, 'index'])->name('index');
    Route::get('/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('pdf');
});

// ── Produits ─────────────────────────────────────────────────────────────
Route::prefix('produits')->name('products.')->group(function () {
    Route::get('/',            [ProductController::class, 'index'])->name('index');
    Route::post('/',           [ProductController::class, 'store'])->name('store');
    Route::put('/{product}',   [ProductController::class, 'update'])->name('update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
});

// ── Services ─────────────────────────────────────────────────────────────
Route::prefix('services')->name('services.')->group(function () {
    Route::post('/',           [ServiceController::class, 'store'])->name('store');
    Route::put('/{service}',   [ServiceController::class, 'update'])->name('update');
    Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
});
// ── Finances & Rapports ───────────────────────────────────────────────────
Route::prefix('finances')->name('expenses.')->group(function () {
    Route::get('/',                      [ExpenseController::class, 'index'])->name('index');
    Route::post('/depenses',             [ExpenseController::class, 'storeExpense'])->name('store');
    Route::delete('/depenses/{expense}', [ExpenseController::class, 'destroyExpense'])->name('destroy');
});

// Confections
Route::post('/confections',            [ConfectionController::class, 'store'])->name('confections.store');
Route::put('/confections/{confection}', [ConfectionController::class, 'update'])->name('confections.update');
Route::delete('/confections/{confection}', [ConfectionController::class, 'destroy'])->name('confections.destroy');
// Fournisseurs
Route::resource('fournisseurs', SupplierController::class)->except(['show', 'edit', 'create']);

// Bons de commande / réapprovisionnement
// web.php — ordre important
Route::get('/reapprovisionnement',                        [PurchaseOrderController::class, 'index'])->name('purchases.index');
Route::post('/reapprovisionnement',                       [PurchaseOrderController::class, 'store'])->name('purchases.store');
Route::get('/reapprovisionnement/{order}/items',          [PurchaseOrderController::class, 'getItems'])->name('purchases.items');   // ← avant show
Route::get('/reapprovisionnement/{order}',                [PurchaseOrderController::class, 'show'])->name('purchases.show');
Route::post('/reapprovisionnement/{order}/recevoir',      [PurchaseOrderController::class, 'receive'])->name('purchases.receive');
Route::post('/reapprovisionnement/{order}/annuler',       [PurchaseOrderController::class, 'cancel'])->name('purchases.cancel');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
