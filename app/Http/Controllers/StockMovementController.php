<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    // public function index()
    // {
    //     $branchId = auth()->user()->branch_id;
    //     $products = Product::where('branch_id', $branchId)->orderBy('name')->get();

    //     // KPIs
    //     $criticalCount     = $products->filter(fn($p) => $p->stock_quantity < ($p->alert_threshold ?? 0))->count();
    //     $stockValue        = $products->sum(fn($p) => $p->stock_quantity * $p->price);
    //     $recentMovements   = StockMovement::where('branch_id', $branchId)->where('created_at', '>=', Carbon::now()->subDays(30))->count();
    //     $recentMovementsData = StockMovement::where('branch_id', $branchId)->with('product')
    //         ->latest()
    //         ->take(8)
    //         ->get();

    //     return view('stock.stock', compact(
    //         'products',
    //         'criticalCount',
    //         'stockValue',
    //         'recentMovements',
    //         'recentMovementsData'
    //     ));
    // }
    // Dans la méthode index() du StockController
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        $products = Product::where('branch_id', $branchId)
            ->orderBy('name')
            ->get();

        // Récupérer le dernier prix d'achat par produit
        // depuis la dernière commande réceptionnée
        $lastPurchasePrices = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
            ->where('po.branch_id', $branchId)
            ->where('po.status', 'received')
            ->select('poi.product_id', 'poi.purchase_price')
            ->orderByDesc('po.received_at')
            ->get()
            ->unique('product_id')        // garde seulement le plus récent par produit
            ->keyBy('product_id');

        // Attacher le prix d'achat à chaque produit
        $products->each(function ($product) use ($lastPurchasePrices) {
            $product->last_purchase_price = $lastPurchasePrices[$product->id]->purchase_price ?? null;
        });

        // Marge brute si les deux prix sont disponibles
        $products->each(function ($product) {
            if ($product->last_purchase_price && $product->last_purchase_price > 0) {
                $product->margin = round(
                    (($product->price - $product->last_purchase_price) / $product->last_purchase_price) * 100,
                    1
                );
            } else {
                $product->margin = null;
            }
        });

        $criticalCount       = $products->filter(fn($p) => $p->stock_quantity < $p->alert_threshold && $p->stock_quantity > 0)->count();
        $stockValue          = $products->sum(fn($p) => $p->price * $p->stock_quantity);
        $recentMovements     = \App\Models\StockMovement::where('branch_id', $branchId)
            ->where('created_at', '>=', now()->subDays(30))->count();
        $recentMovementsData = \App\Models\StockMovement::with('product')
            ->where('branch_id', $branchId)
            ->latest()->take(10)->get();

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
        $branchId = auth()->user()->branch_id;
        $last30   = Carbon::now()->subDays(30);

        $movements = StockMovement::where('branch_id', $branchId)
            ->with(['product'])
            ->latest()
            ->paginate(20);

        $products = Product::where('branch_id', $branchId)
            ->orderBy('name')
            ->get();

        $totalMovements = StockMovement::where('branch_id', $branchId)->count();

        $inCount  = StockMovement::where('branch_id', $branchId)->where('type', 'in')->where('created_at', '>=', $last30)->count();
        $inQty    = StockMovement::where('branch_id', $branchId)->where('type', 'in')->where('created_at', '>=', $last30)->sum('quantity');

        $outCount = StockMovement::where('branch_id', $branchId)->where('type', 'out')->where('created_at', '>=', $last30)->count();
        $outQty   = StockMovement::where('branch_id', $branchId)->where('type', 'out')->where('created_at', '>=', $last30)->sum('quantity');

        $lossCount = StockMovement::where('branch_id', $branchId)->where('type', 'loss')->where('created_at', '>=', $last30)->count();
        $lossQty   = StockMovement::where('branch_id', $branchId)->where('type', 'loss')->where('created_at', '>=', $last30)->sum('quantity');

        return view('stock.movements', compact(
            'movements',
            'products',
            'totalMovements',
            'inCount',
            'inQty',
            'outCount',
            'outQty',
            'lossCount',
            'lossQty'
        ));
    }

    public function storeMovement(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:in,out,loss',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'nullable|string|max:255',
        ]);
        $userBranchId = auth()->user()->branch_id;
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
            'branch_id'  => $userBranchId,
        ]);

        // Mettre à jour le stock produit
        if ($request->type === 'in') {
            $product->increment('stock_quantity', $request->quantity);
        } else {
            $product->decrement('stock_quantity', $request->quantity);
        }

        return back()->with('success', 'Mouvement de stock enregistré avec succès.');
    }
}
