<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\ServiceSchedule;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    //  DASHBOARD ADMIN
    // ═══════════════════════════════════════════════════════════════

    public function index()
    {
        // KPI globaux (toutes branches)
        $totalBranches   = Branch::count();
        $totalUsers      = User::count();
        $totalProducts   = Product::count();

        $totalRevenue    = Sale::sum('total_amount');
        $totalExpenses   = Expense::sum('amount');
        $netProfit       = $totalRevenue - $totalExpenses;

        $monthRevenue    = Sale::whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('total_amount');

        $pendingOrders   = PurchaseOrder::where('status', 'pending')->count();
        $criticalStock   = Product::whereColumn('stock_quantity', '<=', 'alert_threshold')->count();

        // Ventes par branche (pour graphique)
        $salesByBranch   = Branch::withSum('sales', 'total_amount')->get();

        // Ventes des 30 derniers jours (graphique courbe)
        $last30Days = Sale::select(
            DB::raw('DATE(sold_at) as date'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('sold_at', '>=', now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Remplir les jours manquants
        $salesChart = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $salesChart[] = [
                'date'  => now()->subDays($i)->format('d/m'),
                'total' => $last30Days[$d]->total ?? 0,
            ];
        }

        // Top 5 branches par chiffre d'affaires
        $topBranches = Branch::withSum('sales', 'total_amount')
            ->orderByDesc('sales_sum_total_amount')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalBranches',
            'totalUsers',
            'totalProducts',
            'totalRevenue',
            'totalExpenses',
            'netProfit',
            'monthRevenue',
            'pendingOrders',
            'criticalStock',
            'salesByBranch',
            'salesChart',
            'topBranches'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    //  GESTION DES BRANCHES
    // ═══════════════════════════════════════════════════════════════

    public function branches()
    {
        $branches = Branch::withCount(['users', 'products', 'sales'])
            ->withSum('sales', 'total_amount')
            ->withSum('expenses', 'amount')
            ->orderBy('name')
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function createBranch()
    {
        return view('admin.branches.create');
    }

    public function storeBranch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:branches',
            'city' => 'nullable|string|max:100',
        ]);

        Branch::create($request->only('name', 'city'));

        return redirect()->route('admin.branches')
            ->with('success', 'Branche créée avec succès.');
    }

    public function editBranch(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('branches')->ignore($branch->id)],
            'city' => 'nullable|string|max:100',
        ]);

        $branch->update($request->only('name', 'city'));

        return redirect()->route('admin.branches')
            ->with('success', 'Branche mise à jour.');
    }

    public function destroyBranch(Branch $branch)
    {
        // Sécurité : refuser si des ventes ou utilisateurs liés
        if ($branch->sales()->exists() || $branch->users()->exists()) {
            return back()->with('error', 'Impossible de supprimer une branche avec des données liées.');
        }

        $branch->delete();
        return redirect()->route('admin.branches')
            ->with('success', 'Branche supprimée.');
    }

    // ═══════════════════════════════════════════════════════════════
    //  STOCK GLOBAL (vue consolidée toutes branches)
    // ═══════════════════════════════════════════════════════════════

    public function stock(Request $request)
    {
        $branchId = $request->branch_id;

        $query = Product::with('branch')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        // Filtres
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filter === 'critical') {
            $query->whereColumn('stock_quantity', '<=', 'alert_threshold');
        }

        $products  = $query->orderBy('name')->paginate(25)->withQueryString();
        $branches  = Branch::orderBy('name')->get();

        // Stats globales
        $totalProducts      = Product::count();
        $criticalCount      = Product::whereColumn('stock_quantity', '<=', 'alert_threshold')->count();
        $outOfStockCount    = Product::where('stock_quantity', 0)->count();
        $totalStockValue    = Product::selectRaw('SUM(price * stock_quantity)')->value(DB::raw('SUM(price * stock_quantity)'));

        // Derniers mouvements
        $recentMovements = StockMovement::with(['product', 'branch'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.stock.index', compact(
            'products',
            'branches',
            'branchId',
            'totalProducts',
            'criticalCount',
            'outOfStockCount',
            'totalStockValue',
            'recentMovements'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    //  FACTURATION GLOBALE
    // ═══════════════════════════════════════════════════════════════

    public function invoices(Request $request)
    {
        $branchId = $request->branch_id;

        $query = Invoice::with(['sale.user', 'branch'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        if ($request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }
        if ($request->from) {
            $query->whereDate('issued_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('issued_at', '<=', $request->to);
        }

        $invoices  = $query->latest('issued_at')->paginate(20)->withQueryString();
        $branches  = Branch::orderBy('name')->get();

        $totalAmount    = Invoice::when($branchId, fn($q) => $q->where('branch_id', $branchId))->sum('total_amount');
        $totalInvoices  = Invoice::when($branchId, fn($q) => $q->where('branch_id', $branchId))->count();
        $monthAmount    = Invoice::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereMonth('issued_at', now()->month)
            ->sum('total_amount');

        return view('admin.invoices.index', compact(
            'invoices',
            'branches',
            'branchId',
            'totalAmount',
            'totalInvoices',
            'monthAmount'
        ));
    }
    
     public function destroyInvoices(Request $request, Invoice $invoice)
    {
        
        $invoice->sale?->delete(); // supprime la vente liée
        $invoice->delete();
        return back()->with('success', 'Facture supprimée.');
    }

    // ═══════════════════════════════════════════════════════════════
    //  FINANCES & RAPPORTS GLOBAUX
    // ═══════════════════════════════════════════════════════════════

    // public function finances(Request $request)
    // {
    //     $year     = $request->year ?? now()->year;
    //     $branchId = $request->branch_id;

    //     // Revenus par mois
    //     $revenueByMonth = Sale::selectRaw('MONTH(sold_at) as month, SUM(total_amount) as total')
    //         ->whereYear('sold_at', $year)
    //         ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
    //         ->groupBy('month')
    //         ->pluck('total', 'month');

    //     // Dépenses par mois
    //     $expensesByMonth = Expense::selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
    //         ->whereYear('expense_date', $year)
    //         ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
    //         ->groupBy('month')
    //         ->pluck('total', 'month');

    //     // Construire tableau 12 mois
    //     $months = [];
    //     for ($m = 1; $m <= 12; $m++) {
    //         $rev  = $revenueByMonth[$m] ?? 0;
    //         $exp  = $expensesByMonth[$m] ?? 0;
    //         $months[] = [
    //             'label'    => now()->setMonth($m)->format('M'),
    //             'revenue'  => $rev,
    //             'expenses' => $exp,
    //             'profit'   => $rev - $exp,
    //         ];
    //     }

    //     // KPI annuels
    //     $annualRevenue  = array_sum(array_column($months, 'revenue'));
    //     $annualExpenses = array_sum(array_column($months, 'expenses'));
    //     $annualProfit   = $annualRevenue - $annualExpenses;
    //     $margin         = $annualRevenue > 0 ? round(($annualProfit / $annualRevenue) * 100, 1) : 0;

    //     // Dépenses par type
    //     $expensesByType = Expense::selectRaw('type, SUM(amount) as total')
    //         ->whereYear('expense_date', $year)
    //         ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
    //         ->groupBy('type')
    //         ->pluck('total', 'type');

    //     // Revenus par mode de paiement
    //     $salesByPayment = Sale::selectRaw('payment_method, SUM(total_amount) as total')
    //         ->whereYear('sold_at', $year)
    //         ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
    //         ->groupBy('payment_method')
    //         ->pluck('total', 'payment_method');

    //     // Situation par branche
    //     $branchesSituation = Branch::withSum(['sales' => fn($q) => $q->whereYear('sold_at', $year)], 'total_amount')
    //         ->withSum(['expenses' => fn($q) => $q->whereYear('expense_date', $year)], 'amount')
    //         ->get()
    //         ->map(function ($b) {
    //             $b->profit = ($b->sales_sum_total_amount ?? 0) - ($b->expenses_sum_amount ?? 0);
    //             return $b;
    //         });

    //     $branches = Branch::orderBy('name')->get();
    //     $years    = range(now()->year, now()->year - 3);

    //     return view('admin.finances.index', compact(
    //         'months',
    //         'annualRevenue',
    //         'annualExpenses',
    //         'annualProfit',
    //         'margin',
    //         'expensesByType',
    //         'salesByPayment',
    //         'branchesSituation',
    //         'branches',
    //         'years',
    //         'year',
    //         'branchId'
    //     ));
    // }
 public function finances(Request $request)
    {
        $branchId = $request->get('branch_id');   // null = toutes branches
        $year     = (int) $request->get('year', now()->year);

        $branches = Branch::orderBy('name')->get();
        $years    = range(now()->year, now()->year - 4);

        // ── Helpers de requête ──────────────────────────────────────
        $invoiceQ = fn() => Invoice::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        $expenseQ = fn() => Expense::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        $saleQ = fn() => Sale::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        // ── Plages de dates ─────────────────────────────────────────
        $today      = Carbon::today();
        $weekStart  = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart  = Carbon::create($year)->startOfYear();
        $yearEnd    = Carbon::create($year)->endOfYear();

        // ══════════════════════════════════════════════════════
        // APERÇUS PAR PÉRIODE
        // ══════════════════════════════════════════════════════

        // ── Aujourd'hui ─────────────────────────────────────────────
        $day = [
            'revenue'  => (float) $invoiceQ()->whereDate('issued_at', $today)->sum('total_amount'),
            'expenses' => (float) $expenseQ()->whereDate('expense_date', $today)->sum('amount'),
            'sales'    => $saleQ()->whereDate('sold_at', $today)->count(),
        ];
        $day['profit'] = $day['revenue'] - $day['expenses'];

        // ── Cette semaine ───────────────────────────────────────────
        $week = [
            'revenue'  => (float) $invoiceQ()->where('issued_at', '>=', $weekStart)->sum('total_amount'),
            'expenses' => (float) $expenseQ()->where('expense_date', '>=', $weekStart->toDateString())->sum('amount'),
            'sales'    => $saleQ()->where('sold_at', '>=', $weekStart)->count(),
        ];
        $week['profit'] = $week['revenue'] - $week['expenses'];

        // ── Ce mois ─────────────────────────────────────────────────
        $month = [
            'revenue'  => (float) $invoiceQ()->where('issued_at', '>=', $monthStart)->sum('total_amount'),
            'expenses' => (float) $expenseQ()->where('expense_date', '>=', $monthStart->toDateString())->sum('amount'),
            'sales'    => $saleQ()->where('sold_at', '>=', $monthStart)->count(),
        ];
        $month['profit'] = $month['revenue'] - $month['expenses'];

        // ── Année sélectionnée ───────────────────────────────────────
        $annual = [
            'revenue'  => (float) $invoiceQ()->whereBetween('issued_at', [$yearStart, $yearEnd])->sum('total_amount'),
            'expenses' => (float) $expenseQ()->whereBetween('expense_date', [$yearStart->toDateString(), $yearEnd->toDateString()])->sum('amount'),
            'sales'    => $saleQ()->whereBetween('sold_at', [$yearStart, $yearEnd])->count(),
        ];
        $annual['profit'] = $annual['revenue'] - $annual['expenses'];
        $annual['margin'] = $annual['revenue'] > 0
            ? round(($annual['profit'] / $annual['revenue']) * 100, 1)
            : 0;

        // ── Graphique mensuel (année sélectionnée) ───────────────────
        $months = collect(range(1, 12))->map(function ($m) use ($year, $invoiceQ, $expenseQ, $branchId) {
            $start = Carbon::create($year, $m, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();

            $revenue  = (float) $invoiceQ()->whereBetween('issued_at', [$start, $end])->sum('total_amount');
            $expenses = (float) $expenseQ()->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])->sum('amount');

            return [
                'label'    => $start->translatedFormat('M'),
                'revenue'  => $revenue,
                'expenses' => $expenses,
                'profit'   => $revenue - $expenses,
            ];
        });

        // ── Dépenses par type (année) ────────────────────────────────
        $expensesByType = $expenseQ()
            ->whereBetween('expense_date', [$yearStart->toDateString(), $yearEnd->toDateString()])
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'type')
            ->map(fn($v) => (float) $v);

        // ── Revenus par mode de paiement (année) ─────────────────────
        $salesByPayment = $saleQ()
            ->whereBetween('sold_at', [$yearStart, $yearEnd])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method')
            ->map(fn($v) => (float) $v);

        // ── Situation par branche (année) ────────────────────────────
        $branchesSituation = Branch::with([
            'sales' => fn($q) => $q->whereBetween('sold_at', [$yearStart, $yearEnd]),
            'expenses' => fn($q) => $q->whereBetween('expense_date', [$yearStart->toDateString(), $yearEnd->toDateString()]),
        ])
        ->withSum(['sales as sales_sum_total_amount' => fn($q) => $q->whereBetween('sold_at', [$yearStart, $yearEnd])], 'total_amount')
        ->withSum(['expenses as expenses_sum_amount'  => fn($q) => $q->whereBetween('expense_date', [$yearStart->toDateString(), $yearEnd->toDateString()])], 'amount')
        ->get()
        ->each(function ($b) {
            $b->profit = ($b->sales_sum_total_amount ?? 0) - ($b->expenses_sum_amount ?? 0);
        });

        activity_log('admin_finances_viewed', "Admin — consultation finances année $year");

        return view('admin.finances.index', compact(
            'day', 'week', 'month', 'annual',
            'months',
            'expensesByType',
            'salesByPayment',
            'branchesSituation',
            'branches',
            'years',
            'year',
            'branchId',
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // EXPORT PDF — appelé via GET /admin/finances/pdf?period=...&year=...
    // ═══════════════════════════════════════════════════════════════
   public function exportPdf(Request $request)
    {
        $period   = $request->get('period', 'month');   // day|week|month|year|custom
        $branchId = $request->get('branch_id');
        $year     = (int) $request->get('year', now()->year);
        $startDateParam = $request->get('start_date');
        $endDateParam   = $request->get('end_date');

        $branchName = $branchId
            ? Branch::findOrFail($branchId)->name
            : 'Toutes les branches';

        // ── Plage selon la période ──────────────────────────────────
        if ($period === 'custom' && $startDateParam && $endDateParam) {
            $start = Carbon::parse($startDateParam)->startOfDay();
            $end   = Carbon::parse($endDateParam)->endOfDay();
            $periodLabel = 'Du ' . $start->format('d/m/Y') . ' au ' . $end->format('d/m/Y');
        } else {
            // Fallback si "custom" est demandé sans dates valides
            if ($period === 'custom') {
                $period = 'month';
            }

            [$start, $end, $periodLabel] = match ($period) {
                'day'   => [Carbon::today(),                Carbon::today()->endOfDay(),     'Journée du ' . Carbon::today()->format('d/m/Y')],
                'week'  => [Carbon::now()->startOfWeek(),   Carbon::now()->endOfWeek(),      'Semaine du ' . Carbon::now()->startOfWeek()->format('d/m') . ' au ' . Carbon::now()->endOfWeek()->format('d/m/Y')],
                'year'  => [Carbon::create($year)->startOfYear(), Carbon::create($year)->endOfYear(), "Année $year"],
                default => [Carbon::now()->startOfMonth(),  Carbon::now()->endOfMonth(),     Carbon::now()->translatedFormat('F Y')],
            };
        }

        $invoiceQ = fn() => Invoice::query()->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        $expenseQ = fn() => Expense::query()->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        $saleQ    = fn() => Sale::query()->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        $startDate = $start->toDateString();
        $endDate   = $end->toDateString();
        $startDate = $period === 'day' ? $start->toDateString() : $start->toDateString();
        $endDate   = $end->toDateString();

        $revenue  = (float) $invoiceQ()->whereBetween('issued_at', [$start, $end])->sum('total_amount');
        $expenses = (float) $expenseQ()->whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $profit   = $revenue - $expenses;
        $margin   = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0;
        $sales    = $saleQ()->whereBetween('sold_at', [$start, $end])->count();
        $avgTicket = $sales > 0 ? round($revenue / $sales) : 0;

        $expensesByType = $expenseQ()
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        $salesByPayment = $saleQ()
            ->whereBetween('sold_at', [$start, $end])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $branchesSituation = Branch::withSum(
            ['sales as rev' => fn($q) => $q->whereBetween('sold_at', [$start, $end])],
            'total_amount'
        )
            ->withSum(
                ['expenses as exp' => fn($q) => $q->whereBetween('expense_date', [$startDate, $endDate])],
                'amount'
            )
            ->get()
            ->each(fn($b) => $b->profit = ($b->rev ?? 0) - ($b->exp ?? 0));

        $payLabels = [
            'cash' => 'Espèces',
            'amana' => 'Amana',
            'nita' => 'Nita',
            'western_union' => 'Western Union',
            'moneygram' => 'MoneyGram',
            'wave' => 'Wave'
        ];
        $typeLabels = [
            'livraison' => 'Livraison',
            'materiel' => 'Matériel',
            'salaire' => 'Salaires',
            'commande' => 'Commandes',
            'autre' => 'Autre'
        ];

        activity_log('admin_finances_pdf', "Export PDF finances — période $period");

        // ── Rendu HTML → wkhtmltopdf via Blade view ─────────────────
        $html = view('admin.finances.pdf', compact(
            'periodLabel',
            'branchName',
            'revenue',
            'expenses',
            'profit',
            'margin',
            'sales',
            'avgTicket',
            'expensesByType',
            'salesByPayment',
            'branchesSituation',
            'payLabels',
            'typeLabels'
        ))->render();

               $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled'   => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'       => 'DejaVu Sans', // bonne prise en charge des accents FR
            ]);
        
        $filename = 'rapport_finances_' . $period . '_' . now()->format('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }
    // ═══════════════════════════════════════════════════════════════
    //  GESTION DES UTILISATEURS
    // ═══════════════════════════════════════════════════════════════

    public function users(Request $request)
    {
        $query = User::with('branch');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->role) {
            $query->where('role', $request->role);
        }
        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $users    = $query->orderBy('name')->paginate(20)->withQueryString();
        $branches = Branch::orderBy('name')->get();

        $totalUsers  = User::count();
        $adminCount  = User::where('role', 'admin')->count();
        $staffCount  = User::where('role', 'staff')->count();

        return view('admin.users.index', compact(
            'users',
            'branches',
            'totalUsers',
            'adminCount',
            'staffCount'
        ));
    }

    public function createUser()
    {
        $branches = Branch::orderBy('name')->get();
        return view('admin.users.create', compact('branches'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => 'required|in:admin,manager,staff',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'branch_id' => $request->branch_id,
        ]);

        return redirect()->route('admin.users')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function editUser(User $user)
    {
        $branches = Branch::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'branches'));
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password'  => 'nullable|string|min:8|confirmed',
            'role'      => 'required|in:admin,manager,staff',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $data = $request->only('name', 'email', 'role', 'branch_id');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users')
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();
        return redirect()->route('admin.users')
            ->with('success', 'Utilisateur supprimé.');
    }

    // ═══════════════════════════════════════════════════════════════
    //  RÉINITIALISATION MOT DE PASSE (admin)
    // ═══════════════════════════════════════════════════════════════

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Mot de passe réinitialisé.');
    }
    
    
// ═══════════════════════════════════════════════════════════════
//  PLANIFICATION DES PRESTATIONS (admin)
// ═══════════════════════════════════════════════════════════════

public function schedules(Request $request)
{
    $query = ServiceSchedule::with(['service', 'sale'])
        ->orderBy('scheduled_date', 'desc')
        ->orderBy('scheduled_time', 'asc');

    // Filtres
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('date')) {
        $query->whereDate('scheduled_date', $request->date);
    }
    if ($request->filled('service_id')) {
        $query->where('service_id', $request->service_id);
    }
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('client_name', 'like', "%{$search}%")
              ->orWhere('contact_number', 'like', "%{$search}%")
              ->orWhere('address', 'like', "%{$search}%")
              ->orWhereHas('service', fn($s) => $s->where('name', 'like', "%{$search}%"));
        });
    }

    $schedules = $query->paginate(20)->withQueryString();
    $services  = Service::orderBy('name')->get();

    // Stats globales
    $stats = [
        'total'     => ServiceSchedule::count(),
        'pending'   => ServiceSchedule::where('status', 'pending')->count(),
        'confirmed' => ServiceSchedule::where('status', 'confirmed')->count(),
        'completed' => ServiceSchedule::where('status', 'completed')->count(),
        'cancelled' => ServiceSchedule::where('status', 'cancelled')->count(),
        'today'     => ServiceSchedule::whereDate('scheduled_date', today())->count(),
    ];

    activity_log('admin_schedules_viewed', 'Admin — consultation planification prestations');

    return view('admin.schedules.index', compact('schedules', 'services', 'stats'));
}

public function createSchedule()
{
    $services = Service::whereNot('name', 'Livraison')
            ->orderBy('name')
            ->get();
    $sales= Sale::with('client')->latest()->take(50)->get();

    return view('admin.schedules.create', compact('services', 'sales'));
}

public function storeSchedule(Request $request)
{
    $validated = $request->validate([
        'service_id'     => 'required|exists:services,id',
        'sale_id'        => 'nullable|exists:sales,id',
        'client_name'    => 'nullable|string|max:255',
        'contact_number' => 'required|string|max:30',
        'address'        => 'required|string|max:1000',
        'scheduled_date' => 'required|date',
        'scheduled_time' => 'nullable|date_format:H:i',
        'status'         => 'required|in:pending,confirmed,completed,cancelled',
        'notes'          => 'nullable|string|max:2000',
    ]);

    ServiceSchedule::create($validated);

    activity_log('admin_schedule_created', 'Admin — nouvelle prestation planifiée');

    return redirect()->route('admin.schedules')
        ->with('success', 'Prestation planifiée avec succès.');
}

public function showSchedule(ServiceSchedule $schedule)
{
    $schedule->load(['service', 'sale']);
    return view('admin.schedules.show', compact('schedule'));
}

public function editSchedule(ServiceSchedule $schedule)
{
    $services = Service::whereNot('name', 'Livraison')
            ->orderBy('name')
            ->get();
    $sales= Sale::with('client')->latest()->take(50)->get();

    return view('admin.schedules.edit', compact('schedule', 'services', 'sales'));
}

public function updateSchedule(Request $request, ServiceSchedule $schedule)
{
    $validated = $request->validate([
        'service_id'     => 'required|exists:services,id',
        'sale_id'        => 'nullable|exists:sales,id',
        'client_name'    => 'nullable|string|max:255',
        'contact_number' => 'required|string|max:30',
        'address'        => 'required|string|max:1000',
        'scheduled_date' => 'required|date',
        'scheduled_time' => 'nullable|date_format:H:i',
        'status'         => 'required|in:pending,confirmed,completed,cancelled',
        'notes'          => 'nullable|string|max:2000',
    ]);

    $schedule->update($validated);

    activity_log('admin_schedule_updated', "Admin — prestation #{$schedule->id} mise à jour");

    return redirect()->route('admin.schedules')
        ->with('success', 'Prestation mise à jour avec succès.');
}

public function destroySchedule(ServiceSchedule $schedule)
{
    $id = $schedule->id;
    $schedule->delete();

    activity_log('admin_schedule_deleted', "Admin — prestation #{$id} supprimée");

    return redirect()->route('admin.schedules')
        ->with('success', 'Prestation supprimée.');
}

public function updateScheduleStatus(Request $request, ServiceSchedule $schedule)
{
    $request->validate([
        'status' => 'required|in:pending,confirmed,completed,cancelled',
    ]);

    $schedule->update(['status' => $request->status]);

    activity_log('admin_schedule_status', "Admin — statut prestation #{$schedule->id} → {$request->status}");

    return back()->with('success', 'Statut mis à jour.');
}

 public function cancelInvoice(Invoice $invoice)
    {
        if ($invoice->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cette facture est déjà annulée.');
        }

        if ($invoice) {
            $invoice->update(['status' => 'cancelled']);
            activity_log('invoice_cancelled', "Facture annulée : {$invoice->invoice_number}");
            return redirect()->back()->with('success', 'Facture annulée avec succès.');
        }
        Log::error("Tentative d'annulation de facture échouée : facture introuvable (ID: {$invoice->id})");
        return redirect()->back()->with('error', 'Seules les factures payées en espèces peuvent être annulées.');
    }
}
