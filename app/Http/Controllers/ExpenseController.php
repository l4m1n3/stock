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
use App\Exports\ExpensesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinanceExport;

class ExpenseController extends Controller
{

    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        // ── Période sélectionnée ────────────────────────────────────────────
        $period = $request->get('period', 'month');

        [$start, $end, $prevStart, $prevEnd] = match ($period) {
            'day'     => [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay(),
                Carbon::now()->subDay()->startOfDay(),
                Carbon::now()->subDay()->endOfDay(),
            ],
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
            ->where('branch_id', $branchId)                          
            ->sum('total_amount');

       // CA encaissé en espèces sur la période
        $totalRevenueEspece = Invoice::with('sale')
            ->whereHas('sale', fn($q) => $q
                ->whereBetween('sold_at', [$start, $end])
                ->where('branch_id', $branchId)
                ->where('payment_method', 'cash'))
            ->sum('total_amount');

        $prevRevenue   = Invoice::whereBetween('issued_at', [$prevStart, $prevEnd])
            ->where('branch_id', $branchId)                          
            ->sum('total_amount');

        $revenueGrowth = $prevRevenue > 0
            ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 0;

        $expenseCount  = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->count();

        $salesCount = Sale::whereBetween('sold_at', [$start, $end])
            ->where('branch_id', $branchId)                          // ✅ ajouté
            ->count();

        $avgTicket = $salesCount > 0 ? round($totalRevenue / $salesCount) : 0;

        // ── Graphique CA vs Dépenses ────────────────────────────────────────
        if ($period === 'day') {
            $hours = collect(range(0, 23))->map(fn($h) => $start->copy()->addHours($h));
            $chartLabels   = $hours->map(fn($h) => $h->format('H\h'))->toArray();
            $chartRevenue  = $hours->map(
                fn($h) => (float) Invoice::whereBetween('issued_at', [
                    $h, $h->copy()->endOfHour(),
                ])->where('branch_id', $branchId)->sum('total_amount')
            )->toArray();
            $chartExpenses = $hours->map(
                fn($h) => (float) Expense::whereBetween('expense_date', [
                    $h->toDateTimeString(), $h->copy()->endOfHour()->toDateTimeString(),
                ])->where('branch_id', $branchId)->sum('amount')
            )->toArray();
        } elseif ($period === 'week') {
       
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

        $payLabels = ['cash' => 'Espèces', 'amana' => 'Amana', 'nita' => 'Nita', 'western_union' => 'Western Union', 'moneygram' => 'MoneyGram', 'wave' => 'Wave'];
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


        // depenses effectuees dans la caisse
        $totalPaidcaisse = Expense::where('payment_method', 'cash')
            ->where('status', 'payé')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->sum('amount');
        //montant restant dans la caisse après paiement des dépenses = CA(paiement en espece uniquement) - dépenses payées
        // $cashAvailable     = $totalRevenueEspece - $totalPaidcaisse;
                    $cashAvailable = max(0, $totalRevenueEspece - $totalPaidcaisse);
            $cashBalance = [
                'entrees'    => $totalRevenueEspece,
                'sorties'    => $totalPaidcaisse,
                'solde'      => $cashAvailable,
                'alerte'     => $cashAvailable < 50000,  // seuil à adapter
            ]; 
        // ── Liste dépenses paginée ────────────────────────────────────────────
        $expenses = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)                          // ✅ ajouté
            ->orderByDesc('expense_date')
            ->paginate(10)
            ->appends(['period' => $period]);

        $totalExpenses = Expense::where('status', 'payé') // 🔥 uniquement les dépenses non payées
            ->whereBetween('expense_date', [$start, $end])
            ->where('branch_id', $branchId)
            ->sum('amount');
        $totalPaidExpenses = Expense::where('status', 'payé')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->sum('amount');



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
            'period',
            'cashAvailable',
            'cashBalance',
            'totalPaidcaisse'
        ));
    }
    //     public function index(Request $request)
    // {
    //     $branchId = auth()->user()->branch_id;

    //     $period = $request->get('period', 'month');

    //     [$start, $end, $prevStart, $prevEnd] = match ($period) {
    //         'week'    => [now()->startOfWeek(), now()->endOfWeek(), now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
    //         'quarter' => [now()->firstOfQuarter(), now()->lastOfQuarter(), now()->subQuarter()->firstOfQuarter(), now()->subQuarter()->lastOfQuarter()],
    //         'year'    => [now()->startOfYear(), now()->endOfYear(), now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
    //         default   => [now()->startOfMonth(), now()->endOfMonth(), now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
    //     };

    //     // ── REVENUS ─────────────────────────────
    //     $totalRevenue = Invoice::whereBetween('issued_at', [$start, $end])
    //         ->where('branch_id', $branchId)
    //         ->sum('total_amount');

    //     $prevRevenue = Invoice::whereBetween('issued_at', [$prevStart, $prevEnd])
    //         ->where('branch_id', $branchId)
    //         ->sum('total_amount');

    //     $revenueGrowth = $prevRevenue > 0
    //         ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
    //         : 0;

    //     // ── DÉPENSES ────────────────────────────
    //     $totalExpenses = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
    //         ->where('branch_id', $branchId)
    //         ->sum('amount');

    //     $totalPaidExpenses = Expense::where('status', 'payé')
    //         ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
    //         ->where('branch_id', $branchId)
    //         ->sum('amount');

    //     $pendingExpenses = Expense::where('status', 'pending')
    //         ->where('branch_id', $branchId)
    //         ->sum('amount');

    //     $expenseCount = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
    //         ->where('branch_id', $branchId)
    //         ->count();

    //     // ── VENTES ──────────────────────────────
    //     $salesCount = Sale::whereBetween('sold_at', [$start, $end])
    //         ->where('branch_id', $branchId)
    //         ->count();

    //     $avgTicket = $salesCount > 0 ? round($totalRevenue / $salesCount) : 0;

    //     // ── KPI CLÉS ────────────────────────────
    //     $realProfit        = $totalRevenue - $totalPaidExpenses;
    //     $theoreticalProfit = $totalRevenue - $totalExpenses;
    //     $cashAvailable     = $totalRevenue - $totalPaidExpenses; // 🔥 KPI principal

    //     // ── GRAPHIQUE (CASH UNIQUEMENT) ─────────
    //     $days = collect(range(0, 6))->map(fn($d) => $start->copy()->addDays($d));
    //     $chartLabels = $days->map(fn($d) => $d->format('D'))->toArray();

    //     $chartRevenue = $days->map(fn($d) =>
    //         (float) Invoice::whereDate('issued_at', $d)
    //             ->where('branch_id', $branchId)
    //             ->sum('total_amount')
    //     )->toArray();

    //     $chartExpenses = $days->map(fn($d) =>
    //         (float) Expense::where('status', 'paid')
    //             ->whereDate('expense_date', $d)
    //             ->where('branch_id', $branchId)
    //             ->sum('amount')
    //     )->toArray();

    //     // ── LISTE ───────────────────────────────
    //     $expenses = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
    //         ->where('branch_id', $branchId)
    //         ->latest()
    //         ->paginate(10)
    //         ->appends(['period' => $period]);
    // $expensesByType = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
    //     ->where('branch_id', $branchId)
    //     ->select('type', DB::raw('SUM(amount) as total'))
    //     ->groupBy('type')
    //     ->orderByDesc('total')
    //     ->get() ?? collect();
    //     activity_log('finances_viewed', "Période $period | CA $totalRevenue | Cash $cashAvailable");

    //     return view('finances.index', compact(
    //         'totalRevenue',
    //         'revenueGrowth',
    //         'totalExpenses',
    //         'totalPaidExpenses',
    //         'pendingExpenses',
    //         'expenseCount',
    //         'salesCount',
    //         'avgTicket',
    //         'realProfit',
    //         'theoreticalProfit',
    //         'cashAvailable',
    //         'chartLabels',
    //         'chartRevenue',
    //         'chartExpenses',
    //         'expenses',
    //         'period',
    //         'expensesByType'
    //     ));
    // }
    public function storeExpense(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'type'         => 'required|in:livraison,materiel,salaire,autre,commande',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,amana,nita,western_union,moneygram,wave',
            'status'       => 'required|in:payé,non payé',
        ]);
        $branchId = auth()->user()->branch_id;
        // $payment_method = $request->get('payment_method', 'cash'); // par défaut 'cash' si non spécifié
        Expense::create($request->only('title', 'amount', 'type', 'expense_date', 'payment_method', 'status') + ['branch_id' => $branchId]);
        activity_log('expense_created', "Dépense créée : {$request->title}");
        return back()->with('success', 'Dépense enregistrée avec succès.');
    }
    public function updateExpense(Request $request, Expense $expense)
    {
        $branchId = auth()->user()->branch_id;

        // 🔒 sécurité : empêcher modification d’une autre branche
        abort_if($expense->branch_id !== $branchId, 403);

        $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'type'         => 'required|in:livraison,materiel,salaire,autre',
            'status'       => 'required|in:payé,non payé',
            'expense_date' => 'nullable|date',
                'payment_method' => 'required|in:cash,amana,nita,western_union,moneygram,wave',
        ]);

        $expense->update([
            'title'        => $request->title,
            'amount'       => $request->amount,
            'type'         => $request->type,
            'status'       => $request->status,
            'expense_date' => $request->expense_date ?? $expense->expense_date,
        ]);

        activity_log('expense_updated', "Dépense modifiée : {$expense->title}");

        return back()->with('success', 'Dépense mise à jour avec succès.');
    }
    public function destroyExpense(Expense $expense)
    {
        $expense->delete();
        activity_log('expense_deleted', "Dépense supprimée : {$expense->title}");
        return back()->with('success', 'Dépense supprimée.');
    }
    public function export(Request $request)
    {
        $period = $request->get('period', 'month');

        [$start, $end] = match ($period) {
            'week'    => [now()->startOfWeek(), now()->endOfWeek()],
            'quarter' => [now()->firstOfQuarter(), now()->lastOfQuarter()],
            'year'    => [now()->startOfYear(), now()->endOfYear()],
            default   => [now()->startOfMonth(), now()->endOfMonth()],
        };

        return Excel::download(
            new ExpensesExport($start->toDateString(), $end->toDateString()),
            'depenses_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportFinance(Request $request)
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

        $payLabels = ['cash' => 'Espèces', 'amana' => 'Amana', 'nita' => 'Nita', 'western_union' => 'Western Union', 'moneygram' => 'MoneyGram', 'wave' => 'Wave'];
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
        // ⚠️ tu réutilises EXACTEMENT ta logique existante
        // (celle que tu as déjà écrite dans index)

        $data = [
            'period'        => $request->get('period', 'month'),
            'totalRevenue'  => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'profit'        => $totalRevenue - $totalExpenses,
            'salesCount'    => $salesCount,
            // 'avgTicket'     => $avgTicket,
            'chartLabels'   => $chartLabels,
            'chartRevenue'  => $chartRevenue,
            'chartExpenses' => $chartExpenses,
            'expenses'      => Expense::where('branch_id', auth()->user()->branch_id)->get(),
            'paymentData'   => $paymentData,
        ];

        return Excel::download(
            new FinanceExport($data),
            'rapport_financier.xlsx'
        );
    }
}
