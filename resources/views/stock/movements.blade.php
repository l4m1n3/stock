@extends('layouts.app')

@section('title', 'Mouvements de Stock')

@section('page-title', 'Mouvements de Stock')

@section('content')

<style>
    :root { --violet: #6B46C1; --violet-light: #EEEDFE; --violet-hover: #5A3AA6; }

    .mvt-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }

    /* KPIs */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
    .kpi-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e8e4f7;
        padding: 18px 20px;
        box-shadow: 0 2px 12px rgba(107,70,193,0.06);
    }
    .kpi-label { font-size: 12px; font-weight: 600; color: #9e8fc0; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
    .kpi-value { font-size: 28px; font-weight: 700; color: #2d2d2d; line-height: 1; }
    .kpi-sub   { font-size: 12px; color: #b0a0d0; margin-top: 4px; }

    /* Panel */
    .panel {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e8e4f7;
        box-shadow: 0 4px 20px rgba(107,70,193,0.07);
        overflow: hidden;
        margin-bottom: 24px;
    }
    .panel-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f0ebff;
        background: #faf8ff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .panel-title { font-size: 15px; font-weight: 600; color: #2d2d2d; }

    /* Toolbar */
    .toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .toolbar input[type=text], .toolbar input[type=date] {
        padding: 8px 14px;
        border: 1px solid #e0d9f7;
        border-radius: 10px;
        font-size: 13px;
        background: #faf8ff;
        color: #2d2d2d;
        outline: none;
        transition: border-color 0.15s;
    }
    .toolbar input[type=text] { width: 220px; }
    .toolbar input:focus { border-color: var(--violet); }
    .toolbar select {
        padding: 8px 12px;
        border: 1px solid #e0d9f7;
        border-radius: 10px;
        font-size: 13px;
        background: #faf8ff;
        color: #2d2d2d;
        outline: none;
        cursor: pointer;
    }

    /* Table */
    .mvt-table { width: 100%; border-collapse: collapse; }
    .mvt-table thead tr { background: #f7f4ff; }
    .mvt-table th {
        padding: 12px 18px;
        font-size: 12px;
        font-weight: 600;
        color: var(--violet);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #f0ebff;
        white-space: nowrap;
    }
    .mvt-table td {
        padding: 13px 18px;
        font-size: 14px;
        color: #2d2d2d;
        border-bottom: 1px solid #f7f4ff;
        vertical-align: middle;
    }
    .mvt-table tbody tr:last-child td { border-bottom: none; }
    .mvt-table tbody tr:hover { background: #faf8ff; }

    /* Badge type */
    .badge-type {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px; border-radius: 99px;
        font-size: 12px; font-weight: 700; white-space: nowrap;
    }
    .badge-in     { background: #EAF3DE; color: #3B6D11; }
    .badge-out    { background: #FAEEDA; color: #854F0B; }
    .badge-loss   { background: #FCEBEB; color: #A32D2D; }

    .badge-type-icon {
        width: 18px; height: 18px;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 800;
    }
    .icon-in   { background: #639922; color: #fff; }
    .icon-out  { background: #BA7517; color: #fff; }
    .icon-loss { background: #E24B4A; color: #fff; }

    /* Qty */
    .qty-in   { color: #3B6D11; font-weight: 700; font-size: 15px; }
    .qty-out  { color: #854F0B; font-weight: 700; font-size: 15px; }
    .qty-loss { color: #A32D2D; font-weight: 700; font-size: 15px; }

    /* Pagination */
    .pagination-wrap {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 20px;
        border-top: 1px solid #f0ebff;
        font-size: 13px; color: #9e8fc0;
    }
    .pagination-wrap .page-links { display: flex; gap: 6px; }
    .page-btn {
        width: 32px; height: 32px;
        border-radius: 8px;
        border: 1px solid #e0d9f7;
        background: #faf8ff;
        color: #6B46C1;
        font-size: 13px; font-weight: 600;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; text-decoration: none;
        transition: all 0.15s;
    }
    .page-btn:hover, .page-btn.active {
        background: var(--violet); color: white; border-color: var(--violet);
    }

    /* Boutons */
    .btn-violet {
        padding: 9px 18px;
        background: var(--violet); color: white; border: none;
        border-radius: 10px; font-size: 13px; font-weight: 600;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 7px;
        transition: background 0.15s, transform 0.1s;
        text-decoration: none;
    }
    .btn-violet:hover { background: var(--violet-hover); color: white; transform: translateY(-1px); }
    .btn-outline-violet {
        padding: 9px 18px;
        background: transparent; color: var(--violet);
        border: 1.5px solid var(--violet);
        border-radius: 10px; font-size: 13px; font-weight: 600;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 7px;
        transition: all 0.15s; text-decoration: none;
    }
    .btn-outline-violet:hover { background: var(--violet-light); }

    /* Empty state */
    .empty-state {
        padding: 60px 20px; text-align: center; color: #b0a0d0;
    }
    .empty-icon { font-size: 48px; margin-bottom: 14px; opacity: 0.35; }
    .empty-state p { font-size: 15px; margin: 0; }

    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: 1fr 1fr; }
    }
</style>

{{-- ── Header ── --}}
<div class="mvt-header">
    <div>
        <h4 class="fw-bold mb-0">Mouvements de Stock</h4>
        <small class="text-muted">Historique complet des entrées, sorties et pertes</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stock.index') }}" class="btn-outline-violet">
            <i class="fas fa-arrow-left"></i> Retour au stock
        </a>
        <button class="btn-violet" data-bs-toggle="modal" data-bs-target="#modalEntree">
            <i class="fas fa-plus"></i> Nouveau mouvement
        </button>
    </div>
</div>

{{-- ── KPIs ── --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Total mouvements</div>
        <div class="kpi-value">{{ $totalMovements }}</div>
        <div class="kpi-sub">depuis le début</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Entrées (30j)</div>
        <div class="kpi-value" style="color:#3B6D11;">{{ $inCount }}</div>
        <div class="kpi-sub">+{{ $inQty }} unités reçues</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Sorties (30j)</div>
        <div class="kpi-value" style="color:#854F0B;">{{ $outCount }}</div>
        <div class="kpi-sub">-{{ $outQty }} unités sorties</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Pertes / Casses (30j)</div>
        <div class="kpi-value" style="color:#E24B4A;">{{ $lossCount }}</div>
        <div class="kpi-sub">-{{ $lossQty }} unités perdues</div>
    </div>
</div>

{{-- ── Table des mouvements ── --}}
<div class="panel">
    <div class="panel-header">
        <span class="panel-title">
            <i class="fas fa-history me-2" style="color:var(--violet);"></i>
            Historique des mouvements
        </span>
        <div class="toolbar">
            <input type="text" id="mvt-search" placeholder="Rechercher produit, motif..." oninput="mvtFilter()">
            <select id="mvt-filter-type" onchange="mvtFilter()">
                <option value="">Tous les types</option>
                <option value="in">Entrées</option>
                <option value="out">Sorties</option>
                <option value="loss">Pertes</option>
            </select>
            <input type="date" id="mvt-filter-date" onchange="mvtFilter()" title="Filtrer par date">
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="mvt-table" id="mvt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Produit</th>
                    <th>Type</th>
                    <th>Quantité</th>
                    <th>Motif</th>
                    <th>Enregistré par</th>
                </tr>
            </thead>
            <tbody id="mvt-tbody">
                @forelse($movements as $mvt)
                @php
                    $typeLabels = ['in' => 'Entrée', 'out' => 'Sortie', 'loss' => 'Perte / Casse'];
                    $typeIcons  = ['in' => '+', 'out' => '−', 'loss' => '✕'];
                    $dateStr    = $mvt->created_at->format('Y-m-d');
                @endphp
                <tr
                    data-type="{{ $mvt->type }}"
                    data-name="{{ strtolower($mvt->product->name ?? '') }}"
                    data-reason="{{ strtolower($mvt->reason ?? '') }}"
                    data-date="{{ $dateStr }}"
                >
                    <td style="color:#b0a0d0;font-size:13px;">#{{ $mvt->id }}</td>
                    <td>
                        <div style="font-weight:600;font-size:13px;">{{ $mvt->created_at->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:#b0a0d0;">{{ $mvt->created_at->format('H:i') }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600;">{{ $mvt->product->name ?? '—' }}</div>
                    </td>
                    <td>
                        <span class="badge-type badge-{{ $mvt->type }}">
                            <span class="badge-type-icon icon-{{ $mvt->type }}">{{ $typeIcons[$mvt->type] }}</span>
                            {{ $typeLabels[$mvt->type] ?? $mvt->type }}
                        </span>
                    </td>
                    <td>
                        <span class="qty-{{ $mvt->type }}">
                            {{ $mvt->type === 'in' ? '+' : '−' }}{{ $mvt->quantity }}
                        </span>
                    </td>
                    <td style="color:#6b6b6b;font-size:13px;">
                        {{ $mvt->reason ?? '—' }}
                    </td>
                    <td style="font-size:13px;color:#9e8fc0;">
                        {{ $mvt->user->name ?? 'Système' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                            <p>Aucun mouvement de stock enregistré.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($movements->hasPages())
    <div class="pagination-wrap">
        <span>
            Affichage {{ $movements->firstItem() }}–{{ $movements->lastItem() }}
            sur {{ $movements->total() }} mouvements
        </span>
        <div class="page-links">
            @if($movements->onFirstPage())
                <span class="page-btn" style="opacity:.4;cursor:default;">‹</span>
            @else
                <a class="page-btn" href="{{ $movements->previousPageUrl() }}">‹</a>
            @endif

            @foreach($movements->getUrlRange(1, $movements->lastPage()) as $page => $url)
                <a class="page-btn {{ $movements->currentPage() === $page ? 'active' : '' }}"
                   href="{{ $url }}">{{ $page }}</a>
            @endforeach

            @if($movements->hasMorePages())
                <a class="page-btn" href="{{ $movements->nextPageUrl() }}">›</a>
            @else
                <span class="page-btn" style="opacity:.4;cursor:default;">›</span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- ── Modal : Nouveau mouvement ── --}}
<div class="modal fade" id="modalEntree" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div style="background:linear-gradient(135deg,#6B46C1,#9F7AEA);border-radius:16px 16px 0 0;padding:16px 20px;"
                 class="d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>Nouveau mouvement de stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stock.movement.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label style="font-size:13px;font-weight:600;color:#5a4a7a;margin-bottom:5px;display:block;">Produit</label>
                        <select style="width:100%;padding:9px 14px;border:1px solid #e0d9f7;border-radius:10px;font-size:14px;color:#2d2d2d;outline:none;"
                                name="product_id" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (stock: {{ $p->stock_quantity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;" class="mb-3">
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#5a4a7a;margin-bottom:5px;display:block;">Type</label>
                            <select style="width:100%;padding:9px 14px;border:1px solid #e0d9f7;border-radius:10px;font-size:14px;color:#2d2d2d;outline:none;"
                                    name="type" required>
                                <option value="in">Entrée (+)</option>
                                <option value="out">Sortie (-)</option>
                                <option value="loss">Perte / Casse</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:13px;font-weight:600;color:#5a4a7a;margin-bottom:5px;display:block;">Quantité</label>
                            <input type="number"
                                   style="width:100%;padding:9px 14px;border:1px solid #e0d9f7;border-radius:10px;font-size:14px;color:#2d2d2d;outline:none;"
                                   name="quantity" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:13px;font-weight:600;color:#5a4a7a;margin-bottom:5px;display:block;">Motif (optionnel)</label>
                        <input type="text"
                               style="width:100%;padding:9px 14px;border:1px solid #e0d9f7;border-radius:10px;font-size:14px;color:#2d2d2d;outline:none;"
                               name="reason" placeholder="Ex: Réapprovisionnement, vente directe...">
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

<script>
function mvtFilter() {
    const q    = document.getElementById('mvt-search').value.toLowerCase();
    const type = document.getElementById('mvt-filter-type').value;
    const date = document.getElementById('mvt-filter-date').value;

    document.querySelectorAll('#mvt-tbody tr').forEach(row => {
        if (!row.dataset.type) return;
        const nameMatch   = row.dataset.name.includes(q) || row.dataset.reason.includes(q);
        const typeMatch   = !type || row.dataset.type === type;
        const dateMatch   = !date || row.dataset.date === date;
        row.style.display = (nameMatch && typeMatch && dateMatch) ? '' : 'none';
    });
}
</script>

@endsection