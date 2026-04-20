<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('finances.index');
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
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
