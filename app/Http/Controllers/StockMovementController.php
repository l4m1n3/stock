<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;

class StockMovementController extends Controller
{
   public function index()
    {
        $products = Product::orderBy('name')->get();

        // KPIs
        $criticalCount     = $products->filter(fn($p) => $p->stock_quantity < ($p->alert_threshold ?? 0))->count();
        $stockValue        = $products->sum(fn($p) => $p->stock_quantity * $p->price);
        $recentMovements   = StockMovement::where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $recentMovementsData = StockMovement::with('product')
                                ->latest()
                                ->take(8)
                                ->get();

        return view('stock.stock', compact(
            'products',
            'criticalCount',
            'stockValue',
            'recentMovements',
            'recentMovementsData'
        ));
    }

    public function movements()
    {
        $movements = StockMovement::with('product')->latest()->paginate(30);
        return view('stock.movements', compact('movements'));
    }

    public function storeMovement(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:in,out,loss',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Vérifier stock suffisant pour sortie/perte
        if (in_array($request->type, ['out', 'loss']) && $product->stock_quantity < $request->quantity) {
            return back()->with('error', 'Stock insuffisant pour effectuer cette opération.');
        }

        // Enregistrer le mouvement
        StockMovement::create([
            'product_id' => $request->product_id,
            'type'       => $request->type,
            'quantity'   => $request->quantity,
            'reason'     => $request->reason,
        ]);

        // Mettre à jour le stock produit
        if ($request->type === 'in') {
            $product->increment('stock_quantity', $request->quantity);
        } else {
            $product->decrement('stock_quantity', $request->quantity);
        }

        return back()->with('success', 'Mouvement de stock enregistré avec succès.');
    }

    public function create()
    {
        // Logique pour afficher le formulaire de création d'un mouvement de stock
    }

    public function store(Request $request)
    {
        // Logique pour enregistrer un nouveau mouvement de stock
    }

    public function show($id)
    {
        // Logique pour afficher les détails d'un mouvement de stock spécifique
    }

    public function edit($id)
    {
        // Logique pour afficher le formulaire d'édition d'un mouvement de stock
    }

    public function update(Request $request, $id)
    {
        // Logique pour mettre à jour un mouvement de stock existant
    }

    public function destroy($id)
    {
        // Logique pour supprimer un mouvement de stock
    }
}
