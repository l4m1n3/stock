<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleService;
use App\Models\Invoice;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Affiche le point de vente avec tous les produits et services.
     */
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $products = Product::query()
            ->where('branch_id', $branchId)
            ->where('stock_quantity', '>=', 0)
            ->orderBy('name')
            ->get();
        $services = Service::orderBy('name')->get();

        return view('pos.pos', compact('products', 'services'));
    }

    /**
     * Enregistre une nouvelle vente depuis le POS.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:amana,nita,cash',
            'cart_data'      => 'required|json',
        ]);
        $branchId = auth()->user()->branch_id;
        $cartData = json_decode($request->cart_data, true);

        if (empty($cartData)) {
            return back()->with('error', 'Le panier est vide.');
        }

        DB::transaction(function () use ($request, $cartData) {

            // 1. Calculer le total
            $totalAmount = collect($cartData)->sum('total');

            // 2. Créer la vente
            $sale = Sale::create([
                'user_id'        => auth()->id(),
                'total_amount'   => $totalAmount,
                'payment_method' => $request->payment_method,
                'sold_at'        => now(),
                'branch_id'      => auth()->user()->branch_id,
            ]);

            // 3. Enregistrer chaque ligne du panier
            foreach ($cartData as $line) {
                if ($line['type'] === 'produit') {
                    // Ligne produit
                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $line['db_id'],
                        'quantity'   => $line['qty'],
                        'unit_price' => $line['unit_price'],
                        'total_price' => $line['total'],
                    ]);

                    // Décrémenter le stock
                    $product = Product::findOrFail($line['db_id']);
                    $product->decrement('stock_quantity', $line['qty']);

                    // Mouvement de stock
                    StockMovement::create([
                        'product_id' => $line['db_id'],
                        'type'       => 'out',
                        'quantity'   => $line['qty'],
                        'reason'     => 'Vente #' . $sale->id,
                        'branch_id'  => auth()->user()->branch_id,
                    ]);
                } elseif ($line['type'] === 'service') {
                    // Ligne service
                    SaleService::create([
                        'sale_id'    => $sale->id,
                        'service_id' => $line['db_id'],
                        'price'      => $line['total'],
                    ]);
                }
            }

            // 4. Générer la facture
            Invoice::create([
                'sale_id'        => $sale->id,
                'invoice_number' => 'INV-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                'total_amount'   => $totalAmount,
                'issued_at'      => now(),
                'branch_id'      => auth()->user()->branch_id,
            ]);
        });

        return redirect()->back()
            ->with('success', 'Vente enregistrée et facture générée avec succès.');
    }

    /**
     * Affiche la liste de toutes les ventes.
     */
    public function history()
    {
        $sales = Sale::with(['invoice', 'user'])
            ->latest('sold_at')
            ->paginate(20);

        return view('sales.history', compact('sales'));
    }

    /**
     * Affiche le détail d'une vente.
     */
    public function show(Sale $sale)
    {
        $sale->load(['saleItems.product', 'saleServices.service', 'invoice', 'user']);
        return view('sales.show', compact('sale'));
    }
}
