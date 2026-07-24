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
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 76px;
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
            width: var(--sidebar-width);
            color: white;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            padding: 0;
            box-shadow: 4px 0 24px rgba(75,29,149,0.18);
            overflow-y: auto;
            overflow-x: hidden;
            transition: width 0.28s cubic-bezier(0.4,0,0.2,1);
        }

        /* Sidebar réduite (desktop) — icônes seules */
        .sidebar.collapsed { width: var(--sidebar-width-collapsed); }

        .sidebar.collapsed .sidebar-logo { padding: 24px 0; text-align: center; }
        .sidebar.collapsed .sidebar-logo-title { font-size: 0; }
        .sidebar.collapsed .sidebar-logo-title i { font-size: 20px; margin: 0 !important; }
        .sidebar.collapsed .sidebar-logo-sub,
        .sidebar.collapsed .nav-section,
        .sidebar.collapsed .nav-label,
        .sidebar.collapsed .nav-badge,
        .sidebar.collapsed .sidebar-user-name,
        .sidebar.collapsed .sidebar-user-role,
        .sidebar.collapsed .sidebar-version,
        .sidebar.collapsed .logout-label {
            display: none;
        }
        .sidebar.collapsed .sidebar-nav .nav-link {
            justify-content: center;
            padding: 12px 0;
        }
        .sidebar.collapsed .sidebar-nav .nav-link i { margin: 0; }
        .sidebar.collapsed .sidebar-footer-user { justify-content: center; }
        .sidebar.collapsed .sidebar-logout-btn { padding: 10px 0; }

        /* Logo zone */
        .sidebar-logo {
            padding: 28px 24px 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 12px;
            flex-shrink: 0;
            transition: padding 0.2s;
        }
        .sidebar-logo-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1px;
            color: white;
            line-height: 1;
            white-space: nowrap;
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
            white-space: nowrap;
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
            white-space: nowrap;
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
        .sidebar.collapsed .sidebar-nav .nav-link.active::before { display: none; }

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
            transition: padding 0.2s;
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
        .sidebar-user-name  { font-size: 13px; font-weight: 600; color: white; line-height: 1.2; white-space: nowrap; }
        .sidebar-user-role  { font-size: 11px; color: rgba(255,255,255,0.5); white-space: nowrap; }
        .sidebar-version    { font-size: 10px; color: rgba(255,255,255,0.3); margin-top: 10px; white-space: nowrap; }

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
            white-space: nowrap;
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
            margin-left: var(--sidebar-width);
            padding: 20px 24px;
            min-height: 100vh;
            transition: margin-left 0.28s cubic-bezier(0.4,0,0.2,1);
        }
        .main-content.collapsed { margin-left: var(--sidebar-width-collapsed); }

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

        /* Bouton hamburger — visible desktop ET mobile */
        .topbar-menu-btn {
            display: flex;
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            align-items: center; justify-content: center;
            color: white; font-size: 16px;
            cursor: pointer; border: none;
            transition: background 0.15s;
            flex-shrink: 0;
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
                width: var(--sidebar-width) !important; /* pas de mode réduit en mobile */
            }
            .sidebar.open { transform: translateX(0); }
            .sidebar.collapsed .sidebar-logo-sub,
            .sidebar.collapsed .nav-section,
            .sidebar.collapsed .nav-label,
            .sidebar.collapsed .sidebar-user-name,
            .sidebar.collapsed .sidebar-user-role,
            .sidebar.collapsed .sidebar-version,
            .sidebar.collapsed .logout-label {
                display: revert; /* on annule le mode "icônes seules" en mobile */
            }
            .main-content { margin-left: 0 !important; padding: 14px; }
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
            <i class="fas fa-leaf me-2" style="color:rgba(255,255,255,0.6);font-size:18px;"></i><span class="nav-label">ILYKEN</span>
        </div>
        <div class="sidebar-logo-sub">Manager · v1.0</div>
    </div>

    {{-- Navigation --}}
    <ul class="sidebar-nav">

        <li class="nav-section">Principal</li>

        <li class="nav-item">
            <a href="{{ route('dashboard') }}"
               title="Tableau de bord"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i> <span class="nav-label">Tableau de bord</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('sales.index') }}"
               title="Point de Vente"
               class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <i class="fas fa-cash-register"></i> <span class="nav-label">Point de Vente</span>
            </a>
        </li>

        <li class="nav-section">Gestion</li>

        <li class="nav-item">
            <a href="{{ route('stock.index') }}"
               title="Stock"
               class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                <i class="fas fa-boxes-stacked"></i> <span class="nav-label">Stock</span>
                @if(isset($criticalStockCount) && $criticalStockCount > 0)
                    <span class="nav-badge">{{ $criticalStockCount }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('products.index') }}"
               title="Produits & Services"
               class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="fas fa-tag"></i> <span class="nav-label">Produits & Services</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('purchases.index') }}"
               title="Réapprovisionnement"
               class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <i class="fas fa-truck-ramp-box"></i> <span class="nav-label">Réapprovisionnement</span>
                @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                    <span class="nav-badge">{{ $pendingOrdersCount }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('invoices.index') }}"
               title="Facturation"
               class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i> <span class="nav-label">Facturation</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('service-schedules.index') }}"
               title="Plannification"
               class="nav-link {{ request()->routeIs('service-schedules.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> <span class="nav-label">Plannification</span>
            </a>
        </li>
        <li class="nav-section">Rapports</li>

        <li class="nav-item">
            <a href="{{ route('expenses.index') }}"
               title="Finances & Rapports"
               class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> <span class="nav-label">Finances & Rapports</span>
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
            <button type="submit" class="sidebar-logout-btn" title="Déconnexion">
                <i class="fas fa-sign-out-alt"></i> <span class="logout-label">Déconnexion</span>
            </button>
        </form>

        <div class="sidebar-version">Ilyken Manager • 2026</div>
    </div>

</div>

{{-- ══════════════════════ MAIN ══════════════════════ --}}
<div class="main-content" id="main-content">

    {{-- Topbar --}}
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="topbar-menu-btn" id="menu-toggle-btn" onclick="toggleSidebar()" title="Réduire / afficher le menu">
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

    // ── Sidebar : mobile (overlay) + desktop (collapse) ──────────────────────
    const MOBILE_BREAKPOINT = 768;

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const mainContent = document.getElementById('main-content');
        const isMobile = window.innerWidth <= MOBILE_BREAKPOINT;

        if (isMobile) {
            const isOpen = sidebar.classList.contains('open');
            sidebar.classList.toggle('open', !isOpen);
            overlay.classList.toggle('show', !isOpen);
        } else {
            const isCollapsed = sidebar.classList.contains('collapsed');
            sidebar.classList.toggle('collapsed', !isCollapsed);
            mainContent.classList.toggle('collapsed', !isCollapsed);
            localStorage.setItem('sidebarCollapsed', (!isCollapsed) ? '1' : '0');
        }
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-overlay').classList.remove('show');
    }

    // ── Restaure la préférence de sidebar réduite (desktop uniquement) ───────
    document.addEventListener('DOMContentLoaded', () => {
        if (window.innerWidth > MOBILE_BREAKPOINT && localStorage.getItem('sidebarCollapsed') === '1') {
            document.getElementById('sidebar').classList.add('collapsed');
            document.getElementById('main-content').classList.add('collapsed');
        }
    });
</script>

@stack('scripts')

</body>
</html>