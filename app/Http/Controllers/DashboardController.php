<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Expense;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        // ── Ventes du jour ───────────────────────────────────────────────────
        $todaySales = Sale::where('branch_id', $branchId)
            ->whereDate('sold_at', today())
            ->sum('total_amount');

        // Ventes hier (pour le % de variation)
        $yesterdaySales = Sale::where('branch_id', $branchId)
            ->whereDate('sold_at', today()->subDay())
            ->sum('total_amount');

        $todayVariation = $yesterdaySales > 0
            ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1)
            : null;

        // ── Chiffre du mois ──────────────────────────────────────────────────
        $monthSales = Sale::where('branch_id', $branchId)
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('total_amount');

        // ── Commandes en attente ─────────────────────────────────────────────
        $pendingOrders = PurchaseOrder::where('branch_id', $branchId)
            ->where('status', 'pending')
            ->count();

        // ── Articles en alerte stock ─────────────────────────────────────────
        $criticalProducts = Product::where('branch_id', $branchId)
            ->whereColumn('stock_quantity', '<=', 'alert_threshold')
            ->orderBy('stock_quantity')
            ->get();

        // ── Ventes des 7 derniers jours (graphique) ──────────────────────────
        $last7Days = Sale::select(
                DB::raw('DATE(sold_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->where('branch_id', $branchId)
            ->where('sold_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $salesChart[] = [
                'label' => now()->subDays($i)->locale('fr')->isoFormat('ddd D'),
                'total' => (float) ($last7Days[$d]->total ?? 0),
            ];
        }

        // ── Dernières ventes ─────────────────────────────────────────────────
        $latestSales = Sale::with('user')
            ->where('branch_id', $branchId)
            ->latest('sold_at')
            ->take(5)
            ->get();

        // ── Dépenses du mois ─────────────────────────────────────────────────
        $monthExpenses = Expense::where('branch_id', $branchId)
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        $monthProfit = $monthSales - $monthExpenses;

        return view('dashboard', compact(
            'todaySales',
            'todayVariation',
            'yesterdaySales',
            'monthSales',
            'monthExpenses',
            'monthProfit',
            'pendingOrders',
            'criticalProducts',
            'salesChart',
            'latestSales'
        ));
    }
}