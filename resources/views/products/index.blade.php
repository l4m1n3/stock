@extends('layouts.app')

@section('title', 'Produits & Services')

@section('content')

<style>
    :root { --violet: #6B46C1; --violet-light: #EEEDFE; --violet-hover: #5A3AA6; }

    .ps-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }

    /* KPIs */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
    .kpi-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #e8e4f7; padding: 18px 20px;
        box-shadow: 0 2px 12px rgba(107,70,193,0.06);
    }
    .kpi-label { font-size: 12px; font-weight: 600; color: #9e8fc0; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
    .kpi-value { font-size: 26px; font-weight: 700; color: #2d2d2d; line-height: 1; }
    .kpi-sub   { font-size: 12px; color: #b0a0d0; margin-top: 4px; }

    /* Tabs nav */
    .tabs-nav {
        display: flex; gap: 4px;
        background: #f7f4ff; border-radius: 12px;
        padding: 4px; width: fit-content; margin-bottom: 20px;
    }
    .tab-nav-btn {
        padding: 8px 22px; border-radius: 9px;
        font-size: 13px; font-weight: 600;
        border: none; background: transparent;
        color: #9e8fc0; cursor: pointer;
        transition: all 0.18s;
    }
    .tab-nav-btn.active {
        background: #fff; color: var(--violet);
        box-shadow: 0 2px 8px rgba(107,70,193,0.12);
    }

    /* Panel */
    .panel {
        background: #fff; border-radius: 16px;
        border: 1px solid #e8e4f7;
        box-shadow: 0 4px 20px rgba(107,70,193,0.07);
        overflow: hidden; margin-bottom: 24px;
    }
    .panel-header {
        padding: 16px 20px; border-bottom: 1px solid #f0ebff;
        background: #faf8ff;
        display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }
    .panel-title { font-size: 15px; font-weight: 600; color: #2d2d2d; }

    /* Toolbar */
    .toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .toolbar input[type=text] {
        padding: 8px 14px; border: 1px solid #e0d9f7;
        border-radius: 10px; font-size: 13px;
        background: #faf8ff; color: #2d2d2d;
        outline: none; width: 210px; transition: border-color 0.15s;
    }
    .toolbar input:focus { border-color: var(--violet); }
    .toolbar select {
        padding: 8px 12px; border: 1px solid #e0d9f7;
        border-radius: 10px; font-size: 13px;
        background: #faf8ff; color: #2d2d2d; outline: none; cursor: pointer;
    }

    /* Cards grille produits */
    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 18px; padding: 20px;
    }
    .product-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #e8e4f7;
        box-shadow: 0 2px 10px rgba(107,70,193,0.05);
        overflow: hidden; transition: box-shadow 0.18s, transform 0.18s;
        display: flex; flex-direction: column;
    }
    .product-card:hover {
        box-shadow: 0 8px 28px rgba(107,70,193,0.14);
        transform: translateY(-2px);
    }
    .card-thumb {
        height: 110px; background: linear-gradient(135deg, #EEEDFE 0%, #d8d3f7 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 38px; position: relative;
    }
    .card-stock-badge {
        position: absolute; top: 10px; right: 10px;
        font-size: 11px; font-weight: 600; padding: 3px 10px;
        border-radius: 99px;
    }
    .badge-ok       { background: #EAF3DE; color: #3B6D11; }
    .badge-low      { background: #FAEEDA; color: #854F0B; }
    .badge-critical { background: #FCEBEB; color: #A32D2D; }
    .card-body-ps { padding: 14px 16px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
    .card-name  { font-size: 14px; font-weight: 700; color: #2d2d2d; line-height: 1.3; }
    .card-desc  { font-size: 12px; color: #9e8fc0; flex: 1; }
    .card-price { font-size: 16px; font-weight: 800; color: var(--violet); margin-top: 6px; }
    .card-footer-ps {
        display: flex; gap: 6px; padding: 12px 16px;
        border-top: 1px solid #f0ebff;
    }
    .btn-card {
        flex: 1; padding: 7px 0; border-radius: 8px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        border: 1px solid #e0d9f7; background: #faf8ff;
        color: #7c6fa0; transition: all 0.15s;
        display: flex; align-items: center; justify-content: center; gap: 5px;
    }
    .btn-card:hover { background: var(--violet-light); color: var(--violet); border-color: var(--violet); }
    .btn-card.primary { background: var(--violet); color: white; border-color: var(--violet); }
    .btn-card.primary:hover { background: var(--violet-hover); }

    /* Table services */
    .svc-table { width: 100%; border-collapse: collapse; }
    .svc-table thead tr { background: #f7f4ff; }
    .svc-table th {
        padding: 12px 18px; font-size: 12px; font-weight: 600;
        color: var(--violet); text-transform: uppercase;
        letter-spacing: 0.05em; border-bottom: 1px solid #f0ebff; white-space: nowrap;
    }
    .svc-table td {
        padding: 13px 18px; font-size: 14px; color: #2d2d2d;
        border-bottom: 1px solid #f7f4ff; vertical-align: middle;
    }
    .svc-table tbody tr:last-child td { border-bottom: none; }
    .svc-table tbody tr:hover { background: #faf8ff; }

    .badge-type {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 99px;
        font-size: 12px; font-weight: 600;
    }
    .type-simple     { background: #EAF3DE; color: #3B6D11; }
    .type-semi_prive { background: #FAEEDA; color: #854F0B; }
    .type-prive      { background: var(--violet-light); color: var(--violet); }

    /* Actions */
    .action-btns { display: flex; gap: 6px; }
    .btn-icon {
        width: 30px; height: 30px; border-radius: 8px;
        border: 1px solid #e0d9f7; background: #faf8ff;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        color: #9e8fc0; font-size: 13px; transition: all 0.15s;
    }
    .btn-icon:hover { background: var(--violet-light); color: var(--violet); border-color: var(--violet); }
    .btn-icon.danger:hover { background: #FCEBEB; color: #A32D2D; border-color: #E24B4A; }

    /* Boutons */
    .btn-violet {
        padding: 9px 18px; background: var(--violet);
        color: white; border: none; border-radius: 10px;
        font-size: 13px; font-weight: 600; cursor: pointer;
        display: inline-flex; align-items: center; gap: 7px;
        transition: background 0.15s, transform 0.1s;
    }
    .btn-violet:hover { background: var(--violet-hover); transform: translateY(-1px); }

    /* Modal */
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

    /* Empty state */
    .empty-state { padding: 60px 20px; text-align: center; color: #b0a0d0; }
    .empty-icon { font-size: 48px; margin-bottom: 12px; }

    /* Toast */
    #ps-toast {
        position: fixed; top: 20px; right: 20px;
        background: #3B6D11; color: white;
        padding: 12px 22px; border-radius: 12px;
        font-size: 14px; font-weight: 500;
        display: none; z-index: 9999;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    /* Vue liste toggle */
    .view-toggle { display: flex; gap: 4px; }
    .view-btn {
        width: 32px; height: 32px; border-radius: 8px;
        border: 1px solid #e0d9f7; background: #faf8ff;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        color: #9e8fc0; font-size: 14px; transition: all 0.15s;
    }
    .view-btn.active { background: var(--violet-light); color: var(--violet); border-color: var(--violet); }

    /* Table produits (vue liste) */
    .prod-table { width: 100%; border-collapse: collapse; display: none; }
    .prod-table thead tr { background: #f7f4ff; }
    .prod-table th {
        padding: 12px 18px; font-size: 12px; font-weight: 600;
        color: var(--violet); text-transform: uppercase;
        letter-spacing: 0.05em; border-bottom: 1px solid #f0ebff; white-space: nowrap;
    }
    .prod-table td {
        padding: 13px 18px; font-size: 14px; color: #2d2d2d;
        border-bottom: 1px solid #f7f4ff; vertical-align: middle;
    }
    .prod-table tbody tr:last-child td { border-bottom: none; }
    .prod-table tbody tr:hover { background: #faf8ff; }

    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: 1fr 1fr; }
        .form-row { grid-template-columns: 1fr; }
        .cards-grid { grid-template-columns: 1fr 1fr; }
    }
</style>

<div id="ps-toast">✓ Enregistré avec succès</div>

{{-- ── Header ── --}}
<div class="ps-header">
    <div>
        <h4 class="fw-bold mb-0">Produits & Services</h4>
        <small class="text-muted">Catalogue complet — {{ now()->format('d/m/Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn-violet" style="background:#fff;color:var(--violet);border:1.5px solid var(--violet);"
            data-bs-toggle="modal" data-bs-target="#modalService">
            <i class="fas fa-concierge-bell"></i> Nouveau service
        </button>
        <button class="btn-violet" data-bs-toggle="modal" data-bs-target="#modalProduct">
            <i class="fas fa-plus"></i> Nouveau produit
        </button>
         <button class="btn-violet" data-bs-toggle="modal" data-bs-target="#modalConfection">
            <i class="fas fa-plus"></i>  Nouvelle confection
        </button>
    </div>
</div>

{{-- ── KPIs ── --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Total produits</div>
        <div class="kpi-value">{{ $products->count() }}</div>
        <div class="kpi-sub">références actives</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Total services</div>
        <div class="kpi-value" style="color:var(--violet);">{{ $services->count() }}</div>
        <div class="kpi-sub">prestations disponibles</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Valeur catalogue</div>
        <div class="kpi-value" style="color:#3B6D11;">
            {{ number_format($products->sum(fn($p) => $p->price * $p->stock_quantity), 0, ',', ' ') }}
        </div>
        <div class="kpi-sub">FCFA en stock</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Prix moyen</div>
        <div class="kpi-value">
            {{ $products->count() > 0 ? number_format($products->avg('price'), 0, ',', ' ') : '0' }}
        </div>
        <div class="kpi-sub">FCFA / produit</div>
    </div>
</div>

{{-- ── Tabs ── --}}
<div class="tabs-nav">
    <button class="tab-nav-btn active" onclick="switchSection(this,'produits')">
        <i class="fas fa-box me-1"></i> Produits ({{ $products->count() }})
    </button>
    <button class="tab-nav-btn" onclick="switchSection(this,'services')">
        <i class="fas fa-concierge-bell me-1"></i> Services ({{ $services->count() }})
    </button>
    <button class="tab-nav-btn" onclick="switchSection(this,'confections')">
        <i class="fas fa-concierge-bell me-1"></i> Confections ({{ $confections->count() }})
    </button>
</div>

{{-- ══════════════════════ SECTION PRODUITS ══════════════════════ --}}
<div id="section-produits">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title"><i class="fas fa-box me-2" style="color:var(--violet);"></i>Catalogue Produits</span>
            <div class="d-flex align-items-center gap-3">
                <div class="toolbar">
                    <input type="text" id="prod-search" placeholder="Rechercher..." oninput="filterProducts()">
                    <select id="prod-sort" onchange="filterProducts()">
                        <option value="name">Trier : Nom</option>
                        <option value="price_asc">Prix croissant</option>
                        <option value="price_desc">Prix décroissant</option>
                        <option value="stock">Stock</option>
                    </select>
                </div>
                <div class="view-toggle">
                    <button class="view-btn active" id="view-grid-btn" title="Vue grille" onclick="toggleView('grid')">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button class="view-btn" id="view-list-btn" title="Vue liste" onclick="toggleView('list')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Vue Grille --}}
        <div class="cards-grid" id="prod-grid">
            @forelse($products as $product)
            @php
                if ($product->stock_quantity === 0) { $sk = 'critical'; $si = '💀'; }
                elseif ($product->stock_quantity < ($product->alert_threshold ?? 10)) { $sk = 'low'; $si = '⚠️'; }
                else { $sk = 'ok'; $si = '✅'; }
                $icons = ['ok'=>'📦','low'=>'📦','critical'=>'📦'];
                $emojis = [1=>'🌸',2=>'🪴',3=>'🎁',4=>'🏺',5=>'🌺'];
                $em = $emojis[$product->id % 5 + 1] ?? '📦';
            @endphp
            <div class="product-card"
                 data-name="{{ strtolower($product->name) }}"
                 data-price="{{ $product->price }}"
                 data-stock="{{ $product->stock_quantity }}">
                <div class="card-thumb">
                    {{ $em }}
                    <span class="card-stock-badge badge-{{ $sk }}">
                        {{ $product->stock_quantity }} unités
                    </span>
                </div>
                <div class="card-body-ps">
                    <div class="card-name">{{ $product->name }}</div>
                    <div class="card-desc">{{ $product->description ?? 'Aucune description' }}</div>
                    <div class="card-price">{{ number_format($product->price, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="card-footer-ps">
                    <button class="btn-card" onclick="editProduct({{ $product->id }})">
                        <i class="fas fa-pen"></i> Modifier
                    </button>
                    <button class="btn-card primary" onclick="addStockQuick({{ $product->id }}, '{{ addslashes($product->name) }}')">
                        <i class="fas fa-plus"></i> Stock
                    </button>
                </div>
            </div>
            @empty
            <div class="empty-state" style="grid-column: 1/-1;">
                <div class="empty-icon">📦</div>
                <p>Aucun produit enregistré</p>
                <button class="btn-violet mt-2" data-bs-toggle="modal" data-bs-target="#modalProduct">
                    + Ajouter le premier produit
                </button>
            </div>
            @endforelse
        </div>

        {{-- Vue Liste --}}
        <div style="overflow-x:auto; display:none;" id="prod-list-wrap">
            <table class="prod-table" id="prod-list">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Description</th>
                        <th>Prix unitaire</th>
                        <th>Stock</th>
                        <th>Seuil alerte</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    @php
                        if ($product->stock_quantity === 0) { $sk = 'critical'; $sl = 'Rupture'; }
                        elseif ($product->stock_quantity < ($product->alert_threshold ?? 10)) { $sk = 'low'; $sl = 'Bas'; }
                        else { $sk = 'ok'; $sl = 'OK'; }
                    @endphp
                    <tr data-name="{{ strtolower($product->name) }}"
                        data-price="{{ $product->price }}"
                        data-stock="{{ $product->stock_quantity }}">
                        <td style="font-weight:600;">{{ $product->name }}</td>
                        <td style="color:#9e8fc0;font-size:13px;">{{ $product->description ?? '—' }}</td>
                        <td style="font-weight:700;color:var(--violet);">{{ number_format($product->price, 0, ',', ' ') }} FCFA</td>
                        <td style="font-weight:600;">{{ $product->stock_quantity }}</td>
                        <td style="color:#9e8fc0;">{{ $product->alert_threshold ?? '10' }}</td>
                        <td><span class="badge-type type-{{ $sk === 'ok' ? 'simple' : ($sk === 'low' ? 'semi_prive' : '') }}"
                                  style="{{ $sk === 'critical' ? 'background:#FCEBEB;color:#A32D2D;' : '' }}">
                            {{ $sl }}
                        </span></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-icon" onclick="editProduct({{ $product->id }})" title="Modifier"><i class="fas fa-pen"></i></button>
                                <button class="btn-icon" onclick="addStockQuick({{ $product->id }}, '{{ addslashes($product->name) }}')" title="Entrée stock"><i class="fas fa-plus"></i></button>
                                <button class="btn-icon danger" title="Supprimer"
                                    onclick="confirmDelete({{ $product->id }}, 'product')"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ══════════════════════ SECTION SERVICES ══════════════════════ --}}
<div id="section-services" style="display:none;">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title"><i class="fas fa-concierge-bell me-2" style="color:var(--violet);"></i>Catalogue Services</span>
            <div class="toolbar">
                <input type="text" id="svc-search" placeholder="Rechercher..." oninput="filterServices()">
                <select id="svc-type-filter" onchange="filterServices()">
                    <option value="">Tous les types</option>
                    <option value="simple">Simple</option>
                    <option value="semi_prive">Semi-privé</option>
                    <option value="prive">Privé</option>
                </select>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="svc-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Prix</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="svc-tbody">
                    @forelse($services as $service)
                    @php
                        $typeLabels = ['simple'=>'Simple','semi_prive'=>'Semi-privé','prive'=>'Privé'];
                        $typeIcons  = ['simple'=>'🌸','semi_prive'=>'💐','prive'=>'👑'];
                    @endphp
                    <tr data-name="{{ strtolower($service->name) }}" data-type="{{ $service->type }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="font-size:20px;">{{ $typeIcons[$service->type] ?? '🎁' }}</span>
                                <div>
                                    <div style="font-weight:600;">{{ $service->name }}</div>
                                    <div style="font-size:12px;color:#9e8fc0;">Prestation {{ $typeLabels[$service->type] ?? $service->type }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-type type-{{ $service->type }}">
                                {{ $typeLabels[$service->type] ?? $service->type }}
                            </span>
                        </td>
                        <td style="font-weight:700;color:var(--violet);">
                            {{ number_format($service->price, 0, ',', ' ') }} FCFA
                        </td>
                        <td style="font-size:13px;color:#9e8fc0;">
                            {{ $service->created_at->format('d/m/Y') }}
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-icon" onclick="editService({{ $service->id }})" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn-icon danger" title="Supprimer"
                                    onclick="confirmDelete({{ $service->id }}, 'service')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5">
                        <div class="empty-state">
                            <div class="empty-icon">🛎️</div>
                            <p>Aucun service enregistré</p>
                            <button class="btn-violet mt-2" data-bs-toggle="modal" data-bs-target="#modalService">
                                + Ajouter le premier service
                            </button>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
{{-- ══════════════════════ SECTION CONFECTIONS ══════════════════════ --}}
<div id="section-confections" style="display:none;">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">
                <i class="fas fa-scissors me-2" style="color:var(--violet);"></i>Catalogue Confections
            </span>
            <div class="toolbar">
                <input type="text" id="conf-search" placeholder="Rechercher..." oninput="filterConfections()">
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="svc-table">
                <thead>
                    <tr>
                        <th>Confection</th>
                        <th>Composition</th>
                        <th>Prix confection</th>
                        <th>Prix total estimé</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="conf-tbody">
                    @forelse($confections as $confection)
                    <tr data-name="{{ strtolower($confection->name) }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="font-size:20px;">🎀</span>
                                <div>
                                    <div style="font-weight:600;">{{ $confection->name }}</div>
                                    <div style="font-size:12px;color:#9e8fc0;">
                                        {{ $confection->description ?? 'Aucune description' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($confection->products->count())
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach($confection->products as $p)
                                    <span class="badge-type type-simple" style="font-size:11px;">
                                        {{ $p->name }} ×{{ $p->pivot->quantity }}
                                    </span>
                                @endforeach
                                </div>
                            @else
                                <span style="color:#b0a0d0;font-size:13px;">Aucun produit</span>
                            @endif
                        </td>
                        <td style="font-weight:700;color:var(--violet);">
                            {{ number_format($confection->making_price, 0, ',', ' ') }} FCFA
                        </td>
                        <td style="font-weight:700;color:#3B6D11;">
                            {{ number_format($confection->total_price, 0, ',', ' ') }} FCFA
                        </td>
                        <td style="font-size:13px;color:#9e8fc0;">
                            {{ $confection->created_at->format('d/m/Y') }}
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-icon" onclick="editConfection({{ $confection->id }})" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn-icon danger" title="Supprimer"
                                    onclick="confirmDelete({{ $confection->id }}, 'confection')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon">🎀</div>
                            <p>Aucune confection enregistrée</p>
                            <button class="btn-violet mt-2" data-bs-toggle="modal" data-bs-target="#modalConfection">
                                + Ajouter la première confection
                            </button>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ══════════════════════ MODAL : Nouveau/Edit Produit ══════════════════════ --}}
<div class="modal fade" id="modalProduct" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0" id="modal-product-title">
                    <i class="fas fa-box me-2"></i>Nouveau produit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="product-form" action="{{ route('products.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="product-method" value="POST">
                <input type="hidden" name="product_id" id="product-edit-id" value="">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="form-label-s">Nom du produit *</label>
                        <input type="text" class="form-control-s" name="name" id="p-name"
                               placeholder="Ex: Bouquet de roses rouges" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-s">Description</label>
                        <textarea class="form-control-s" name="description" id="p-description"
                                  rows="2" placeholder="Description courte du produit..."></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-s">Prix unitaire (FCFA) *</label>
                            <input type="number" class="form-control-s" name="price" id="p-price"
                                   placeholder="0" min="0" step="500" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label-s">Stock initial</label>
                            <input type="number" class="form-control-s" name="stock_quantity" id="p-stock"
                                   placeholder="0" min="0" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label-s">Seuil d'alerte stock</label>
                        <input type="number" class="form-control-s" name="alert_threshold" id="p-threshold"
                               placeholder="10" min="0" value="10">
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

{{-- ══════════════════════ MODAL : Nouveau/Edit Service ══════════════════════ --}}
<div class="modal fade" id="modalService" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0" id="modal-service-title">
                    <i class="fas fa-concierge-bell me-2"></i>Nouveau service
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="service-form" action="{{ route('services.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="service-method" value="POST">
                <input type="hidden" name="service_id" id="service-edit-id" value="">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="form-label-s">Nom du service *</label>
                        <input type="text" class="form-control-s" name="name" id="s-name"
                               placeholder="Ex: Bouquet privé avec livraison" required>
                    </div>
                    <div class="form-row">
                        {{-- <div class="form-group">
                            <label class="form-label-s">Type de service *</label>
                            <select class="form-control-s" name="type" id="s-type" required>
                                <option value="simple">🌸 Simple</option>
                                <option value="semi_prive">💐 Semi-privé</option>
                                <option value="prive">👑 Privé</option>
                            </select>
                        </div> --}}
                        <div class="form-group">
                            <label class="form-label-s">Prix (FCFA) *</label>
                            <input type="number" class="form-control-s" name="price" id="s-price"
                                   placeholder="0" min="0" step="500" required>
                        </div>
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

{{-- ══════════════════════ MODAL : Nouvelle/Edit Confection ══════════════════════ --}}
<div class="modal fade" id="modalConfection" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0" id="modal-confection-title">
                    <i class="fas fa-scissors me-2"></i>Nouvelle confection
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="confection-form" action="{{ route('confections.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="confection-method" value="POST">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="form-label-s">Nom de la confection *</label>
                        <input type="text" class="form-control-s" name="name" id="c-name"
                               placeholder="Ex: Bouquet mariage romantique" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-s">Description</label>
                        <textarea class="form-control-s" name="description" id="c-description"
                                  rows="2" placeholder="Description..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label-s">Prix de confection (main-d'œuvre) (FCFA) *</label>
                        <input type="number" class="form-control-s" name="making_price" id="c-making-price"
                               placeholder="0" min="0" step="500" required>
                    </div>

                    {{-- Composition en produits --}}
                    <div class="form-group">
                        <label class="form-label-s">Composition (produits)</label>
                        <div id="confection-products-list" style="display:flex;flex-direction:column;gap:8px;margin-bottom:8px;">
                            {{-- Lignes ajoutées dynamiquement --}}
                        </div>
                        <button type="button" class="btn-card" style="width:auto;padding:7px 16px;"
                                onclick="addConfectionProductRow()">
                            <i class="fas fa-plus"></i> Ajouter un produit
                        </button>
                    </div>

                    {{-- Récap prix total --}}
                    <div style="background:#f7f4ff;border-radius:10px;padding:12px 16px;margin-top:8px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:13px;color:#9e8fc0;font-weight:600;">Prix total estimé</span>
                        <span id="conf-total-price" style="font-size:18px;font-weight:800;color:var(--violet);">0 FCFA</span>
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

{{-- ══════════════════════ MODAL : Entrée stock rapide ══════════════════════ --}}
<div class="modal fade" id="modalQuickStock" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0" id="quick-stock-title">
                    <i class="fas fa-plus-circle me-2"></i>Entrée stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stock.movement.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="in">
                <input type="hidden" name="product_id" id="qs-product-id">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="form-label-s">Quantité à ajouter</label>
                        <input type="number" class="form-control-s" name="quantity" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-s">Motif</label>
                        <input type="text" class="form-control-s" name="reason" placeholder="Réapprovisionnement...">
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-violet"><i class="fas fa-check me-1"></i>Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════ MODAL : Confirmation suppression ══════════════════════ --}}
<div class="modal fade" id="modalDelete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body p-4 text-center">
                <div style="font-size:40px;margin-bottom:12px;">🗑️</div>
                <h5 class="fw-bold mb-2">Confirmer la suppression</h5>
                <p style="font-size:14px;color:#9e8fc0;">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 justify-content-center gap-3">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                <form id="delete-form" method="POST" style="display:inline;">
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
const productsData = @json($products->keyBy('id'));
const servicesData = @json($services->keyBy('id'));

// ── Tabs sections ────────────────────────────────────────────────────────────
function switchSection(btn, section) {
    document.querySelectorAll('.tab-nav-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('section-produits').style.display = section === 'produits' ? '' : 'none';
    document.getElementById('section-services').style.display = section === 'services' ? '' : 'none';
}

// ── Vue grille / liste produits ──────────────────────────────────────────────
function toggleView(view) {
    const grid    = document.getElementById('prod-grid');
    const listWrap= document.getElementById('prod-list-wrap');
    const listTbl = document.getElementById('prod-list');
    if (view === 'grid') {
        grid.style.display = '';
        listWrap.style.display = 'none';
        listTbl.style.display = 'none';
        document.getElementById('view-grid-btn').classList.add('active');
        document.getElementById('view-list-btn').classList.remove('active');
    } else {
        grid.style.display = 'none';
        listWrap.style.display = 'block';
        listTbl.style.display = 'table';
        document.getElementById('view-grid-btn').classList.remove('active');
        document.getElementById('view-list-btn').classList.add('active');
    }
}

// ── Filtre / tri produits ────────────────────────────────────────────────────
function filterProducts() {
    const q    = document.getElementById('prod-search').value.toLowerCase();
    const sort = document.getElementById('prod-sort').value;

    // Filtre grille
    const cards = Array.from(document.querySelectorAll('#prod-grid .product-card'));
    cards.forEach(c => { c.style.display = c.dataset.name.includes(q) ? '' : 'none'; });

    // Filtre liste
    const rows = Array.from(document.querySelectorAll('#prod-list tbody tr'));
    rows.forEach(r => { r.style.display = r.dataset.name.includes(q) ? '' : 'none'; });
}

// ── Filtre services ──────────────────────────────────────────────────────────
function filterServices() {
    const q = document.getElementById('svc-search').value.toLowerCase();
    const t = document.getElementById('svc-type-filter').value;
    document.querySelectorAll('#svc-tbody tr').forEach(row => {
        const nameOk = row.dataset.name && row.dataset.name.includes(q);
        const typeOk = !t || row.dataset.type === t;
        row.style.display = (nameOk && typeOk) ? '' : 'none';
    });
}

// ── Edit produit ──────────────────────────────────────────────────────────────
function editProduct(id) {
    const p = productsData[id];
    if (!p) return;
    document.getElementById('modal-product-title').innerHTML = '<i class="fas fa-pen me-2"></i>Modifier ' + p.name;
    document.getElementById('product-form').action = `/produits/${id}`;
    document.getElementById('product-method').value = 'PUT';
    document.getElementById('product-edit-id').value = id;
    document.getElementById('p-name').value         = p.name;
    document.getElementById('p-description').value  = p.description || '';
    document.getElementById('p-price').value        = p.price;
    document.getElementById('p-stock').value        = p.stock_quantity;
    document.getElementById('p-threshold').value    = p.alert_threshold || 10;
    new bootstrap.Modal(document.getElementById('modalProduct')).show();
}

// ── Edit service ──────────────────────────────────────────────────────────────
function editService(id) {
    const s = servicesData[id];
    if (!s) return;
    document.getElementById('modal-service-title').innerHTML = '<i class="fas fa-pen me-2"></i>Modifier ' + s.name;
    document.getElementById('service-form').action = `/services/${id}`;
    document.getElementById('service-method').value = 'PUT';
    document.getElementById('service-edit-id').value = id;
    document.getElementById('s-name').value  = s.name;
    // document.getElementById('s-type').value  = s.type;
    document.getElementById('s-price').value = s.price;
    new bootstrap.Modal(document.getElementById('modalService')).show();
}

// ── Entrée stock rapide ──────────────────────────────────────────────────────
function addStockQuick(id, name) {
    document.getElementById('qs-product-id').value = id;
    document.getElementById('quick-stock-title').innerHTML = '<i class="fas fa-plus-circle me-2"></i>' + name;
    new bootstrap.Modal(document.getElementById('modalQuickStock')).show();
}

// ── Supprimer ─────────────────────────────────────────────────────────────────
function confirmDelete(id, type) {
    const url = type === 'product' ? `/produits/${id}` : `/services/${id}`;
    document.getElementById('delete-form').action = url;
    new bootstrap.Modal(document.getElementById('modalDelete')).show();
}

// ── Toast session ─────────────────────────────────────────────────────────────
@if(session('success'))
    const t = document.getElementById('ps-toast');
    t.textContent = '✓ {{ session("success") }}';
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 3000);
@endif

// ── Données confections ──────────────────────────────────────────────────────
const confectionsData = @json($confections->load('products')->keyBy('id'));

// ── Tab confections ──────────────────────────────────────────────────────────
// Mettre à jour switchSection pour gérer la 3e section :
function switchSection(btn, section) {
    document.querySelectorAll('.tab-nav-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('section-produits').style.display   = section === 'produits'    ? '' : 'none';
    document.getElementById('section-services').style.display   = section === 'services'    ? '' : 'none';
    document.getElementById('section-confections').style.display = section === 'confections' ? '' : 'none';
}

// ── Filtre confections ───────────────────────────────────────────────────────
function filterConfections() {
    const q = document.getElementById('conf-search').value.toLowerCase();
    document.querySelectorAll('#conf-tbody tr').forEach(row => {
        row.style.display = (row.dataset.name && row.dataset.name.includes(q)) ? '' : 'none';
    });
}

// ── Composition : ajouter une ligne produit ──────────────────────────────────
function addConfectionProductRow(productId = '', quantity = 1) {
    const list = document.getElementById('confection-products-list');
    const idx  = list.children.length;
    const options = Object.values(productsData).map(p =>
        `<option value="${p.id}" data-price="${p.price}" ${p.id == productId ? 'selected' : ''}>${p.name} — ${Number(p.price).toLocaleString()} FCFA</option>`
    ).join('');

    const row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:8px;align-items:center;';
    row.innerHTML = `
        <select class="form-control-s conf-product-select" name="products[${idx}][id]"
                style="flex:1;" onchange="recalcConfTotal()">
            <option value="">— Choisir un produit —</option>
            ${options}
        </select>
        <input type="number" class="form-control-s conf-qty-input" name="products[${idx}][quantity]"
               value="${quantity}" min="1" style="width:80px;" oninput="recalcConfTotal()">
        <button type="button" class="btn-icon danger" onclick="this.parentElement.remove();recalcConfTotal();"
                style="flex-shrink:0;"><i class="fas fa-times"></i></button>
    `;
    list.appendChild(row);
    recalcConfTotal();
}

function recalcConfTotal() {
    let total = parseFloat(document.getElementById('c-making-price').value) || 0;
    document.querySelectorAll('#confection-products-list > div').forEach(row => {
        const sel = row.querySelector('.conf-product-select');
        const qty = parseFloat(row.querySelector('.conf-qty-input').value) || 0;
        if (sel && sel.value) {
            const price = parseFloat(sel.selectedOptions[0]?.dataset.price) || 0;
            total += price * qty;
        }
    });
    document.getElementById('conf-total-price').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

// Recalc quand making_price change
document.getElementById('c-making-price')?.addEventListener('input', recalcConfTotal);

// ── Edit confection ──────────────────────────────────────────────────────────
function editConfection(id) {
    const c = confectionsData[id];
    if (!c) return;
    document.getElementById('modal-confection-title').innerHTML = '<i class="fas fa-pen me-2"></i>Modifier ' + c.name;
    document.getElementById('confection-form').action = `/confections/${id}`;
    document.getElementById('confection-method').value = 'PUT';
    document.getElementById('c-name').value          = c.name;
    document.getElementById('c-description').value   = c.description || '';
    document.getElementById('c-making-price').value  = c.making_price;

    // Vider et recharger les lignes produits
    document.getElementById('confection-products-list').innerHTML = '';
    if (c.products) {
        c.products.forEach(p => addConfectionProductRow(p.id, p.pivot.quantity));
    }
    recalcConfTotal();
    new bootstrap.Modal(document.getElementById('modalConfection')).show();
}

// ── Réinitialiser le modal confection à l'ouverture (nouveau) ───────────────
document.getElementById('modalConfection').addEventListener('show.bs.modal', function(e) {
    if (!e.relatedTarget) return; // vient de editConfection, déjà peuplé
    document.getElementById('modal-confection-title').innerHTML = '<i class="fas fa-scissors me-2"></i>Nouvelle confection';
    document.getElementById('confection-form').action = "{{ route('confections.store') }}";
    document.getElementById('confection-method').value = 'POST';
    document.getElementById('c-name').value = '';
    document.getElementById('c-description').value = '';
    document.getElementById('c-making-price').value = '';
    document.getElementById('confection-products-list').innerHTML = '';
    recalcConfTotal();
});

// ── Étendre confirmDelete pour les confections ───────────────────────────────
function confirmDelete(id, type) {
    const urls = { product: `/produits/${id}`, service: `/services/${id}`, confection: `/confections/${id}` };
    document.getElementById('delete-form').action = urls[type];
    new bootstrap.Modal(document.getElementById('modalDelete')).show();
}
</script>

@endsection