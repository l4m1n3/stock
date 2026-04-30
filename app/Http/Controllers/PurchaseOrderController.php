<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItems;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Branch;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // LISTE DES COMMANDES
    // ═══════════════════════════════════════════════════════════════
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
        $monthStart   = Carbon::now()->startOfMonth();

        $monthOrders  = PurchaseOrder::where('branch_id', $branchId)
            ->where('ordered_at', '>=', $monthStart)
            ->count();

        $monthSpend   = PurchaseOrder::where('branch_id', $branchId)
            ->where('status', 'received')
            ->where('received_at', '>=', $monthStart)
            ->sum('total_amount');

        $pendingCount = PurchaseOrder::where('branch_id', $branchId)
            ->where('status', 'pending')
            ->count();

        activity_log('view_purchase_orders', "Consultation commandes");

        return view('purchase.index', compact(
            'orders',
            'suppliers',
            'products',
            'monthOrders',
            'monthSpend',
            'pendingCount'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // CRÉATION COMMANDE
    // ═══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where(fn($q) =>
                    $q->where('branch_id', auth()->user()->branch_id)
                ),
            ],
            'notes' => 'nullable|string',

            'items' => 'required|array|min:1',

            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn($q) =>
                    $q->where('branch_id', auth()->user()->branch_id)
                ),
            ],

            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.selling_price'  => 'nullable|numeric|min:0',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {

            $order = PurchaseOrder::create([
                'supplier_id'  => $validated['supplier_id'],
                'user_id'      => auth()->id(),
                'branch_id'    => auth()->user()->branch_id,
                'status'       => 'pending',
                'notes'        => $validated['notes'] ?? null,
                'ordered_at'   => now(),
                'total_amount' => 0,
            ]);

            foreach ($validated['items'] as $line) {
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

            return $order;
        });

        activity_log('purchase_order_created', "Commande #{$order->id} créée");

        return back()->with('success', 'Bon de commande créé avec succès.');
    }

    // ═══════════════════════════════════════════════════════════════
    // RÉCEPTION COMMANDE
    // ═══════════════════════════════════════════════════════════════
    // public function receive(Request $request, PurchaseOrder $order)
    // {
    //     abort_if($order->branch_id !== auth()->user()->branch_id, 403);
    //     abort_if(!$order->isPending(), 403, 'Commande déjà traitée.');

    //     $validated = $request->validate([
    //         'items'                     => 'required|array',
    //         'items.*.id'                => 'required|exists:purchase_order_items,id',
    //         'items.*.quantity_received' => 'required|integer|min:0',
    //     ]);

    //     DB::transaction(function () use ($validated, $order) {

    //         foreach ($validated['items'] as $line) {

    //             $qty = (int) $line['quantity_received'];

    //             $item = PurchaseOrderItems::with('product')
    //                 ->where('id', $line['id'])
    //                 ->where('purchase_order_id', $order->id)
    //                 ->firstOrFail();

    //             if ($qty <= 0) continue;

    //             $remaining = $item->quantity_ordered - $item->quantity_received;

    //             if ($qty > $remaining) {
    //                 throw new \Exception("Quantité dépasse le restant pour item #{$item->id}");
    //             }

    //             $product = $item->product;

    //             if (!$product) {
    //                 throw new \Exception("Produit introuvable");
    //             }

    //             // ✅ cumul réception
    //             $item->increment('quantity_received', $qty);

    //             // ✅ stock
    //             $product->increment('stock_quantity', $qty);

    //             // ✅ MAJ prix
    //             if (!empty($item->selling_price) && $item->selling_price > 0) {
    //                 $product->update(['price' => $item->selling_price]);
    //             }

    //             // ✅ mouvement stock
    //             StockMovement::create([
    //                 'product_id' => $product->id,
    //                 'type'       => 'in',
    //                 'quantity'   => $qty,
    //                 'reason'     => "Commande #{$order->id}",
    //                 'branch_id'  => $order->branch_id,
    //             ]);
    //         }
    //         //  protected $fillable = ['title', 'amount', 'type', 'expense_date','branch_id'];
    //             Expense::create([
    //                 'branch_id'  => $order->branch_id,
    //                 'title'       => "Achat - Commande #{$order->id}",
    //                 'amount'     => $order->total_amount,
    //                 'type'       => 'commande',
    //                 'description' => "Achat - Commande #{$order->id}",
    //                 'date'        => now(),
    //             ]);
    //         // ✅ statut dynamique
    //         $totalOrdered  = $order->items()->sum('quantity_ordered');
    //         $totalReceived = $order->items()->sum('quantity_received');

    //         $status = ($totalReceived >= $totalOrdered) ? 'received' : 'partial';

    //         $order->update([
    //             'status'      => $status,
    //             'received_at' => now(),
    //         ]);
    //     });

    //     activity_log(
    //         'purchase_order_received',
    //         "Réception commande #{$order->id}"
    //     );

    //     return back()->with('success', 'Commande réceptionnée.');
    // }


public function receive(Request $request, PurchaseOrder $order)
{
    abort_if($order->branch_id !== auth()->user()->branch_id, 403);
    abort_if(!$order->isPending(), 403, 'Commande déjà traitée.');

    $validated = $request->validate([
        'items'                     => 'required|array',
        'items.*.id'                => 'required|exists:purchase_order_items,id',
        'items.*.quantity_received' => 'required|integer|min:0',
    ]);

    DB::transaction(function () use ($validated, $order) {

        foreach ($validated['items'] as $line) {

            $qty = (int) $line['quantity_received'];

            // ✅ FIX 1 : bon nom de modèle PurchaseOrderItem (sans 's')
            $item = PurchaseOrderItems::with('product')
                ->where('id', $line['id'])
                ->where('purchase_order_id', $order->id)
                ->firstOrFail();

            if ($qty <= 0) continue;

            $remaining = $item->quantity_ordered - $item->quantity_received;

            if ($qty > $remaining) {
                throw new \Exception("Quantité dépasse le restant pour item #{$item->id}");
            }

            $product = $item->product;

            if (!$product) {
                throw new \Exception("Produit introuvable pour l'item #{$item->id}");
            }

            $item->increment('quantity_received', $qty);

            $product->increment('stock_quantity', $qty);

            if (!empty($item->selling_price) && $item->selling_price > 0) {
                $product->update(['price' => $item->selling_price]);
            }

            StockMovement::create([
                'product_id' => $product->id,
                'type'       => 'in',
                'quantity'   => $qty,
                'reason'     => "Réception commande #{$order->id}",
                'branch_id'  => $order->branch_id,
            ]);
        }

        // ✅ FIX 2 : champs corrects selon la migration Expense
        // Migration : title, amount, type, expense_date, status, branch_id
        // Suppression des champs inexistants : description, date
        Expense::create([
            'branch_id'    => $order->branch_id,
            'title'        => "Achat - Commande #{$order->id}",
            'amount'       => $order->total_amount,
            'type'         => 'commande',
            'expense_date' => now()->toDateString(), // ✅ date (pas datetime) + bon nom de colonne
            'status'       => 'payé',               // ✅ champ requis avec valeur métier correcte
        ]);

        // ✅ FIX 3 : recalcul après tous les incréments (fraîcheur des données)
        $order->refresh();

        $totalOrdered  = $order->items()->sum('quantity_ordered');
        $totalReceived = $order->items()->sum('quantity_received');

        // ✅ FIX 4 : statut 'partial' absent de l'enum migration ['pending','received','cancelled']
        // → on n'utilise 'received' que si tout est reçu, sinon on laisse 'pending'
        $newStatus = ($totalReceived >= $totalOrdered) ? 'received' : 'pending';

        $order->update([
            'status'      => $newStatus,
            'received_at' => now(),
        ]);
    });

    // ✅ FIX 5 : activity_log attend probablement un user_id — vérifier le helper
    // Si c'est un helper global : activity_log(string $action, string $desc)
    activity_log(
        'purchase_order_received',
        "Réception commande #{$order->id} — branche #{$order->branch_id}"
    );

    return back()->with('success', 'Commande réceptionnée avec succès.');
}
    // ═══════════════════════════════════════════════════════════════
    // ANNULATION
    // ═══════════════════════════════════════════════════════════════
    public function cancel(PurchaseOrder $order)
    {
        abort_if($order->branch_id !== auth()->user()->branch_id, 403);
        abort_if(!$order->isPending(), 403);

        $order->update(['status' => 'cancelled']);

        activity_log('purchase_order_cancelled', "Commande #{$order->id} annulée");

        return back()->with('success', 'Commande annulée.');
    }

    // ═══════════════════════════════════════════════════════════════
    // DETAIL
    // ═══════════════════════════════════════════════════════════════
    public function show(PurchaseOrder $order)
    {
        abort_if($order->branch_id !== auth()->user()->branch_id, 403);

        $order->load(['supplier', 'user', 'items.product', 'branch']);

        activity_log('purchase_order_viewed', "Commande #{$order->id} consultée");

        return view('purchases.show', compact('order'));
    }

    // ═══════════════════════════════════════════════════════════════
    // AJAX ITEMS
    // ═══════════════════════════════════════════════════════════════
    public function getItems(PurchaseOrder $order)
    {
        abort_if($order->branch_id !== auth()->user()->branch_id, 403);

        $order->load('items.product');

        activity_log('purchase_order_items_viewed', "Items commande #{$order->id}");

        return response()->json($order->items);
    }
}