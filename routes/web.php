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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PaieController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ServiceScheduleController;

// Login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('logs', [LogController::class, 'index'])->name('logs');
    // Dashboard admin
    Route::get('/',               [AdminController::class, 'index'])->name('index');

    // Branches
    Route::get('branches',                    [AdminController::class, 'branches'])->name('branches');
    Route::get('branches/create',             [AdminController::class, 'createBranch'])->name('branches.create');
    Route::post('branches',                   [AdminController::class, 'storeBranch'])->name('branches.store');
    Route::get('branches/{branch}/edit',      [AdminController::class, 'editBranch'])->name('branches.edit');
    Route::put('branches/{branch}',           [AdminController::class, 'updateBranch'])->name('branches.update');
    Route::delete('branches/{branch}',        [AdminController::class, 'destroyBranch'])->name('branches.destroy');

    // Stock global
    Route::get('stock',                       [AdminController::class, 'stock'])->name('stock');

    // Facturation globale
    Route::get('invoices',                    [AdminController::class, 'invoices'])->name('invoices');
    Route::delete('invoices/{invoice}',             [AdminController::class, 'destroyInvoices'])->name('invoices.destroy');
    Route::post('invoices/{invoice}/cancel',             [AdminController::class, 'cancelInvoice'])->name('invoices.cancel');
    // Finances & Rapports
    Route::get('finances',                    [AdminController::class, 'finances'])->name('finances');
    Route::get('finances/pdf',                    [AdminController::class, 'exportPdf'])->name('finances.pdf');
    // Utilisateurs
    Route::get('users',                       [AdminController::class, 'users'])->name('users');
    Route::get('users/create',                [AdminController::class, 'createUser'])->name('users.create');
    Route::post('users',                      [AdminController::class, 'storeUser'])->name('users.store');
    Route::get('users/{user}/edit',           [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('users/{user}',                [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('users/{user}',             [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('users/{user}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');

    Route::get('/paie', [PaieController::class, 'index'])->name('paie.index');
    Route::post('/paie', [PaieController::class, 'store'])->name('paie.store');
    Route::put('/paie/{paie}', [PaieController::class, 'update'])->name('paie.update');
    Route::delete('/paie/{paie}', [PaieController::class, 'destroy'])->name('paie.destroy');
    Route::get('/paie/pdf', [PaieController::class, 'pdf'])->name('paie.pdf');
    Route::get('/paie/fiche/{user}', [PaieController::class, 'generateFichePaiement'])->name('paies.fiche');

    Route::get('clients/export', [ClientController::class, 'export'])->name('clients.export');
    Route::resource('clients', ClientController::class);
    Route::resource('clients', ClientController::class)->except(['create', 'edit']);
    // ── Planification des prestations ──────────────────────────
    Route::get('schedules',                     [AdminController::class, 'schedules'])->name('schedules');
    Route::get('schedules/create',              [AdminController::class, 'createSchedule'])->name('schedules.create');
    Route::post('schedules',                     [AdminController::class, 'storeSchedule'])->name('schedules.store');
    Route::get('schedules/{schedule}',          [AdminController::class, 'showSchedule'])->name('schedules.show');
    Route::get('schedules/{schedule}/edit',     [AdminController::class, 'editSchedule'])->name('schedules.edit');
    Route::put('schedules/{schedule}',          [AdminController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('schedules/{schedule}',        [AdminController::class, 'destroySchedule'])->name('schedules.destroy');
    Route::patch('schedules/{schedule}/status',  [AdminController::class, 'updateScheduleStatus'])->name('schedules.update-status');
    Route::get(
        '/service-schedules/{serviceSchedule}/print',
        [ServiceScheduleController::class, 'printSingle']
    )->name('service-schedules.print-single');
});

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
  
    Route::put('/{expense}', [ExpenseController::class, 'updateExpense'])
        ->name('update');
    Route::delete('/depenses/{expense}', [ExpenseController::class, 'destroyExpense'])->name('destroy');
    Route::get('/depenses/export', [ExpenseController::class, 'export'])
        ->name('export');
    Route::get('/export-global', [ExpenseController::class, 'exportFinance'])
        ->name('Globalexport');
    Route::post('/cash-transfer', [ExpenseController::class, 'transferCash'])
        ->name('cash.transfer');
});

// Confections
Route::post('/confections',            [ConfectionController::class, 'store'])->name('confections.store');
Route::put('/confections/{confection}', [ConfectionController::class, 'update'])->name('confections.update');
Route::delete('/confections/{confection}', [ConfectionController::class, 'destroy'])->name('confections.destroy');
// Fournisseurs
// Route::resource('fournisseurs', SupplierController::class)->except(['show', 'edit', 'create']);
Route::resource('fournisseurs', SupplierController::class)
    ->parameters(['fournisseurs' => 'supplier']);
// Bons de commande / réapprovisionnement

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

Route::middleware(['auth'])->group(function () {

    // CRUD principal
    Route::resource('service-schedules', ServiceScheduleController::class);
    Route::get('/service-schedules/print', [ServiceScheduleController::class, 'print'])
        ->name('service-schedules.print');
    Route::get(
        '/service-schedules/{serviceSchedule}/print',
        [ServiceScheduleController::class, 'printSingle']
    )->name('service-schedules.print-single');


    // Mise à jour rapide du statut (PATCH)
    Route::patch(
        'service-schedules/{serviceSchedule}/status',
        [ServiceScheduleController::class, 'updateStatus']
    )->name('service-schedules.update-status');
});
require __DIR__ . '/auth.php';
