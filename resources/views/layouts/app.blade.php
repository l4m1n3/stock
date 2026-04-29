<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Ilyken Manager')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --violet: #6B46C1;
            --violet-dark: #4C1D95;
            --violet-light: #EEEDFE;
            --violet-hover: #5A3AA6;
        }

        * { box-sizing: border-box; }

        body {
            background-color: #f4f2fc;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
        }

        /* ══════════════════════ SIDEBAR ══════════════════════ */
        .sidebar {
            background: linear-gradient(160deg, var(--violet) 0%, var(--violet-dark) 100%);
            min-height: 100vh;
            position: fixed;
            top: 0; left: 0;
            width: 260px;
            color: white;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            padding: 0;
            box-shadow: 4px 0 24px rgba(75,29,149,0.18);
            overflow-y: auto;
        }

        /* Logo zone */
        .sidebar-logo {
            padding: 28px 24px 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 12px;
            flex-shrink: 0;
        }
        .sidebar-logo-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1px;
            color: white;
            line-height: 1;
        }
        .sidebar-logo-sub {
            font-size: 11px;
            color: rgba(255,255,255,0.55);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            padding: 0 12px;
            list-style: none;
            margin: 0;
        }
        .sidebar-nav .nav-section {
            font-size: 10px;
            font-weight: 700;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 18px 12px 6px 12px;
        }
        .sidebar-nav .nav-item { margin-bottom: 2px; }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: rgba(255,255,255,0.75) !important;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.18s;
            position: relative;
        }
        .sidebar-nav .nav-link i {
            width: 18px;
            text-align: center;
            font-size: 15px;
            opacity: 0.85;
            flex-shrink: 0;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.12);
            color: white !important;
        }
        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.18);
            color: white !important;
            font-weight: 600;
        }
        .sidebar-nav .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 20px;
            background: white;
            border-radius: 0 3px 3px 0;
        }

        /* Badge notification nav */
        .nav-badge {
            margin-left: auto;
            background: #E24B4A;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 99px;
            flex-shrink: 0;
        }

        /* Footer sidebar */
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
            flex-shrink: 0;
        }
        .sidebar-footer-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-avatar {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: white;
            flex-shrink: 0;
        }
        .sidebar-user-name  { font-size: 13px; font-weight: 600; color: white; line-height: 1.2; }
        .sidebar-user-role  { font-size: 11px; color: rgba(255,255,255,0.5); }
        .sidebar-version    { font-size: 10px; color: rgba(255,255,255,0.3); margin-top: 10px; }

        .sidebar-logout-btn {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: none;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.8);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.15s;
            margin-top: 12px;
        }
        .sidebar-logout-btn:hover {
            background: #E24B4A;
            color: #fff;
        }

        /* Scrollbar sidebar */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

        /* ══════════════════════ MAIN CONTENT ══════════════════════ */
        .main-content {
            margin-left: 260px;
            padding: 20px 24px;
            min-height: 100vh;
        }

        /* ══════════════════════ TOPBAR ══════════════════════ */
        .topbar {
            background: linear-gradient(135deg, var(--violet) 0%, #9F7AEA 100%);
            border-radius: 14px;
            padding: 14px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(107,70,193,0.2);
        }
        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: white;
            margin: 0;
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        .topbar-avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: white;
        }
        .topbar-user-name  { font-size: 13px; font-weight: 600; color: white; line-height: 1.2; }
        .topbar-user-role  { font-size: 11px; color: rgba(255,255,255,0.65); }

        /* Bouton hamburger mobile */
        .topbar-menu-btn {
            display: none;
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            align-items: center; justify-content: center;
            color: white; font-size: 16px;
            cursor: pointer; border: none;
            transition: background 0.15s;
        }
        .topbar-menu-btn:hover { background: rgba(255,255,255,0.25); }

        /* ══════════════════════ ALERTS SESSION ══════════════════════ */
        .session-alert {
            border-radius: 12px;
            border: none;
            padding: 12px 18px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .session-alert-success {
            background: #EAF3DE;
            color: #3B6D11;
            border-left: 4px solid #639922;
        }
        .session-alert-error {
            background: #FCEBEB;
            color: #A32D2D;
            border-left: 4px solid #E24B4A;
        }

        /* ══════════════════════ GLOBAL UTILS ══════════════════════ */
        .btn-violet {
            background-color: var(--violet);
            border: none; color: white;
            border-radius: 10px;
            padding: 9px 18px;
            font-weight: 600; font-size: 13px;
            cursor: pointer;
            display: inline-flex; align-items: center; gap: 7px;
            transition: background 0.15s, transform 0.1s;
            text-decoration: none;
        }
        .btn-violet:hover {
            background-color: var(--violet-hover);
            color: white;
            transform: translateY(-1px);
        }

        .card-kpi {
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(107,70,193,0.10);
        }

        .table th {
            background-color: #f0ebff;
            color: var(--violet);
        }

        /* Overlay mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 999;
        }
        .sidebar-overlay.show { display: block; }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.28s cubic-bezier(0.4,0,0.2,1);
            }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 14px; }
            .topbar-menu-btn { display: flex; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- Overlay mobile --}}
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

{{-- ══════════════════════ SIDEBAR ══════════════════════ --}}
<div class="sidebar" id="sidebar">

    {{-- Logo --}}
    <div class="sidebar-logo">
        <div class="sidebar-logo-title">
            <i class="fas fa-leaf me-2" style="color:rgba(255,255,255,0.6);font-size:18px;"></i>ILYKEN
        </div>
        <div class="sidebar-logo-sub">Manager · v1.0</div>
    </div>

    {{-- Navigation --}}
    <ul class="sidebar-nav">

        <li class="nav-section">Principal</li>

        <li class="nav-item">
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i> Tableau de bord
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('sales.index') }}"
               class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <i class="fas fa-cash-register"></i> Point de Vente
            </a>
        </li>

        <li class="nav-section">Gestion</li>

        <li class="nav-item">
            <a href="{{ route('stock.index') }}"
               class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                <i class="fas fa-boxes-stacked"></i> Stock
                @if(isset($criticalStockCount) && $criticalStockCount > 0)
                    <span class="nav-badge">{{ $criticalStockCount }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('products.index') }}"
               class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="fas fa-tag"></i> Produits & Services
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('purchases.index') }}"
               class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <i class="fas fa-truck-ramp-box"></i> Réapprovisionnement
                @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                    <span class="nav-badge">{{ $pendingOrdersCount }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('invoices.index') }}"
               class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i> Facturation
            </a>
        </li>

        <li class="nav-section">Rapports</li>

        <li class="nav-item">
            <a href="{{ route('expenses.index') }}"
               class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Finances & Rapports
            </a>
        </li>

        <li class="nav-section">Admin</li>

        <li class="nav-item">
            <a href="#" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Utilisateurs
            </a>
        </li>

    </ul>

    {{-- Footer user --}}
    <div class="sidebar-footer">
        <div class="sidebar-footer-user">
            <div class="sidebar-avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <div class="sidebar-user-name">{{ auth()->user()->name ?? 'Utilisateur' }}</div>
                <div class="sidebar-user-role">{{ ucfirst(auth()->user()->role ?? 'Utilisateur') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout-btn">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </button>
        </form>

        <div class="sidebar-version">Ilyken Manager • 2026</div>
    </div>

</div>

{{-- ══════════════════════ MAIN ══════════════════════ --}}
<div class="main-content">

    {{-- Topbar --}}
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="topbar-menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h4 class="topbar-title">@yield('page-title', 'Ilyken Manager')</h4>
        </div>
        <div class="topbar-right">
            <div class="topbar-user">
                <div class="topbar-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div class="topbar-user-name">{{ auth()->user()->name ?? 'Utilisateur' }}</div>
                    <div class="topbar-user-role">{{ ucfirst(auth()->user()->role ?? 'Utilisateur') }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertes session --}}
    @if(session('success'))
        <div class="session-alert session-alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="session-alert session-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="session-alert session-alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Contenu de la page --}}
    @yield('content')

</div>

{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    // ── Auto-dismiss session alerts ──────────────────────────────────────────
    document.querySelectorAll('.session-alert').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 4000);
    });

    // ── Sidebar mobile ────────────────────────────────────────────────────────
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const overlay  = document.getElementById('sidebar-overlay');
        const isOpen   = sidebar.classList.contains('open');
        sidebar.classList.toggle('open', !isOpen);
        overlay.classList.toggle('show', !isOpen);
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('show');
    }

    // ── salesChart guard (dashboard uniquement) ───────────────────────────────
    const salesChartEl = document.getElementById('salesChart');
    if (salesChartEl) {
        new Chart(salesChartEl, {
            type: 'line',
            data: {
                labels: ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'],
                datasets: [{
                    label: 'Ventes',
                    data: [120000,185000,210000,245000,198000,312000,278000],
                    borderColor: '#6B46C1',
                    backgroundColor: 'rgba(107,70,193,0.08)',
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: '#f0ebff' }, ticks: { color: '#9e8fc0', callback: v => (v/1000)+'k' } },
                    x: { grid: { display: false }, ticks: { color: '#9e8fc0' } }
                }
            }
        });
    }
</script>

@stack('scripts')

</body>
</html>