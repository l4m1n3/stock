<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseOrderItems;
use App\Models\StockMovement;
use App\Models\Branch;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        $orders = PurchaseOrder::with(['supplier', 'user', 'items'])
            ->where('branch_id', $branchId)
            ->latest('ordered_at')
            ->paginate(20);

        $suppliers = Supplier::where('branch_id', $branchId)->orderBy('name')->get();
        $products  = Product::where('branch_id', $branchId)->orderBy('name')->get();

        // KPIs
        $monthStart    = Carbon::now()->startOfMonth();
        $monthOrders   = PurchaseOrder::where('branch_id', $branchId)->where('ordered_at', '>=', $monthStart)->count();
        $monthSpend    = PurchaseOrder::where('branch_id', $branchId)->where('status', 'received')->where('received_at', '>=', $monthStart)->sum('total_amount');
        $pendingCount  = PurchaseOrder::where('branch_id', $branchId)->where('status', 'pending')->count();

        return view('purchase.index', compact(
            'orders',
            'suppliers',
            'products',
            'monthOrders',
            'monthSpend',
            'pendingCount'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'notes'       => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.selling_price'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $order = PurchaseOrder::create([
                'supplier_id'  => $request->supplier_id,
                'user_id'      => auth()->id(),
                'branch_id'    => auth()->user()->branch_id,
                'status'       => 'pending',
                'notes'        => $request->notes,
                'ordered_at'   => now(),
                'total_amount' => 0,
            ]);

            foreach ($request->items as $line) {
                PurchaseOrderItems::create([
                    'purchase_order_id' => $order->id,
                    'product_id'        => $line['product_id'],
                    'quantity_ordered'  => $line['quantity'],
                    'quantity_received' => 0,
                    'purchase_price'    => $line['purchase_price'],
                    'selling_price'     => $line['selling_price'] ?? null,
                ]);
            }

            $order->recalcTotal();
        });

        return back()->with('success', 'Bon de commande créé avec succès.');
    }

    /**
     * Réceptionner une commande : incrémente le stock, met à jour prix de vente si fourni.
     */
    // PurchaseOrderController::receive() — version corrigée complète
    // public function receive(Request $request, PurchaseOrder $order)
    // {
    //     abort_if($order->branch_id !== auth()->user()->branch_id, 403);
    //     abort_if(!$order->isPending(), 403, 'Commande déjà réceptionnée ou annulée.');

    //     $request->validate([
    //         'items'                             => 'required|array',
    //         'items.*.id'                        => 'required|exists:purchase_order_items,id',
    //         'items.*.quantity_received'         => 'required|integer|min:0',
    //     ]);

    //     DB::transaction(function () use ($request, $order) {
    //         foreach ($request->items as $line) {
    //             $qty = (int) $line['quantity_received'];

    //             // ✅ Vérification que l'item appartient bien à cette commande
    //             $item = PurchaseOrderItem::where('id', $line['id'])
    //                 ->where('purchase_order_id', $order->id)  // ← sécurité ajoutée
    //                 ->firstOrFail();

    //             if ($qty <= 0) continue;

    //             $item->update(['quantity_received' => $qty]);

    //             $product = $item->product;

    //             // Incrémenter le stock
    //             $product->increment('stock_quantity', $qty);

    //             // ✅ Mise à jour prix de vente uniquement si selling_price > 0
    //             if (!empty($item->selling_price) && $item->selling_price > 0) {
    //                 $product->update(['price' => $item->selling_price]);
    //             }

    //             // Mouvement de stock
    //             StockMovement::create([
    //                 'product_id' => $product->id,
    //                 'type'       => 'in',
    //                 'quantity'   => $qty,
    //                 'reason'     => "Réception commande #{$order->id} — {$order->supplier->name}",
    //                 'branch_id'  => $order->branch_id,
    //             ]);
    //         }

    //         $order->update([
    //             'status'      => 'received',
    //             'received_at' => now(),
    //         ]);
    //     });

    //     return back()->with('success', 'Commande réceptionnée et stocks mis à jour.');
    // }
// app/Http/Controllers/PurchaseOrderController.php
public function receive(Request $request, PurchaseOrder $order)
{
    abort_if($order->branch_id !== auth()->user()->branch_id, 403);
    abort_if(!$order->isPending(), 403, 'Commande déjà réceptionnée ou annulée.');

    $request->validate([
        'items'                     => 'required|array',
        'items.*.id'                => 'required|exists:purchase_order_items,id',
        'items.*.quantity_received' => 'required|integer|min:0',
    ]);

    DB::transaction(function () use ($request, $order) {
        foreach ($request->items as $line) {
            $qty = (int) $line['quantity_received'];

            $item = PurchaseOrderItems::with('product')  // ← eager load, évite le lazy null
                ->where('id', $line['id'])
                ->where('purchase_order_id', $order->id)
                ->firstOrFail();

            if ($qty <= 0) continue;

            $product = $item->product;

            // ← Guard explicite : si produit introuvable, erreur claire
            if (!$product) {
                throw new \RuntimeException(
                    "Produit introuvable pour l'article #{$item->id}"
                );
            }

            $item->update(['quantity_received' => $qty]);

            // Incrémenter le stock
            $product->increment('stock_quantity', $qty);

            // Mettre à jour le prix de vente si renseigné
            if (!empty($item->selling_price) && $item->selling_price > 0) {
                $product->update(['price' => $item->selling_price]);
            }

            // Mouvement de stock
            StockMovement::create([
                'product_id' => $product->id,
                'type'       => 'in',
                'quantity'   => $qty,
                'reason'     => "Réception commande #{$order->id} — {$order->supplier->name}",
                'branch_id'  => $order->branch_id,
            ]);
        }

        $order->update([
            'status'      => 'received',
            'received_at' => now(),
        ]);
    });

    return back()->with('success', 'Commande réceptionnée et stocks mis à jour.');
}
    public function cancel(PurchaseOrder $order)
    {
        abort_if(!$order->isPending(), 403, 'Impossible d\'annuler.');
        $order->update(['status' => 'cancelled']);
        return back()->with('success', 'Commande annulée.');
    }

    public function show(PurchaseOrder $order)
    {
        $order->load(['supplier', 'user', 'items.product', 'branch']);
        return view('purchases.show', compact('order'));
    }
    // Méthode à ajouter dans PurchaseOrderController
    public function getItems(PurchaseOrder $order)
    {
        abort_if($order->branch_id !== auth()->user()->branch_id, 403);
        $order->load('items.product');
        return response()->json($order->items);
    }
}
