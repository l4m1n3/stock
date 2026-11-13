<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\SaleItem;
use App\Models\SaleService;
use App\Models\CashTransfer;
use App\Models\CashRegister;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\ExpensesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinanceExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{

    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $isStaff  = auth()->user()->role === 'staff';
        $userId = auth()->user()->id;
        // ── Période sélectionnée ──────────────────────────────────────────────
        if ($isStaff) {
            $period = 'day';
        } else {
            $period = $request->get('period', 'month');
        }
        $initialDate = Carbon::now();
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        if ($period === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();

            $diffInDays = $start->diffInDays($end) + 1;
            $prevEnd    = $start->copy()->subDay()->endOfDay();
            $prevStart  = $prevEnd->copy()->subDays($diffInDays - 1)->startOfDay();
        } else {
            if ($period === 'custom') {
                $period = 'month';
            }

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
        }

        // ── KPIs ──────────────────────────────────────────────────────────────
        $totalRevenue = Invoice::whereBetween('issued_at', [$start, $end])
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->whereHas('sale', fn($q2) => $q2->where('user_id', $userId)))
            ->sum('total_amount');

        $totalRevenueEspece = Invoice::with('sale')
            ->whereHas('sale', fn($q) => $q
                ->whereBetween('sold_at', [$start, $end])
                ->where('branch_id', $branchId)
                ->where('payment_method', 'cash'))
            ->sum('total_amount');

        $prevRevenue = Invoice::whereBetween('issued_at', [$prevStart, $prevEnd])
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->whereHas('sale', fn($q2) => $q2->where('user_id', $userId)))
            ->sum('total_amount');
        $revenueGrowth = $prevRevenue > 0
            ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 0;

        $salesCount = Sale::whereBetween('sold_at', [$start, $end])
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->where('user_id', $userId))
            ->count();
        $avgTicket = $salesCount > 0 ? round($totalRevenue / $salesCount) : 0;

        // ── Graphique CA vs Dépenses ──────────────────────────────────────────
        if ($period === 'day') {
            $hours = collect(range(0, 23))->map(fn($h) => $start->copy()->addHours($h));
            $chartLabels   = $hours->map(fn($h) => $h->format('H\h'))->toArray();
            $chartRevenue  = $hours->map(
                fn($h) => (float) Invoice::whereBetween('issued_at', [$h, $h->copy()->endOfHour()])
                    ->where('branch_id', $branchId)
                    ->when($isStaff, fn($q) => $q->whereHas('sale', fn($q2) => $q2->where('user_id', $userId)))
                    ->sum('total_amount')
            )->toArray();
            $chartExpenses = $hours->map(
                fn($h) => (float) Expense::whereBetween('expense_date', [
                    $h->toDateTimeString(),
                    $h->copy()->endOfHour()->toDateTimeString(),
                ])->where('branch_id', $branchId)
                    ->when($isStaff, fn($q) => $q->where('user_id', $userId))
                    ->sum('amount')
            )->toArray();
        } elseif ($period === 'week') {
            $days = collect(range(0, 6))->map(fn($d) => $start->copy()->addDays($d));
            $chartLabels   = $days->map(fn($d) => $d->format('D'))->toArray();
            $chartRevenue  = $days->map(
                fn($d) => (float) Invoice::whereDate('issued_at', $d->toDateString())
                    ->where('branch_id', $branchId)
                    ->when($isStaff, fn($q) => $q->whereHas('sale', fn($q2) => $q2->where('user_id', $userId)))
                    ->sum('total_amount')
            )->toArray();
            $chartExpenses = $days->map(
                fn($d) => (float) Expense::whereDate('expense_date', $d->toDateString())
                    ->where('branch_id', $branchId)
                    ->when($isStaff, fn($q) => $q->where('user_id', $userId))
                    ->sum('amount')
            )->toArray();
        } elseif ($period === 'month') {
            $weeks = collect(range(1, 5));
            $chartLabels   = $weeks->map(fn($w) => "Sem. $w")->toArray();
            $chartRevenue  = $weeks->map(fn($w) => (float) Invoice::whereBetween('issued_at', [
                $start->copy()->addWeeks($w - 1),
                $start->copy()->addWeeks($w)->subSecond(),
            ])->where('branch_id', $branchId)
                ->when($isStaff, fn($q) => $q->whereHas('sale', fn($q2) => $q2->where('user_id', $userId)))
                ->sum('total_amount'))->toArray();
            $chartExpenses = $weeks->map(fn($w) => (float) Expense::whereBetween('expense_date', [
                $start->copy()->addWeeks($w - 1)->toDateString(),
                $start->copy()->addWeeks($w)->subDay()->toDateString(),
            ])->where('branch_id', $branchId)
                ->when($isStaff, fn($q) => $q->where('user_id', $userId))
                ->sum('amount'))->toArray();
        } elseif ($period === 'custom') {
            $totalDays = $start->diffInDays($end) + 1;

            if ($totalDays <= 31) {
                $days = collect(range(0, $totalDays - 1))->map(fn($d) => $start->copy()->addDays($d));
                $chartLabels   = $days->map(fn($d) => $d->format('d/m'))->toArray();
                $chartRevenue  = $days->map(
                    fn($d) => (float) Invoice::whereDate('issued_at', $d->toDateString())
                        ->where('branch_id', $branchId)
                        ->when($isStaff, fn($q) => $q->whereHas('sale', fn($q2) => $q2->where('user_id', $userId)))
                        ->sum('total_amount')
                )->toArray();
                $chartExpenses = $days->map(
                    fn($d) => (float) Expense::whereDate('expense_date', $d->toDateString())
                        ->where('branch_id', $branchId)
                        ->when($isStaff, fn($q) => $q->where('user_id', $userId))
                        ->sum('amount')
                )->toArray();
            } else {
                $months = collect();
                $cursor = $start->copy()->startOfMonth();
                while ($cursor->lte($end)) {
                    $months->push($cursor->copy());
                    $cursor->addMonth();
                }

                $chartLabels   = $months->map(fn($m) => $m->translatedFormat('M Y'))->toArray();
                $chartRevenue  = $months->map(
                    fn($m) => (float) Invoice::whereYear('issued_at', $m->year)
                        ->whereMonth('issued_at', $m->month)
                        ->where('branch_id', $branchId)
                        ->when($isStaff, fn($q) => $q->whereHas('sale', fn($q2) => $q2->where('user_id', $userId)))
                        ->sum('total_amount')
                )->toArray();
                $chartExpenses = $months->map(
                    fn($m) => (float) Expense::whereYear('expense_date', $m->year)
                        ->whereMonth('expense_date', $m->month)
                        ->where('branch_id', $branchId)
                        ->when($isStaff, fn($q) => $q->where('user_id', $userId))
                        ->sum('amount')
                )->toArray();
            }
        } else {
            $months = $period === 'quarter'
                ? collect(range(0, 2))->map(fn($m) => $start->copy()->addMonths($m))
                : collect(range(0, 11))->map(fn($m) => Carbon::now()->startOfYear()->addMonths($m));

            $chartLabels   = $months->map(fn($m) => $m->translatedFormat('M'))->toArray();
            $chartRevenue  = $months->map(
                fn($m) => (float) Invoice::whereYear('issued_at', $m->year)
                    ->whereMonth('issued_at', $m->month)
                    ->where('branch_id', $branchId)
                    ->when($isStaff, fn($q) => $q->whereHas('sale', fn($q2) => $q2->where('user_id', $userId)))
                    ->sum('total_amount')
            )->toArray();
            $chartExpenses = $months->map(
                fn($m) => (float) Expense::whereYear('expense_date', $m->year)
                    ->whereMonth('expense_date', $m->month)
                    ->where('branch_id', $branchId)
                    ->when($isStaff, fn($q) => $q->where('user_id', $userId))
                    ->sum('amount')
            )->toArray();
        }
        // ── Dépenses par catégorie ────────────────────────────────────────────
        $expensesByType = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->where('user_id', $userId))
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
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->where('user_id', $userId))
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();
        $payLabels = [
            'cash' => 'Espèces',
            'amana' => 'Amana',
            'nita' => 'Nita',
            'western_union' => 'Western Union',
            'moneygram' => 'MoneyGram',
            'wave' => 'Wave'
        ];
        $paymentData = [
            'labels' => $paymentMethods->pluck('payment_method')
                ->map(fn($m) => $payLabels[$m] ?? $m)->toArray(),
            'values' => $paymentMethods->pluck('total')->map(fn($v) => (float) $v)->toArray(),
        ];

        // ── Top produits / services ───────────────────────────────────────────
        $topProducts = SaleItem::with('product')
            ->whereHas('sale', fn($q) => $q->whereBetween('sold_at', [$start, $end])
                ->where('branch_id', $branchId)
                ->when($isStaff, fn($q2) => $q2->where('user_id', $userId)))
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->take(4)->get();

        $topServices = SaleService::with('service')
            ->whereHas('sale', fn($q) => $q->whereBetween('sold_at', [$start, $end])
                ->where('branch_id', $branchId)
                ->when($isStaff, fn($q2) => $q2->where('user_id', $userId)))
            ->select('service_id', DB::raw('COUNT(*) as total_qty'), DB::raw('SUM(price) as total_revenue'))
            ->groupBy('service_id')
            ->orderByDesc('total_revenue')
            ->take(4)->get();

        // ── Cash caisse ───────────────────────────────────────────────────────

        $totalPaidcaisse = Expense::where('payment_method', 'cash')
            ->where('status', 'payé')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->where('user_id', $userId))
            ->sum('amount');

        $cashTransferredOut = CashTransfer::where('from_method', 'cash')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $cashTransferredIn = CashTransfer::where('to_method', 'cash')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        // ─────────────────────────────────────────────────────────────────
        // ✅ Snapshot journalier ré-activé. Il est désormais fiable car
        // cashMovementsBetween() s'ancre sur created_at (immuable) et non
        // plus sur expense_date (éditable). Backdater expense_date ne peut
        // donc plus jamais "invisibiliser" une dépense pour le solde de
        // caisse — seul reste l'angle mort d'un changement de statut/
        // montant/payment_method a posteriori, géré par invalidation
        // ciblée dans updateExpense()/destroyExpense() (voir plus bas).
        // ─────────────────────────────────────────────────────────────────
        $todayOpeningCash = $this->getTodayCashOpeningBalance($branchId);
        $todayCashMovements = $this->cashMovementsBetween(
            $branchId,
            Carbon::today()->toDateString(),
            Carbon::today()->toDateString()
        );

        $cashAvailable = max(0, $todayOpeningCash + $todayCashMovements);

        $cashBalance = [
            'entrees' => $totalRevenueEspece,
            'sorties' => $totalPaidcaisse,
            'transferts' => $cashTransferredOut,
            'solde' => $cashAvailable,
            'alerte' => $cashAvailable < 50000,
        ];

        // Solde AMANA
        $amanaSales = Sale::where('payment_method', 'amana')
            ->whereBetween('sold_at', [$start, $end])
            ->where('branch_id', $branchId)
            ->sum('total_amount');

        $amanaTransferredIn = CashTransfer::where('to_method', 'amana')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $amanaTransferredOut = CashTransfer::where('from_method', 'amana')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $amanaBalance = (
            $amanaSales
            + $amanaTransferredIn
            - $amanaTransferredOut
        );

        // Solde NITA
        $nitaSales = Sale::where('payment_method', 'nita')
            ->whereBetween('sold_at', [$start, $end])
            ->where('branch_id', $branchId)
            ->sum('total_amount');

        $nitaTransferredIn = CashTransfer::where('to_method', 'nita')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $nitaTransferredOut = CashTransfer::where('from_method', 'nita')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $nitaBalance = (
            $nitaSales
            + $nitaTransferredIn
            - $nitaTransferredOut
        );

        // ── Liste dépenses paginée ────────────────────────────────────────────
        $expenseListStart = $isStaff
            ? Carbon::now()->subDays(30)->startOfDay()
            : $start;

        $expenseListEnd = $isStaff
            ? Carbon::now()->endOfDay()
            : $end;

        $expensesQuery = Expense::with('user')
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->where('user_id', $userId))
            ->whereBetween('expense_date', [$expenseListStart, $expenseListEnd])
            ->orderByDesc('expense_date');

        $expenses = $expensesQuery
            ->paginate(10, ['*'], 'expenses_page')
            ->appends(request()->query());

        $expenseCount = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->where('user_id', $userId))
            ->count();

        $totalExpenses = Expense::where('status', 'payé')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->where('user_id', $userId))
            ->sum('amount');

        $depenseVeille = Expense::whereDate('expense_date', Carbon::yesterday())
            ->where('branch_id', $branchId)
            ->when($isStaff, fn($q) => $q->where('user_id', $userId))
            ->sum('amount');

        // ── Historique des transferts ───────────────────────────────────────
        $transfersQuery = CashTransfer::with('user')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at');

        $transfers = $transfersQuery
            ->paginate(10, ['*'], 'transfers_page')
            ->appends(request()->query());

        $totalPaidExpenses = $totalExpenses;
        $profit = $totalRevenue - $totalExpenses;
        activity_log('finances_viewed', "Consultation finances : Période $period, CA $totalRevenue, Dépenses $totalExpenses");

        return view('finances.index', compact(
            'isStaff',
            'totalRevenue',
            'prevRevenue',
            'revenueGrowth',
            'totalExpenses',
            'totalPaidExpenses',
            'expenseCount',
            'salesCount',
            'profit',
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
            'transfers',
            'period',
            'cashAvailable',
            'cashBalance',
            'totalPaidcaisse',
            'amanaBalance',
            'nitaBalance',
            'startDate',
            'endDate',
            'depenseVeille'
        ));
    }

    public function storeExpense(Request $request)
    {
        try {
        $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'type'         => 'required|in:livraison,materiel,salaire,autre,commande',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,amana,nita,western_union,moneygram,wave',
            'status'       => 'required|in:payé,non payé',
        ]);
        $branchId = auth()->user()->branch_id;
        $expense = Expense::create($request->only('title', 'amount', 'type', 'expense_date', 'payment_method', 'status') + [
            'branch_id' => $branchId,
            'user_id'   => Auth::id(),
        ]);

        // Invalide à partir du jour de created_at. En pratique created_at
        // = maintenant, donc ça ne touche que le snapshot du jour courant
        // (déjà recalculé à chaque accès) — gardé par cohérence/robustesse.
        if ($expense->payment_method === 'cash' && $expense->status === 'payé') {
            $this->invalidateCashSnapshotsFrom($branchId, $expense->created_at->toDateString());
        }

        activity_log('expense_created', "Dépense créée : {$request->title}");
        return back()->with('success', 'Dépense enregistrée avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement de la dépense : " . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'enregistrement de la dépense.');
        }
    }

    public function updateExpense(Request $request, Expense $expense)
    {
        $branchId = auth()->user()->branch_id;
        $user = auth()->user();
        abort_if($expense->branch_id !== $branchId, 403);
        abort_if($user->role === 'staff' && $expense->user_id !== $user->id, 403);

        $request->validate([
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'type'         => 'required|in:livraison,materiel,salaire,autre',
            'status'       => 'required|in:payé,non payé',
            'expense_date' => 'nullable|date',
            'payment_method' => 'required|in:cash,amana,nita,western_union,moneygram,wave',
        ]);

        $previousStatus = $expense->status;

        // Capturé AVANT update.
        $wasCashPaid   = $expense->payment_method === 'cash' && $expense->status === 'payé';
        $wasAmount     = (float) $expense->amount;
        $createdAtDate = $expense->created_at->toDateString();

        $expense->update([
            'title'          => $request->title,
            'amount'         => $request->amount,
            'type'           => $request->type,
            'status'         => $request->status,
            'expense_date'   => $request->expense_date ?? $expense->expense_date,
            'payment_method' => $request->payment_method,
            'user_id'        => Auth::id(),
        ]);

        $isCashPaid = $request->payment_method === 'cash' && $request->status === 'payé';

        // ✅ Invalidation ciblée : uniquement si l'effet réel sur le cash
        // change (bascule payé/non payé, cash/autre méthode, ou montant
        // différent sur une dépense cash+payée). expense_date n'a AUCUN
        // impact sur le solde de caisse — c'est tout l'intérêt de l'avoir
        // découplé. On invalide à partir de created_at (immuable).
        if ($wasCashPaid !== $isCashPaid || ($isCashPaid && $wasAmount !== (float) $request->amount)) {
            $this->invalidateCashSnapshotsFrom($branchId, $createdAtDate);
        }

        if ($previousStatus !== $request->status) {
            activity_log(
                'expense_status_changed',
                "Statut de « {$expense->title} » changé de {$previousStatus} à {$request->status} par " . $user->name
            );
        }

        activity_log('expense_updated', "Dépense modifiée : {$expense->title}");

        return back()->with('success', 'Dépense mise à jour avec succès.');
    }

    public function destroyExpense(Expense $expense)
    {
        $user = auth()->user();

        abort_if($expense->branch_id !== $user->branch_id, 403);
        abort_if($user->role === 'staff' && $expense->user_id !== $user->id, 403);

        if ($expense->payment_method === 'cash' && $expense->status === 'payé') {
            $this->invalidateCashSnapshotsFrom($expense->branch_id, $expense->created_at->toDateString());
        }

        $expense->delete();
        activity_log('expense_deleted', "Dépense supprimée : {$expense->title}");
        return back()->with('success', 'Dépense supprimée.');
    }

    public function export(Request $request)
    {
        $period    = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        if ($period === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();
        } else {
            [$start, $end] = match ($period) {
                'week'    => [now()->startOfWeek(), now()->endOfWeek()],
                'quarter' => [now()->firstOfQuarter(), now()->lastOfQuarter()],
                'year'    => [now()->startOfYear(), now()->endOfYear()],
                default   => [now()->startOfMonth(), now()->endOfMonth()],
            };
        }

        return Excel::download(
            new ExpensesExport($start->toDateString(), $end->toDateString()),
            'depenses_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportFinance(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $period    = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        if ($period === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();

            $diffInDays = $start->diffInDays($end) + 1;
            $prevEnd    = $start->copy()->subDay()->endOfDay();
            $prevStart  = $prevEnd->copy()->subDays($diffInDays - 1)->startOfDay();
        } else {
            if ($period === 'custom') {
                $period = 'month';
            }

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
        }

        $totalRevenue  = Invoice::whereBetween('issued_at', [$start, $end])
            ->where('branch_id', $branchId)
            ->sum('total_amount');

        $prevRevenue   = Invoice::whereBetween('issued_at', [$prevStart, $prevEnd])
            ->where('branch_id', $branchId)
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
            ->where('branch_id', $branchId)
            ->count();

        $avgTicket = $salesCount > 0 ? round($totalRevenue / $salesCount) : 0;

        if ($period === 'week') {
            $days = collect(range(0, 6))->map(fn($d) => $start->copy()->addDays($d));
            $chartLabels   = $days->map(fn($d) => $d->format('D'))->toArray();
            $chartRevenue  = $days->map(
                fn($d) => (float) Invoice::whereDate('issued_at', $d->toDateString())
                    ->where('branch_id', $branchId)
                    ->sum('total_amount')
            )->toArray();
            $chartExpenses = $days->map(
                fn($d) => (float) Expense::whereDate('expense_date', $d->toDateString())
                    ->where('branch_id', $branchId)
                    ->sum('amount')
            )->toArray();
        } elseif ($period === 'month') {
            $weeks = collect(range(1, 5));
            $chartLabels   = $weeks->map(fn($w) => "Sem. $w")->toArray();
            $chartRevenue  = $weeks->map(fn($w) => (float) Invoice::whereBetween('issued_at', [
                $start->copy()->addWeeks($w - 1),
                $start->copy()->addWeeks($w)->subSecond(),
            ])->where('branch_id', $branchId)->sum('total_amount'))->toArray();

            $chartExpenses = $weeks->map(fn($w) => (float) Expense::whereBetween('expense_date', [
                $start->copy()->addWeeks($w - 1)->toDateString(),
                $start->copy()->addWeeks($w)->subDay()->toDateString(),
            ])->where('branch_id', $branchId)->sum('amount'))->toArray();
        } else {
            $months = $period === 'quarter'
                ? collect(range(0, 2))->map(fn($m) => $start->copy()->addMonths($m))
                : collect(range(0, 11))->map(fn($m) => Carbon::now()->startOfYear()->addMonths($m));

            $chartLabels   = $months->map(fn($m) => $m->translatedFormat('M'))->toArray();
            $chartRevenue  = $months->map(
                fn($m) => (float) Invoice::whereYear('issued_at', $m->year)
                    ->whereMonth('issued_at', $m->month)
                    ->where('branch_id', $branchId)
                    ->sum('total_amount')
            )->toArray();
            $chartExpenses = $months->map(
                fn($m) => (float) Expense::whereYear('expense_date', $m->year)
                    ->whereMonth('expense_date', $m->month)
                    ->where('branch_id', $branchId)
                    ->sum('amount')
            )->toArray();
        }

        $expensesByType = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
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

        $paymentMethods = Sale::whereBetween('sold_at', [$start, $end])
            ->where('branch_id', $branchId)
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $payLabels = ['cash' => 'Espèces', 'amana' => 'Amana', 'nita' => 'Nita', 'western_union' => 'Western Union', 'moneygram' => 'MoneyGram', 'wave' => 'Wave'];
        $paymentData = [
            'labels' => $paymentMethods->pluck('payment_method')
                ->map(fn($m) => $payLabels[$m] ?? $m)->toArray(),
            'values' => $paymentMethods->pluck('total')->map(fn($v) => (float) $v)->toArray(),
        ];

        $topProducts = SaleItem::with('product')
            ->whereHas('sale', fn($q) => $q->whereBetween('sold_at', [$start, $end])
                ->where('branch_id', $branchId))
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->take(4)
            ->get();

        $topServices = SaleService::with('service')
            ->whereHas('sale', fn($q) => $q->whereBetween('sold_at', [$start, $end])
                ->where('branch_id', $branchId))
            ->select(
                'service_id',
                DB::raw('COUNT(*) as total_qty'),
                DB::raw('SUM(price) as total_revenue')
            )
            ->groupBy('service_id')
            ->orderByDesc('total_revenue')
            ->take(4)
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where('branch_id', $branchId)
            ->orderByDesc('expense_date')
            ->paginate(10)
            ->appends(['period' => $period]);

        $data = [
            'period'        => $request->get('period', 'month'),
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'totalRevenue'  => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'profit'        => $totalRevenue - $totalExpenses,
            'salesCount'    => $salesCount,
            'chartLabels'   => $chartLabels,
            'chartRevenue'  => $chartRevenue,
            'chartExpenses' => $chartExpenses,
            'expenses'      => $expenses,
            'paymentData'   => $paymentData,
        ];

        return Excel::download(
            new FinanceExport($data),
            'rapport_financier.xlsx'
        );
    }

    public function transferCash(Request $request)
    {
        try {
            $request->validate([
                'from_method' => 'required|in:cash,amana,nita',
                'to_method'   => 'required|in:cash,amana,nita',
                'amount'      => 'required|numeric|min:1',
                'note'        => 'nullable|string|max:500',
            ]);

            if ($request->from_method === $request->to_method) {
                return back()->with('error', 'Méthode source et destination identiques.');
            }

            $branchId = auth()->user()->branch_id;

            CashTransfer::create([
                'from_method' => $request->from_method,
                'to_method'   => $request->to_method,
                'amount'      => $request->amount,
                'note'        => $request->note,
                'user_id'     => auth()->id(),
                'branch_id'   => $branchId,
            ]);

            activity_log(
                'cash_transfer',
                "Transfert {$request->amount} FCFA de {$request->from_method} vers {$request->to_method}"
            );

            return back()->with('success', 'Transfert effectué avec succès.');
        } catch (\Throwable $th) {
            Log::error('Erreur lors du transfert de cash : ' . $th->getMessage(), [
                'from_method' => $request->from_method,
                'to_method'   => $request->to_method,
                'amount'      => $request->amount,
                'user_id'     => auth()->id(),
                'branch_id'   => auth()->user()->branch_id,
            ]);
            return back()->with('error', 'Une erreur est survenue lors du transfert. Veuillez réessayer.');
        }
    }
    /**
     * Solde des mouvements cash entre deux dates (bornes en DATE, inclusives).
     * $from = null signifie "depuis toujours".
     */
    private function cashMovementsBetween(int $branchId, ?string $from, string $to): float
    {
        $revenue = Invoice::with('sale')
            ->whereHas('sale', function ($q) use ($branchId, $from, $to) {
                $q->where('branch_id', $branchId)
                    ->where('payment_method', 'cash')
                    ->whereDate('sold_at', '<=', $to);
                if ($from) {
                    $q->whereDate('sold_at', '>=', $from);
                }
            })
            ->sum('total_amount');

        // ✅ Ancré sur created_at (tampon immuable) : une dépense est
        // déduite du solde à partir du jour où elle est réellement saisie
        // dans le système, quelle que soit la date comptable choisie
        // (expense_date). Simple, prévisible, jamais rétroactif.
        $expenses = Expense::where('branch_id', $branchId)
            ->where('payment_method', 'cash')
            ->where('status', 'payé')
            ->whereDate('created_at', '<=', $to)
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->sum('amount');

        $transfersIn = CashTransfer::where('branch_id', $branchId)
            ->where('to_method', 'cash')
            ->whereDate('created_at', '<=', $to)
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->sum('amount');

        $transfersOut = CashTransfer::where('branch_id', $branchId)
            ->where('from_method', 'cash')
            ->whereDate('created_at', '<=', $to)
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->sum('amount');

        return $revenue + $transfersIn - $expenses - $transfersOut;
    }

    /**
     * Invalide (supprime) tous les snapshots CashRegister de la branche à
     * partir de $fromDate inclus. Volontairement PAS de recalcul immédiat
     * en cascade ici (coûteux et inutile) : getTodayCashOpeningBalance()
     * sait déjà retrouver le dernier snapshot valide restant et combler
     * l'écart à la demande, de façon paresseuse, au prochain accès.
     *
     * N'est appelé que dans de rares cas : changement de statut
     * payé/non-payé, de payment_method vers/depuis cash, de montant sur
     * une dépense cash+payée, ou suppression d'une telle dépense.
     * Une simple correction de expense_date, titre ou type n'appelle
     * jamais cette méthode.
     */
    private function invalidateCashSnapshotsFrom(int $branchId, string $fromDate): void
    {
        CashRegister::where('branch_id', $branchId)
            ->whereDate('register_date', '>=', $fromDate)
            ->delete();
    }

    /**
     * Récupère (ou crée) le solde d'ouverture du jour pour la branche.
     * Comble automatiquement les jours sans connexion / sans snapshot,
     * ou dont le snapshot vient d'être invalidé par invalidateCashSnapshotsFrom().
     */
    private function getTodayCashOpeningBalance(int $branchId): float
    {
        $today = Carbon::today();

        $register = \App\Models\CashRegister::where('branch_id', $branchId)
            ->whereDate('register_date', $today->toDateString())
            ->first();

        if ($register) {
            return (float) $register->opening_balance;
        }

        $lastRegister = \App\Models\CashRegister::where('branch_id', $branchId)
            ->whereDate('register_date', '<', $today->toDateString())
            ->orderByDesc('register_date')
            ->first();

        if ($lastRegister) {
            $opening = (float) $lastRegister->opening_balance
                + $this->cashMovementsBetween(
                    $branchId,
                    $lastRegister->register_date->toDateString(),
                    $today->copy()->subDay()->toDateString()
                );
        } else {
            $opening = $this->cashMovementsBetween(
                $branchId,
                null,
                $today->copy()->subDay()->toDateString()
            );
        }
        $opening = max(0, $opening);

        try {
            \App\Models\CashRegister::create([
                'branch_id'       => $branchId,
                'register_date'   => $today->toDateString(),
                'opening_balance' => $opening,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            $opening = (float) \App\Models\CashRegister::where('branch_id', $branchId)
                ->whereDate('register_date', $today->toDateString())
                ->value('opening_balance');
        }

        return $opening;
    }
}