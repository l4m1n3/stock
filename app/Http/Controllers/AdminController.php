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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    // ═══════════════════════════════════════════════════════════════
    //  FINANCES & RAPPORTS GLOBAUX
    // ═══════════════════════════════════════════════════════════════

    public function finances(Request $request)
    {
        $year     = $request->year ?? now()->year;
        $branchId = $request->branch_id;

        // Revenus par mois
        $revenueByMonth = Sale::selectRaw('MONTH(sold_at) as month, SUM(total_amount) as total')
            ->whereYear('sold_at', $year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->groupBy('month')
            ->pluck('total', 'month');

        // Dépenses par mois
        $expensesByMonth = Expense::selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
            ->whereYear('expense_date', $year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->groupBy('month')
            ->pluck('total', 'month');

        // Construire tableau 12 mois
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $rev  = $revenueByMonth[$m] ?? 0;
            $exp  = $expensesByMonth[$m] ?? 0;
            $months[] = [
                'label'    => now()->setMonth($m)->format('M'),
                'revenue'  => $rev,
                'expenses' => $exp,
                'profit'   => $rev - $exp,
            ];
        }

        // KPI annuels
        $annualRevenue  = array_sum(array_column($months, 'revenue'));
        $annualExpenses = array_sum(array_column($months, 'expenses'));
        $annualProfit   = $annualRevenue - $annualExpenses;
        $margin         = $annualRevenue > 0 ? round(($annualProfit / $annualRevenue) * 100, 1) : 0;

        // Dépenses par type
        $expensesByType = Expense::selectRaw('type, SUM(amount) as total')
            ->whereYear('expense_date', $year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->groupBy('type')
            ->pluck('total', 'type');

        // Revenus par mode de paiement
        $salesByPayment = Sale::selectRaw('payment_method, SUM(total_amount) as total')
            ->whereYear('sold_at', $year)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        // Situation par branche
        $branchesSituation = Branch::withSum(['sales' => fn($q) => $q->whereYear('sold_at', $year)], 'total_amount')
            ->withSum(['expenses' => fn($q) => $q->whereYear('expense_date', $year)], 'amount')
            ->get()
            ->map(function ($b) {
                $b->profit = ($b->sales_sum_total_amount ?? 0) - ($b->expenses_sum_amount ?? 0);
                return $b;
            });

        $branches = Branch::orderBy('name')->get();
        $years    = range(now()->year, now()->year - 3);

        return view('admin.finances.index', compact(
            'months',
            'annualRevenue',
            'annualExpenses',
            'annualProfit',
            'margin',
            'expensesByType',
            'salesByPayment',
            'branchesSituation',
            'branches',
            'years',
            'year',
            'branchId'
        ));
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
}
