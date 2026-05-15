<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\Service;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleService;
use App\Models\SaleConfection;
use App\Models\Invoice;
use App\Models\StockMovement;
use App\Models\Confection;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;

class SaleController extends Controller
{
    // ── POS — affichage ───────────────────────────────────────────────────────
    // public function index()
    // {
    //     $branchId = auth()->user()->branch_id;

    //     $products = Product::where('branch_id', $branchId)
    //         ->where('stock_quantity', '>=', 0)
    //         ->orderBy('name')->get();

    //     $services = Service::orderBy('name')->get();

    //     $confections = Confection::with('products')
    //         ->where('branch_id', $branchId)
    //         ->orderBy('name')->get();

    //     // Liste clients pour l'autocomplete POS (toutes agences, trié par nom)
    //     $clients = Client::orderBy('name')->get(['id', 'name', 'phone']);

    //     $pCount = $products->count();
    //     $sCount = $services->count();
    //     $cCount = $confections->count();

    //     activity_log(
    //         'sale_index',
    //         "Consultation POS : {$pCount} produits, {$sCount} services, {$cCount} confections"
    //     );

    //     return view('pos.pos', compact('products', 'services', 'confections', 'clients'));
    // }

    // Dans SaleController@index — remplacer le return view() existant par ceci :

    public function index()
    {
        $branchId = auth()->user()->branch_id;

        $products = Product::where('branch_id', $branchId)
            ->where('stock_quantity', '>=', 0)
            ->orderBy('name')->get();

        $services = Service::orderBy('name')->get();

        $confections = Confection::with('products')
            ->where('branch_id', $branchId)
            ->orderBy('name')->get();

        $clients = Client::orderBy('name')->get(['id', 'name', 'phone']);

        // ── Pré-sérialisation pour éviter les closures dans @json() Blade ──────
        // @json() avec fn() imbriqué dans foreach génère un ParseError Blade.
        // On prépare les tableaux scalaires ici, côté PHP.

        $productsJson = $products->map(fn($p) => [
            'id'    => 'p' . $p->id,
            'name'  => $p->name,
            'type'  => 'produit',
            'price' => (float) $p->price,
            'stock' => (int) $p->stock_quantity,
            'dbId'  => $p->id,
        ])->values()->toArray();

        $servicesJson = $services->map(fn($s) => [
            'id'          => 's' . $s->id,
            'name'        => $s->name,
            'type'        => 'service',
            'price'       => (float) $s->price,
            'stock'       => null,
            'dbId'        => $s->id,
            'serviceType' => $s->type,
        ])->values()->toArray();

        $confectionsJson = $confections->map(fn($c) => [
            'id'          => 'c' . $c->id,
            'name'        => $c->name,
            'type'        => 'confection',
            'price'       => (float) $c->total_price,
            'stock'       => (int) ($c->products->min('stock_quantity') ?? 0),
            'dbId'        => $c->id,
            'composition' => $c->products->map(fn($p) => [
                'name' => $p->name,
                'qty'  => $p->pivot->quantity,
            ])->toArray(),
        ])->values()->toArray();

        $clientsJson = $clients->map(fn($c) => [
            'id'    => $c->id,
            'name'  => $c->name,
            'phone' => $c->phone ?? '',
        ])->values()->toArray();

        $pCount = $products->count();
        $sCount = $services->count();
        $cCount = $confections->count();

        activity_log(
            'sale_index',
            "Consultation POS : {$pCount} produits, {$sCount} services, {$cCount} confections"
        );

        return view('pos.pos', compact(
            'products',
            'services',
            'confections',
            'clients',
            'productsJson',
            'servicesJson',
            'confectionsJson',
            'clientsJson'
        ));
    }
    // ── POS — enregistrement ──────────────────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $request->validate([
                'payment_method' => 'required|in:cash,amana,nita,western_union,moneygram,wave',
                'cart_data'      => 'required|json',
                'is_anonymous'   => 'nullable|boolean',
                'client_id'      => 'nullable|exists:clients,id',
                'client_name'    => 'nullable|string|max:255',
                'client_phone'   => 'nullable|string|max:30',
            ]);

            $cartData = json_decode($request->cart_data, true);

            if (empty($cartData)) {
                return back()->with('error', 'Le panier est vide.');
            }

            $sale = null;

            DB::transaction(function () use ($request, $cartData, &$sale) {

                // ── 1. Résolution du client ───────────────────────────────────
                $clientId   = null;
                $isAnon     = $request->boolean('is_anonymous');
                $branchId   = auth()->user()->branch_id;

                if (!$isAnon) {
                    if ($request->filled('client_id')) {
                        // Client existant sélectionné dans l'autocomplete
                        $clientId = (int) $request->client_id;
                    } elseif ($request->filled('client_name')) {
                        // Nouveau client saisi en inline — on le crée
                        $newClient = Client::create([
                            'name'      => $request->client_name,
                            'phone'     => $request->client_phone ?: null,
                            'branch_id' => $branchId,
                        ]);
                        $clientId = $newClient->id;
                    }
                    // Si ni client_id ni client_name → vente sans client (mode 'none')
                }

                // ── 2. Création de la vente ───────────────────────────────────
                $totalAmount = collect($cartData)->sum('total');

                $sale = Sale::create([
                    'user_id'        => auth()->id(),
                    'client_id'      => $clientId,   // nullable
                    'total_amount'   => $totalAmount,
                    'payment_method' => $request->payment_method,
                    'sold_at'        => now(),
                    'branch_id'      => $branchId,
                ]);

                // ── 3. Lignes du panier ───────────────────────────────────────
                foreach ($cartData as $line) {

                    if ($line['type'] === 'produit') {

                        SaleItem::create([
                            'sale_id'     => $sale->id,
                            'product_id'  => $line['db_id'],
                            'quantity'    => $line['qty'],
                            'unit_price'  => $line['unit_price'],
                            'total_price' => $line['total'],
                        ]);

                        Product::findOrFail($line['db_id'])
                            ->decrement('stock_quantity', $line['qty']);

                        StockMovement::create([
                            'product_id' => $line['db_id'],
                            'type'       => 'out',
                            'quantity'   => $line['qty'],
                            'reason'     => 'Vente #' . $sale->id,
                            'branch_id'  => $branchId,
                        ]);
                    } elseif ($line['type'] === 'service') {

                        $service = Service::findOrFail($line['db_id']);

                        SaleService::create([
                            'sale_id'    => $sale->id,
                            'service_id' => $line['db_id'],
                            'price'      => $line['total'],
                        ]);

                        if ($service->is_delivery) {
                            Expense::create([
                                'title'        => 'Frais livraison - Vente #' . $sale->id,
                                'amount'       => $line['total'],
                                'type'         => 'livraison',
                                'expense_date' => now(),
                                'status'       => 'pending',
                                'branch_id'    => $branchId,
                            ]);
                        }
                    } elseif ($line['type'] === 'confection') {

                        SaleConfection::create([
                            'sale_id'       => $sale->id,
                            'confection_id' => $line['db_id'],
                            'quantity'      => $line['qty'],
                            'unit_price'    => $line['unit_price'],
                            'total_price'   => $line['total'],
                        ]);

                        $confection = Confection::with('products')->findOrFail($line['db_id']);

                        foreach ($confection->products as $component) {
                            $needed = $component->pivot->quantity * $line['qty'];
                            $component->decrement('stock_quantity', $needed);

                            StockMovement::create([
                                'product_id' => $component->id,
                                'type'       => 'out',
                                'quantity'   => $needed,
                                'reason'     => "Confection \"{$confection->name}\" — Vente #{$sale->id}",
                                'branch_id'  => $branchId,
                            ]);
                        }
                    }
                }

                // ── 4. Facture ────────────────────────────────────────────────
                Invoice::create([
                    'sale_id'        => $sale->id,
                    'invoice_number' => 'INV-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                    'total_amount'   => $totalAmount,
                    'issued_at'      => now(),
                    'branch_id'      => $branchId,
                ]);

                // ── 5. Mise à jour des totaux client ──────────────────────────
                if ($clientId) {
                    Client::findOrFail($clientId)->recomputeTotals();
                }
            });

            $clientLabel = $sale->client_id
                ? "client #{$sale->client_id}"
                : ($request->boolean('is_anonymous') ? 'vente anonyme' : 'sans client');

            activity_log('sale_created', "Vente #{$sale->id} créée ({$clientLabel})");

            return redirect()->back()->with('success', 'Vente enregistrée et facture générée avec succès.');
        } catch (\Throwable $th) {
            FacadesLog::error('Erreur lors de la création de la vente : ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);
            return back()->with('error', 'Une erreur est survenue lors de l\'enregistrement de la vente. Veuillez réessayer.');
        }
    }

    // ── Historique ────────────────────────────────────────────────────────────
    public function history()
    {
        $branchId = auth()->user()->branch_id;

        $sales = Sale::with(['invoice', 'user', 'client'])
            ->where('branch_id', $branchId)
            ->latest('sold_at')
            ->paginate(20);

        $total = $sales->total();
        activity_log('sale_history', "Consultation historique des ventes : {$total} ventes affichées");

        return view('sales.history', compact('sales'));
    }

    // ── Détail vente ──────────────────────────────────────────────────────────
    public function show(Sale $sale)
    {
        $sale->load([
            'saleItems.product',
            'saleServices.service',
            'saleConfections.confection',
            'invoice',
            'user',
            'client',        // ← ajouté
        ]);

        activity_log('sale_show', "Consultation vente #{$sale->id}");

        return view('sales.show', compact('sale'));
    }
}
