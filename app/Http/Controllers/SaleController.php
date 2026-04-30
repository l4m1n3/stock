<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleService;
use App\Models\SaleConfection;
use App\Models\Invoice;
use App\Models\StockMovement;
use App\Models\Confection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        $products = Product::where('branch_id', $branchId)
            ->where('stock_quantity', '>=', 0)
            ->orderBy('name')->get();

        $services    = Service::orderBy('name')->get();

        $confections = Confection::with('products')
            ->where('branch_id', $branchId)
            ->orderBy('name')->get();

        // ✅ FIX : les appels de méthode ne s'interpolent pas dans les strings PHP.
        //    "$collection->count()" est lu comme accès à la propriété 'count' → erreur.
        //    Solution : extraire les valeurs dans des variables scalaires avant l'interpolation.
        $pCount = $products->count();
        $sCount = $services->count();
        $cCount = $confections->count();

        activity_log(
            'sale_index',
            "Consultation produits/services/confections pour vente : {$pCount} produits, {$sCount} services, {$cCount} confections"
        );

        return view('pos.pos', compact('products', 'services', 'confections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,amana,nita,western_union,moneygram,wave',
            'cart_data'      => 'required|json',
        ]);

        $cartData = json_decode($request->cart_data, true);

        if (empty($cartData)) {
            return back()->with('error', 'Le panier est vide.');
        }

        // ✅ FIX : $sale déclaré hors de la closure pour être accessible
        //    après DB::transaction() (activity_log en avait besoin)
        $sale = null;

        DB::transaction(function () use ($request, $cartData, &$sale) {

            $totalAmount = collect($cartData)->sum('total');

            $sale = Sale::create([
                'user_id'        => auth()->id(),
                'total_amount'   => $totalAmount,
                'payment_method' => $request->payment_method,
                'sold_at'        => now(),
                'branch_id'      => auth()->user()->branch_id,
            ]);

            foreach ($cartData as $line) {

                if ($line['type'] === 'produit') {

                    SaleItem::create([
                        'sale_id'     => $sale->id,
                        'product_id'  => $line['db_id'],
                        'quantity'    => $line['qty'],
                        'unit_price'  => $line['unit_price'],
                        'total_price' => $line['total'],
                    ]);

                    $product = Product::findOrFail($line['db_id']);
                    $product->decrement('stock_quantity', $line['qty']);

                    StockMovement::create([
                        'product_id' => $line['db_id'],
                        'type'       => 'out',
                        'quantity'   => $line['qty'],
                        'reason'     => 'Vente #' . $sale->id,
                        'branch_id'  => auth()->user()->branch_id,
                    ]);

                } elseif ($line['type'] === 'service') {

                    SaleService::create([
                        'sale_id'    => $sale->id,
                        'service_id' => $line['db_id'],
                        'price'      => $line['total'],
                    ]);

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
                            'branch_id'  => auth()->user()->branch_id,
                        ]);
                    }
                }
            }

            Invoice::create([
                'sale_id'        => $sale->id,
                'invoice_number' => 'INV-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                'total_amount'   => $totalAmount,
                'issued_at'      => now(),
                'branch_id'      => auth()->user()->branch_id,
            ]);
        });

        // ✅ FIX : $sale est maintenant accessible ici grâce au passage par référence &$sale
        activity_log('sale_created', "Vente #{$sale->id} créée");

        return redirect()->back()->with('success', 'Vente enregistrée et facture générée avec succès.');
    }

    public function history()
    {
        $branchId = auth()->user()->branch_id;

        $sales = Sale::with(['invoice', 'user'])
            ->where('branch_id', $branchId)   // ✅ FIX : filtrer par branche (manquait)
            ->latest('sold_at')
            ->paginate(20);

        // ✅ FIX : même problème d'interpolation — utiliser une variable scalaire
        $total = $sales->total();
        activity_log('sale_history', "Consultation historique des ventes : {$total} ventes affichées");

        return view('sales.history', compact('sales'));
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'saleItems.product',
            'saleServices.service',
            'saleConfections.confection',
            'invoice',
            'user',
        ]);

        activity_log('sale_show', "Consultation vente #{$sale->id}");

        return view('sales.show', compact('sale'));
    }
}