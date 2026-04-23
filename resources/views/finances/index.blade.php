@extends('layouts.app')

@section('title', 'Finances & Rapports')

@section('content')

<style>
    :root { --violet: #6B46C1; --violet-light: #EEEDFE; --violet-hover: #5A3AA6; }

    /* ── Header ── */
    .fin-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }

    /* ── Période selector ── */
    .period-nav {
        display: flex; align-items: center; gap: 6px;
        background: #f7f4ff; border-radius: 12px; padding: 4px;
    }
    .period-btn {
        padding: 7px 16px; border-radius: 9px;
        font-size: 12px; font-weight: 600; border: none;
        background: transparent; color: #9e8fc0; cursor: pointer;
        transition: all 0.15s;
    }
    .period-btn.active {
        background: #fff; color: var(--violet);
        box-shadow: 0 2px 8px rgba(107,70,193,0.12);
    }

    /* ── KPIs ── */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
    .kpi-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #e8e4f7; padding: 20px;
        box-shadow: 0 2px 12px rgba(107,70,193,0.06);
        position: relative; overflow: hidden;
    }
    .kpi-card::before {
        content: ''; position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
    }
    .kpi-card.violet::before { background: var(--violet); }
    .kpi-card.green::before  { background: #639922; }
    .kpi-card.red::before    { background: #E24B4A; }
    .kpi-card.amber::before  { background: #EF9F27; }

    .kpi-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; margin-bottom: 12px;
    }
    .kpi-label { font-size: 11px; font-weight: 600; color: #9e8fc0; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 6px; }
    .kpi-value { font-size: 22px; font-weight: 800; color: #2d2d2d; line-height: 1; }
    .kpi-sub   { font-size: 11px; color: #b0a0d0; margin-top: 5px; }
    .kpi-trend {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: 11px; font-weight: 600; padding: 2px 8px;
        border-radius: 99px; margin-top: 6px;
    }
    .trend-up   { background: #EAF3DE; color: #3B6D11; }
    .trend-down { background: #FCEBEB; color: #A32D2D; }

    /* ── Panels ── */
    .panel {
        background: #fff; border-radius: 16px;
        border: 1px solid #e8e4f7;
        box-shadow: 0 4px 20px rgba(107,70,193,0.07);
        overflow: hidden; margin-bottom: 24px;
    }
    .panel-header {
        padding: 16px 20px; border-bottom: 1px solid #f0ebff;
        background: #faf8ff;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .panel-title { font-size: 15px; font-weight: 600; color: #2d2d2d; }

    /* ── Grille 2 colonnes ── */
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
    .three-col { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px; }

    /* ── Charts ── */
    .chart-wrap { padding: 20px; }
    .chart-container { position: relative; height: 240px; }

    /* ── Boutons ── */
    .btn-violet {
        padding: 9px 18px; background: var(--violet);
        color: white; border: none; border-radius: 10px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        display: inline-flex; align-items: center; gap: 7px;
        transition: background 0.15s, transform 0.1s;
    }
    .btn-violet:hover { background: var(--violet-hover); transform: translateY(-1px); }
    .btn-outline-violet {
        padding: 9px 18px; background: transparent;
        color: var(--violet); border: 1.5px solid var(--violet);
        border-radius: 10px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
        transition: all 0.15s;
    }
    .btn-outline-violet:hover { background: var(--violet-light); }

    /* ── Table dépenses ── */
    .exp-table { width: 100%; border-collapse: collapse; }
    .exp-table thead tr { background: #f7f4ff; }
    .exp-table th {
        padding: 11px 18px; font-size: 11px; font-weight: 700;
        color: var(--violet); text-transform: uppercase;
        letter-spacing: 0.06em; border-bottom: 1px solid #f0ebff; white-space: nowrap;
    }
    .exp-table td {
        padding: 12px 18px; font-size: 13px; color: #2d2d2d;
        border-bottom: 1px solid #f7f4ff; vertical-align: middle;
    }
    .exp-table tbody tr:last-child td { border-bottom: none; }
    .exp-table tbody tr:hover { background: #faf8ff; }

    .badge-exp {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 99px;
        font-size: 11px; font-weight: 600;
    }
    .exp-livraison { background: #EEEDFE; color: #534AB7; }
    .exp-materiel  { background: #FAEEDA; color: #854F0B; }
    .exp-salaire   { background: #EAF3DE; color: #3B6D11; }
    .exp-autre     { background: #f0ebff; color: #7c6fa0; }

    /* ── Toolbar ── */
    .toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .toolbar input[type=text] {
        padding: 8px 14px; border: 1px solid #e0d9f7;
        border-radius: 10px; font-size: 13px;
        background: #faf8ff; color: #2d2d2d;
        outline: none; width: 190px; transition: border-color 0.15s;
    }
    .toolbar input:focus { border-color: var(--violet); }
    .toolbar select {
        padding: 8px 12px; border: 1px solid #e0d9f7;
        border-radius: 10px; font-size: 13px;
        background: #faf8ff; color: #2d2d2d; outline: none; cursor: pointer;
    }

    /* ── Action btns ── */
    .action-btns { display: flex; gap: 6px; }
    .btn-icon {
        width: 30px; height: 30px; border-radius: 8px;
        border: 1px solid #e0d9f7; background: #faf8ff;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        color: #9e8fc0; font-size: 13px; transition: all 0.15s;
    }
    .btn-icon:hover { background: var(--violet-light); color: var(--violet); border-color: var(--violet); }
    .btn-icon.danger:hover { background: #FCEBEB; color: #A32D2D; border-color: #E24B4A; }

    /* ── Récapitulatif catégories ── */
    .cat-row {
        display: flex; align-items: center;
        gap: 12px; padding: 13px 20px;
        border-bottom: 1px solid #f7f4ff;
    }
    .cat-row:last-child { border-bottom: none; }
    .cat-icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }
    .cat-info { flex: 1; }
    .cat-name  { font-size: 13px; font-weight: 600; color: #2d2d2d; }
    .cat-bar-bg {
        height: 5px; background: #f0ebff; border-radius: 99px;
        overflow: hidden; margin-top: 5px;
    }
    .cat-bar { height: 100%; border-radius: 99px; }
    .cat-amount { font-size: 13px; font-weight: 700; white-space: nowrap; }
    .cat-pct    { font-size: 11px; color: #9e8fc0; white-space: nowrap; }

    /* ── Top ventes ── */
    .top-row {
        display: flex; align-items: center; gap: 12px;
        padding: 11px 20px; border-bottom: 1px solid #f7f4ff;
    }
    .top-row:last-child { border-bottom: none; }
    .top-rank {
        width: 26px; height: 26px; border-radius: 8px;
        background: var(--violet-light); color: var(--violet);
        font-size: 12px; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .top-name  { font-size: 13px; font-weight: 600; color: #2d2d2d; flex: 1; }
    .top-count { font-size: 12px; color: #9e8fc0; }
    .top-amt   { font-size: 13px; font-weight: 700; color: var(--violet); white-space: nowrap; }

    /* ── Modal ── */
    .modal-content { border-radius: 16px; border: none; }
    .modal-header-violet {
        background: linear-gradient(135deg, #6B46C1, #9F7AEA);
        border-radius: 16px 16px 0 0; padding: 16px 20px;
    }
    .form-label-s { font-size: 13px; font-weight: 600; color: #5a4a7a; margin-bottom: 5px; display: block; }
    .form-control-s {
        width: 100%; padding: 9px 14px;
        border: 1px solid #e0d9f7; border-radius: 10px;
        font-size: 14px; color: #2d2d2d; outline: none;
        transition: border-color 0.15s; background: #faf8ff;
    }
    .form-control-s:focus { border-color: var(--violet); background: #fff; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { margin-bottom: 16px; }

    /* ── Toast ── */
    #fin-toast {
        position: fixed; top: 20px; right: 20px;
        background: #3B6D11; color: white;
        padding: 12px 22px; border-radius: 12px;
        font-size: 14px; font-weight: 500;
        display: none; z-index: 9999;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    /* ── Solde indicator ── */
    .solde-box {
        padding: 20px; text-align: center;
    }
    .solde-label { font-size: 12px; font-weight: 700; color: #9e8fc0; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 8px; }
    .solde-value { font-size: 30px; font-weight: 800; line-height: 1; }
    .solde-sub   { font-size: 12px; color: #b0a0d0; margin-top: 6px; }

    @media (max-width: 900px) {
        .kpi-grid { grid-template-columns: 1fr 1fr; }
        .two-col, .three-col { grid-template-columns: 1fr; }
        .form-row { grid-template-columns: 1fr; }
    }
</style>

<div id="fin-toast">✓ Dépense enregistrée</div>

{{-- ── Header ── --}}
<div class="fin-header">
    <div>
        <h4 class="fw-bold mb-0">Finances & Rapports</h4>
        <small class="text-muted">Tableau de bord financier — {{ now()->format('d/m/Y') }}</small>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="period-nav" id="period-nav">
            <button class="period-btn" onclick="setPeriod(this,'week')">7 jours</button>
            <button class="period-btn active" onclick="setPeriod(this,'month')">Ce mois</button>
            <button class="period-btn" onclick="setPeriod(this,'quarter')">Trimestre</button>
            <button class="period-btn" onclick="setPeriod(this,'year')">Année</button>
        </div>
        <button class="btn-violet" data-bs-toggle="modal" data-bs-target="#modalDepense">
            <i class="fas fa-plus"></i> Nouvelle dépense
        </button>
    </div>
</div>

{{-- ── KPIs ── --}}
<div class="kpi-grid">
    <div class="kpi-card violet">
        <div class="kpi-icon" style="background:#EEEDFE;">
            <i class="fas fa-cash-register" style="color:var(--violet);"></i>
        </div>
        <div class="kpi-label">Chiffre d'affaires</div>
        <div class="kpi-value">{{ number_format($totalRevenue, 0, ',', ' ') }}</div>
        <div class="kpi-sub">FCFA ce mois</div>
        <div class="kpi-trend trend-up">↑ {{ $revenueGrowth }}% vs mois préc.</div>
    </div>
    <div class="kpi-card red">
        <div class="kpi-icon" style="background:#FCEBEB;">
            <i class="fas fa-arrow-trend-down" style="color:#E24B4A;"></i>
        </div>
        <div class="kpi-label">Dépenses totales</div>
        <div class="kpi-value" style="color:#E24B4A;">{{ number_format($totalExpenses, 0, ',', ' ') }}</div>
        <div class="kpi-sub">FCFA ce mois</div>
        <div class="kpi-trend trend-down">{{ $expenseCount }} opérations</div>
    </div>
    <div class="kpi-card green">
        <div class="kpi-icon" style="background:#EAF3DE;">
            <i class="fas fa-sack-dollar" style="color:#639922;"></i>
        </div>
        <div class="kpi-label">Bénéfice net</div>
        @php $profit = $totalRevenue - $totalExpenses; @endphp
        <div class="kpi-value" style="color:{{ $profit >= 0 ? '#639922' : '#E24B4A' }};">
            {{ number_format($profit, 0, ',', ' ') }}
        </div>
        <div class="kpi-sub">FCFA ce mois</div>
        <div class="kpi-trend {{ $profit >= 0 ? 'trend-up' : 'trend-down' }}">
            {{ $profit >= 0 ? '↑ Positif' : '↓ Déficit' }}
        </div>
    </div>
    <div class="kpi-card amber">
        <div class="kpi-icon" style="background:#FAEEDA;">
            <i class="fas fa-receipt" style="color:#EF9F27;"></i>
        </div>
        <div class="kpi-label">Ticket moyen</div>
        <div class="kpi-value" style="color:#BA7517;">{{ number_format($avgTicket, 0, ',', ' ') }}</div>
        <div class="kpi-sub">FCFA / vente</div>
        <div class="kpi-trend trend-up">{{ $salesCount }} ventes</div>
    </div>
</div>

{{-- ── Graphiques ── --}}
<div class="three-col">

    {{-- Graphique CA vs Dépenses --}}
    {{-- <div class="panel">
        <div class="panel-header">
            <span class="panel-title">
                <i class="fas fa-chart-line me-2" style="color:var(--violet);"></i>
                Évolution CA & Dépenses
            </span>
            <div style="display:flex;gap:14px;font-size:12px;">
                <span><span style="display:inline-block;width:10px;height:10px;background:var(--violet);border-radius:50%;margin-right:4px;"></span>CA</span>
                <span><span style="display:inline-block;width:10px;height:10px;background:#E24B4A;border-radius:50%;margin-right:4px;"></span>Dépenses</span>
            </div>
        </div>
        <div class="chart-wrap">
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div> --}}

    {{-- Répartition dépenses par catégorie ── --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">
                <i class="fas fa-pie-chart me-2" style="color:var(--violet);"></i>
                Dépenses
            </span>
        </div>
        <div class="chart-wrap" style="padding-bottom:8px;">
            <div class="chart-container" style="height:160px;">
                <canvas id="expenseDonut"></canvas>
            </div>
        </div>
        @php
            $catIcons  = ['livraison'=>'🚚','materiel'=>'🔧','salaire'=>'👤','autre'=>'📦'];
            $catColors = ['livraison'=>'#7F77DD','materiel'=>'#EF9F27','salaire'=>'#639922','autre'=>'#9e8fc0'];
            $catLabels = ['livraison'=>'Livraison','materiel'=>'Matériel','salaire'=>'Salaires','autre'=>'Autre'];
            $totalExp  = $expensesByType->sum('total');
        @endphp
        @foreach($expensesByType as $cat)
        @php
            $pct = $totalExp > 0 ? round($cat->total / $totalExp * 100) : 0;
            $key = $cat->type;
        @endphp
        <div class="cat-row">
            <div class="cat-icon" style="background:{{ $catColors[$key] ?? '#9e8fc0' }}22;">
                {{ $catIcons[$key] ?? '📦' }}
            </div>
            <div class="cat-info">
                <div class="cat-name">{{ $catLabels[$key] ?? ucfirst($key) }}</div>
                <div class="cat-bar-bg">
                    <div class="cat-bar" style="width:{{ $pct }}%;background:{{ $catColors[$key] ?? '#9e8fc0' }};"></div>
                </div>
            </div>
            <div style="text-align:right;">
                <div class="cat-amount" style="color:{{ $catColors[$key] ?? '#2d2d2d' }};">
                    {{ number_format($cat->total, 0, ',', ' ') }}
                </div>
                <div class="cat-pct">{{ $pct }}%</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Table dépenses + Top produits ── --}}
<div class="two-col">

    {{-- Dépenses récentes ── --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">
                <i class="fas fa-list-ul me-2" style="color:var(--violet);"></i>
                Dépenses récentes
            </span>
            <div class="toolbar">
                <input type="text" id="exp-search" placeholder="Rechercher..." oninput="filterExpenses()">
                <select id="exp-type-filter" onchange="filterExpenses()">
                    <option value="">Tous types</option>
                    <option value="livraison">Livraison</option>
                    <option value="materiel">Matériel</option>
                    <option value="salaire">Salaire</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="exp-table">
                <thead>
                    <tr>
                        <th>Intitulé</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="exp-tbody">
                    @forelse($expenses as $exp)
                    <tr data-title="{{ strtolower($exp->title) }}" data-type="{{ $exp->type }}">
                        <td style="font-weight:600;">{{ $exp->title }}</td>
                        <td>
                            <span class="badge-exp exp-{{ $exp->type }}">
                                {{ $catIcons[$exp->type] ?? '📦' }}
                                {{ $catLabels[$exp->type] ?? ucfirst($exp->type) }}
                            </span>
                        </td>
                        <td style="font-weight:700;color:#E24B4A;">
                            - {{ number_format($exp->amount, 0, ',', ' ') }} FCFA
                        </td>
                        <td style="font-size:12px;color:#9e8fc0;">
                            {{ \Carbon\Carbon::parse($exp->expense_date)->format('d/m/Y') }}
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-icon danger" title="Supprimer"
                                    onclick="deleteExpense({{ $exp->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="padding:40px;text-align:center;color:#b0a0d0;">
                        Aucune dépense enregistrée
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3" style="border-top:1px solid #f0ebff;">
            {{ $expenses->links() }}
        </div>
    </div>

    {{-- Top produits / services vendus ── --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">
                <i class="fas fa-trophy me-2" style="color:#EF9F27;"></i>
                Top ventes du mois
            </span>
        </div>

        <div style="padding:14px 0 4px 0;">
            <div style="padding:0 20px 8px;font-size:11px;font-weight:700;color:#9e8fc0;text-transform:uppercase;letter-spacing:0.06em;">
                Produits
            </div>
            @forelse($topProducts as $i => $p)
            <div class="top-row">
                <div class="top-rank">{{ $i + 1 }}</div>
                <div class="top-name">{{ $p->product->name ?? '—' }}</div>
                <div class="top-count">{{ $p->total_qty }} unités</div>
                <div class="top-amt">{{ number_format($p->total_revenue, 0, ',', ' ') }} FCFA</div>
            </div>
            @empty
            <div style="padding:20px;text-align:center;color:#b0a0d0;font-size:13px;">Aucune vente ce mois</div>
            @endforelse
        </div>

        <div style="padding:14px 0 4px 0;border-top:1px solid #f7f4ff;">
            <div style="padding:0 20px 8px;font-size:11px;font-weight:700;color:#9e8fc0;text-transform:uppercase;letter-spacing:0.06em;">
                Services
            </div>
            @forelse($topServices as $i => $s)
            <div class="top-row">
                <div class="top-rank">{{ $i + 1 }}</div>
                <div class="top-name">{{ $s->service->name ?? '—' }}</div>
                <div class="top-count">{{ $s->total_qty }}×</div>
                <div class="top-amt">{{ number_format($s->total_revenue, 0, ',', ' ') }} FCFA</div>
            </div>
            @empty
            <div style="padding:20px;text-align:center;color:#b0a0d0;font-size:13px;">Aucun service ce mois</div>
            @endforelse
        </div>

        {{-- Solde ── --}}
        <div style="margin:12px 16px;border-radius:12px;background:{{ $profit >= 0 ? '#EAF3DE' : '#FCEBEB' }};border:1px solid {{ $profit >= 0 ? '#c0dd97' : '#F7C1C1' }};">
            <div class="solde-box">
                <div class="solde-label">Solde net du mois</div>
                <div class="solde-value" style="color:{{ $profit >= 0 ? '#3B6D11' : '#A32D2D' }};">
                    {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 0, ',', ' ') }} FCFA
                </div>
                <div class="solde-sub">CA {{ number_format($totalRevenue, 0, ',', ' ') }} − Dépenses {{ number_format($totalExpenses, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>

</div>

{{-- ── Paiements par méthode ── --}}
{{-- <div class="panel">
    <div class="panel-header">
        <span class="panel-title">
            <i class="fas fa-credit-card me-2" style="color:var(--violet);"></i>
            Répartition par mode de paiement
        </span>
    </div>
    <div style="padding:20px;">
        <div class="chart-container" style="height:200px;">
            <canvas id="paymentChart"></canvas>
        </div>
    </div>
</div> --}}

{{-- ── Modal Nouvelle Dépense ── --}}
<div class="modal fade" id="modalDepense" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0">
                    <i class="fas fa-minus-circle me-2"></i>Nouvelle dépense
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="form-label-s">Intitulé *</label>
                        <input type="text" class="form-control-s" name="title"
                               placeholder="Ex: Achat de matériaux, Salaire livreur..." required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-s">Montant (FCFA) *</label>
                            <input type="number" class="form-control-s" name="amount"
                                   placeholder="0" min="0" step="500" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label-s">Type *</label>
                            <select class="form-control-s" name="type" required>
                                <option value="livraison">🚚 Livraison</option>
                                <option value="materiel">🔧 Matériel</option>
                                <option value="salaire">👤 Salaire</option>
                                <option value="autre">📦 Autre</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label-s">Date *</label>
                        <input type="date" class="form-control-s" name="expense_date"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-violet px-4">
                        <i class="fas fa-check me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal suppression ── --}}
<div class="modal fade" id="modalDeleteExp" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body p-4 text-center">
                <div style="font-size:36px;margin-bottom:10px;">🗑️</div>
                <h5 class="fw-bold mb-2">Supprimer cette dépense ?</h5>
                <p style="font-size:13px;color:#9e8fc0;">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 justify-content-center gap-3">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                <form id="delete-exp-form" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-violet px-4" style="background:#E24B4A;">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ── Données injectées depuis Laravel ────────────────────────────────────────
const chartLabels    = @json($chartLabels);
const chartRevenue   = @json($chartRevenue);
const chartExpenses  = @json($chartExpenses);
const paymentData    = @json($paymentData);
const expenseDonutData = @json($expenseDonutData);

// ── Graphique CA vs Dépenses ─────────────────────────────────────────────────
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [
            {
                label: 'CA',
                data: chartRevenue,
                borderColor: '#6B46C1',
                backgroundColor: 'rgba(107,70,193,0.08)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#6B46C1',
                pointRadius: 4,
            },
            {
                label: 'Dépenses',
                data: chartExpenses,
                borderColor: '#E24B4A',
                backgroundColor: 'rgba(226,75,74,0.06)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#E24B4A',
                pointRadius: 4,
                borderDash: [6, 3],
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.dataset.label + ' : ' + ctx.parsed.y.toLocaleString('fr-FR') + ' FCFA'
                }
            }
        },
        scales: {
            y: {
                grid: { color: '#f7f4ff' },
                ticks: {
                    color: '#9e8fc0',
                    callback: v => (v / 1000) + 'k'
                }
            },
            x: { grid: { display: false }, ticks: { color: '#9e8fc0' } }
        }
    }
});

// ── Donut dépenses ───────────────────────────────────────────────────────────
const donutCtx = document.getElementById('expenseDonut').getContext('2d');
new Chart(donutCtx, {
    type: 'doughnut',
    data: {
        labels: expenseDonutData.labels,
        datasets: [{
            data: expenseDonutData.values,
            backgroundColor: ['#7F77DD', '#EF9F27', '#639922', '#9e8fc0'],
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.label + ' : ' + ctx.parsed.toLocaleString('fr-FR') + ' FCFA'
                }
            }
        }
    }
});

// ── Barres modes de paiement ─────────────────────────────────────────────────
const payCtx = document.getElementById('paymentChart').getContext('2d');
new Chart(payCtx, {
    type: 'bar',
    data: {
        labels: paymentData.labels,
        datasets: [{
            label: 'Montant encaissé (FCFA)',
            data: paymentData.values,
            backgroundColor: ['#6B46C1', '#EF9F27', '#639922'],
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.parsed.y.toLocaleString('fr-FR') + ' FCFA'
                }
            }
        },
        scales: {
            y: {
                grid: { color: '#f7f4ff' },
                ticks: { color: '#9e8fc0', callback: v => (v / 1000) + 'k' }
            },
            x: { grid: { display: false }, ticks: { color: '#9e8fc0' } }
        }
    }
});

// ── Filtre dépenses ──────────────────────────────────────────────────────────
function filterExpenses() {
    const q = document.getElementById('exp-search').value.toLowerCase();
    const t = document.getElementById('exp-type-filter').value;
    document.querySelectorAll('#exp-tbody tr').forEach(row => {
        if (!row.dataset.title) return;
        const ok = row.dataset.title.includes(q) && (!t || row.dataset.type === t);
        row.style.display = ok ? '' : 'none';
    });
}

// ── Suppression dépense ──────────────────────────────────────────────────────
function deleteExpense(id) {
    document.getElementById('delete-exp-form').action = `/finances/depenses/${id}`;
    new bootstrap.Modal(document.getElementById('modalDeleteExp')).show();
}

// ── Sélecteur de période ──────────────────────────────────────────────────────
function setPeriod(btn, period) {
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    window.location.href = `{{ route('expenses.index') }}?period=${period}`;
}

// ── Toast session ─────────────────────────────────────────────────────────────
@if(session('success'))
    const t = document.getElementById('fin-toast');
    t.textContent = '✓ {{ session("success") }}';
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 3000);
@endif
</script>

@endsection