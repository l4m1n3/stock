<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\SaleItem;
use App\Models\SaleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
   
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        // ── Période sélectionnée ────────────────────────────────────────────
        $period = $request->get('period', 'month');

        [$start, $end, $prevStart, $prevEnd] = match ($period) {
            'week'    => [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
                Carbon::now()->subWeek()->startOfWeek(),
                Carbon::now()->subWeek()->endOfWeek(),
            ],
            'quarter' => [
                Carbon::now()->firstOfQuarter(),
                Carbon::now()->lastOfQuarter(),
                Carbon::now()->subQuarter()->firstOfQuarter(),
                Carbon::now()->subQuarter()->lastOfQuarter(),
            ],
            'year'    => [
                Carbon::now()->startOfYear(),
                Carbon::now()->endOfYear(),
                Carbon::now()->subYear()->startOfYear(),
                Carbon::now()->subYear()->endOfYear(),
            ],
            default   => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ],
        };

        // ── KPIs ─────────────────────────────────────────────────────────────
        $totalRevenue  = Invoice::whereBetween('issued_at', [$start, $end])
            ->where('branch_id', $branchId)                          // ✅ ajouté
            ->sum('total_amount');

        $prevRevenue   = Invoice::whereBetween('issued_at', [$prevStart, $prevEnd])
            ->where('branch_id', $branchId)                          // ✅ ajouté
            ->sum('total_amount');

        $revenueGrowth = $prevRevenue > 0
            ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 0;

        $totalExpenses = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->sum('amount');

        $expenseCount  = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->count();

        $salesCount = Sale::whereBetween('sold_at', [$start, $end])
            ->where('branch_id', $branchId)                          // ✅ ajouté
            ->count();

        $avgTicket = $salesCount > 0 ? round($totalRevenue / $salesCount) : 0;

        // ── Graphique CA vs Dépenses ────────────────────────────────────────
        if ($period === 'week') {
            $days = collect(range(0, 6))->map(fn($d) => $start->copy()->addDays($d));
            $chartLabels   = $days->map(fn($d) => $d->format('D'))->toArray();
            $chartRevenue  = $days->map(
                fn($d) => (float) Invoice::whereDate('issued_at', $d->toDateString())
                    ->where('branch_id', $branchId)                  // ✅ ajouté
                    ->sum('total_amount')
            )->toArray();
            $chartExpenses = $days->map(
                fn($d) => (float) Expense::whereDate('expense_date', $d->toDateString())
                    ->where('branch_id', $branchId)                  // ✅ ajouté
                    ->sum('amount')
            )->toArray();
        } elseif ($period === 'month') {
            $weeks = collect(range(1, 5));
            $chartLabels   = $weeks->map(fn($w) => "Sem. $w")->toArray();
            $chartRevenue  = $weeks->map(fn($w) => (float) Invoice::whereBetween('issued_at', [
                $start->copy()->addWeeks($w - 1),
                $start->copy()->addWeeks($w)->subSecond(),
            ])->where('branch_id', $branchId)->sum('total_amount'))->toArray(); // ✅ ajouté

            $chartExpenses = $weeks->map(fn($w) => (float) Expense::whereBetween('expense_date', [
                $start->copy()->addWeeks($w - 1)->toDateString(),
                $start->copy()->addWeeks($w)->subDay()->toDateString(),
            ])->where('branch_id', $branchId)->sum('amount'))->toArray();       // ✅ ajouté

        } else {
            $months = $period === 'quarter'
                ? collect(range(0, 2))->map(fn($m) => $start->copy()->addMonths($m))
                : collect(range(0, 11))->map(fn($m) => Carbon::now()->startOfYear()->addMonths($m));

            $chartLabels   = $months->map(fn($m) => $m->translatedFormat('M'))->toArray();
            $chartRevenue  = $months->map(
                fn($m) => (float) Invoice::whereYear('issued_at', $m->year)
                    ->whereMonth('issued_at', $m->month)
                    ->where('branch_id', $branchId)                  // ✅ ajouté
                    ->sum('total_amount')
            )->toArray();
            $chartExpenses = $months->map(
                fn($m) => (float) Expense::whereYear('expense_date', $m->year)
                    ->whereMonth('expense_date', $m->month)
                    ->where('branch_id', $branchId)                  // ✅ ajouté
                    ->sum('amount')
            )->toArray();
        }

        // ── Dépenses par catégorie ────────────────────────────────────────────
        $expensesByType = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)                          // déjà présent ✅
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        $expenseDonutData = [
            'labels' => $expensesByType->pluck('type')
                ->map(fn($t) => match ($t) {
                    'livraison' => 'Livraison',
                    'materiel'  => 'Matériel',
                    'salaire'   => 'Salaires',
                    default     => 'Autre',
                })->toArray(),
            'values' => $expensesByType->pluck('total')->map(fn($v) => (float) $v)->toArray(),
        ];

        // ── Modes de paiement ─────────────────────────────────────────────────
        $paymentMethods = Sale::whereBetween('sold_at', [$start, $end])
            ->where('branch_id', $branchId)                          // ✅ ajouté
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $payLabels = ['cash' => 'Espèces', 'amana' => 'Amana', 'nita' => 'Nita'];
        $paymentData = [
            'labels' => $paymentMethods->pluck('payment_method')
                ->map(fn($m) => $payLabels[$m] ?? $m)->toArray(),
            'values' => $paymentMethods->pluck('total')->map(fn($v) => (float) $v)->toArray(),
        ];

        // ── Top produits vendus ───────────────────────────────────────────────
        $topProducts = SaleItem::with('product')
            ->whereHas('sale', fn($q) => $q->whereBetween('sold_at', [$start, $end])
                ->where('branch_id', $branchId))                     // ✅ ajouté
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->take(4)
            ->get();

        // ── Top services vendus ───────────────────────────────────────────────
        $topServices = SaleService::with('service')
            ->whereHas('sale', fn($q) => $q->whereBetween('sold_at', [$start, $end])
                ->where('branch_id', $branchId))                     // ✅ ajouté
            ->select(
                'service_id',
                DB::raw('COUNT(*) as total_qty'),
                DB::raw('SUM(price) as total_revenue')
            )
            ->groupBy('service_id')
            ->orderByDesc('total_revenue')
            ->take(4)
            ->get();

        // ── Liste dépenses paginée ────────────────────────────────────────────
        $expenses = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)                          // ✅ ajouté
            ->orderByDesc('expense_date')
            ->paginate(10)
            ->appends(['period' => $period]);
   activity_log('finances_viewed', "Consultation finances : Période $period, CA $totalRevenue, Dépenses $totalExpenses");
        return view('finances.index', compact(
            'totalRevenue',
            'prevRevenue',
            'revenueGrowth',
            'totalExpenses',
            'expenseCount',
            'salesCount',
            'avgTicket',
            'chartLabels',
            'chartRevenue',
            'chartExpenses',
            'expensesByType',
            'expenseDonutData',
            'paymentData',
            'topProducts',
            'topServices',
            'expenses',
            'period'
        ));
    }
    public function storeExpense(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'type'         => 'required|in:livraison,materiel,salaire,autre',
            'expense_date' => 'required|date',
        ]);
        $branchId = auth()->user()->branch_id;
        Expense::create($request->only('title', 'amount', 'type', 'expense_date') + ['branch_id' => $branchId]);
        activity_log('expense_created', "Dépense créée : {$request->title}");
        return back()->with('success', 'Dépense enregistrée avec succès.');
    }

    public function destroyExpense(Expense $expense)
    {
        $expense->delete();
        activity_log('expense_deleted', "Dépense supprimée : {$expense->title}");
        return back()->with('success', 'Dépense supprimée.');
    }
}
