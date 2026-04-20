@extends('layouts.app')

@section('title', 'Gestion de Stock')

@section('content')

<style>
    :root { --violet: #6B46C1; --violet-light: #EEEDFE; --violet-hover: #5A3AA6; }

    .stock-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
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
    .kpi-sub { font-size: 12px; color: #b0a0d0; margin-top: 4px; }

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
    .toolbar input[type=text] {
        padding: 8px 14px;
        border: 1px solid #e0d9f7;
        border-radius: 10px;
        font-size: 13px;
        background: #faf8ff;
        color: #2d2d2d;
        outline: none;
        width: 220px;
        transition: border-color 0.15s;
    }
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
    .stock-table { width: 100%; border-collapse: collapse; }
    .stock-table thead tr { background: #f7f4ff; }
    .stock-table th {
        padding: 12px 18px;
        font-size: 12px;
        font-weight: 600;
        color: var(--violet);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #f0ebff;
        white-space: nowrap;
    }
    .stock-table td {
        padding: 13px 18px;
        font-size: 14px;
        color: #2d2d2d;
        border-bottom: 1px solid #f7f4ff;
        vertical-align: middle;
    }
    .stock-table tbody tr:last-child td { border-bottom: none; }
    .stock-table tbody tr:hover { background: #faf8ff; }

    /* Progress bar stock */
    .stock-bar-wrap { display: flex; align-items: center; gap: 10px; }
    .stock-bar-bg { flex: 1; height: 6px; background: #f0ebff; border-radius: 99px; overflow: hidden; min-width: 80px; }
    .stock-bar { height: 100%; border-radius: 99px; transition: width 0.4s; }
    .stock-qty { font-weight: 600; font-size: 14px; }

    /* Badges statut */
    .badge-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 99px;
        font-size: 12px; font-weight: 600; white-space: nowrap;
    }
    .badge-ok       { background: #EAF3DE; color: #3B6D11; }
    .badge-low      { background: #FAEEDA; color: #854F0B; }
    .badge-critical { background: #FCEBEB; color: #A32D2D; }
    .badge-rupture  { background: #f0ebff; color: var(--violet); }
    .status-dot { width: 7px; height: 7px; border-radius: 50%; }
    .dot-ok       { background: #639922; }
    .dot-low      { background: #BA7517; }
    .dot-critical { background: #E24B4A; }
    .dot-rupture  { background: var(--violet); }

    /* Actions */
    .action-btns { display: flex; gap: 6px; }
    .btn-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        border: 1px solid #e0d9f7;
        background: #faf8ff;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #9e8fc0; font-size: 13px;
        transition: all 0.15s;
    }
    .btn-icon:hover { background: var(--violet-light); color: var(--violet); border-color: var(--violet); }
    .btn-icon.danger:hover { background: #FCEBEB; color: #A32D2D; border-color: #E24B4A; }

    /* Boutons principaux */
    .btn-violet {
        padding: 9px 18px;
        background: var(--violet);
        color: white; border: none;
        border-radius: 10px;
        font-size: 13px; font-weight: 600;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 7px;
        transition: background 0.15s, transform 0.1s;
    }
    .btn-violet:hover { background: var(--violet-hover); transform: translateY(-1px); }
    .btn-outline-violet {
        padding: 9px 18px;
        background: transparent;
        color: var(--violet);
        border: 1.5px solid var(--violet);
        border-radius: 10px;
        font-size: 13px; font-weight: 600;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 7px;
        transition: all 0.15s;
    }
    .btn-outline-violet:hover { background: var(--violet-light); }

    /* Modal */
    .modal-content { border-radius: 16px; border: none; }
    .modal-header-violet {
        background: linear-gradient(135deg, #6B46C1, #9F7AEA);
        border-radius: 16px 16px 0 0;
        padding: 16px 20px;
    }
    .form-label-s { font-size: 13px; font-weight: 600; color: #5a4a7a; margin-bottom: 5px; display: block; }
    .form-control-s {
        width: 100%; padding: 9px 14px;
        border: 1px solid #e0d9f7;
        border-radius: 10px;
        font-size: 14px;
        color: #2d2d2d;
        outline: none;
        transition: border-color 0.15s;
    }
    .form-control-s:focus { border-color: var(--violet); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    /* Toast */
    #stock-toast {
        position: fixed; top: 20px; right: 20px;
        background: #3B6D11; color: white;
        padding: 12px 22px; border-radius: 12px;
        font-size: 14px; font-weight: 500;
        display: none; z-index: 9999;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    /* Historique mouvements */
    .mvt-row { display: flex; align-items: center; gap: 14px; padding: 10px 20px; border-bottom: 1px solid #f7f4ff; }
    .mvt-row:last-child { border-bottom: none; }
    .mvt-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
    .mvt-in      { background: #EAF3DE; }
    .mvt-out     { background: #FAEEDA; }
    .mvt-loss    { background: #FCEBEB; }
    .mvt-info    { flex: 1; }
    .mvt-product { font-size: 13px; font-weight: 600; color: #2d2d2d; }
    .mvt-reason  { font-size: 12px; color: #9e8fc0; }
    .mvt-qty     { font-size: 14px; font-weight: 700; }
    .mvt-date    { font-size: 12px; color: #b0a0d0; white-space: nowrap; }

    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: 1fr 1fr; }
        .form-row { grid-template-columns: 1fr; }
    }
</style>

<div id="stock-toast">✓ Mouvement de stock enregistré</div>

{{-- ── Header ── --}}
<div class="stock-header">
    <div>
        <h4 class="fw-bold mb-0">Gestion du Stock</h4>
        <small class="text-muted">Inventaire en temps réel — {{ now()->format('d/m/Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn-outline-violet" onclick="openModal('modal-export')">
            <i class="fas fa-download"></i> Exporter
        </button>
        <button class="btn-violet" data-bs-toggle="modal" data-bs-target="#modalEntree">
            <i class="fas fa-plus"></i> Nouvelle entrée
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
        <div class="kpi-label">Stock critique</div>
        <div class="kpi-value" style="color:#E24B4A;">{{ $criticalCount }}</div>
        <div class="kpi-sub">produits sous seuil</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Valeur stock</div>
        <div class="kpi-value" style="color:var(--violet);">{{ number_format($stockValue, 0, ',', ' ') }}</div>
        <div class="kpi-sub">FCFA estimé</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Mouvements (30j)</div>
        <div class="kpi-value">{{ $recentMovements }}</div>
        <div class="kpi-sub">entrées + sorties</div>
    </div>
</div>

{{-- ── Table inventaire ── --}}
<div class="panel">
    <div class="panel-header">
        <span class="panel-title"><i class="fas fa-boxes me-2" style="color:var(--violet);"></i>Inventaire</span>
        <div class="toolbar">
            <input type="text" id="stock-search" placeholder="Rechercher un produit..." oninput="stockFilter()">
            <select id="stock-filter-status" onchange="stockFilter()">
                <option value="">Tous les statuts</option>
                <option value="ok">OK</option>
                <option value="low">Bas</option>
                <option value="critical">Critique</option>
                <option value="rupture">Rupture</option>
            </select>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="stock-table" id="stock-table">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Seuil alerte</th>
                    <th>Statut</th>
                    <th>Dernière MAJ</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="stock-tbody">
                @foreach($products as $product)
                @php
                    $pct = $product->alert_threshold > 0
                        ? min(100, round($product->stock_quantity / $product->alert_threshold * 100))
                        : 100;
                    if ($product->stock_quantity === 0) {
                        $statusKey = 'rupture';
                        $barColor  = '#7F77DD';
                    } elseif ($product->stock_quantity < $product->alert_threshold * 0.5) {
                        $statusKey = 'critical';
                        $barColor  = '#E24B4A';
                    } elseif ($product->stock_quantity < $product->alert_threshold) {
                        $statusKey = 'low';
                        $barColor  = '#EF9F27';
                    } else {
                        $statusKey = 'ok';
                        $barColor  = '#639922';
                    }
                    $badges = [
                        'ok'       => '<span class="badge-status badge-ok"><span class="status-dot dot-ok"></span>OK</span>',
                        'low'      => '<span class="badge-status badge-low"><span class="status-dot dot-low"></span>Bas</span>',
                        'critical' => '<span class="badge-status badge-critical"><span class="status-dot dot-critical"></span>Critique</span>',
                        'rupture'  => '<span class="badge-status badge-rupture"><span class="status-dot dot-rupture"></span>Rupture</span>',
                    ];
                @endphp
                <tr data-status="{{ $statusKey }}" data-name="{{ strtolower($product->name) }}">
                    <td>
                        <div style="font-weight:600;">{{ $product->name }}</div>
                        <div style="font-size:11px;color:#9e8fc0;">{{ $product->description ?? '—' }}</div>
                    </td>
                    <td style="font-weight:600;color:var(--violet);">
                        {{ number_format($product->price, 0, ',', ' ') }} FCFA
                    </td>
                    <td>
                        <div class="stock-bar-wrap">
                            <span class="stock-qty" style="color:{{ $barColor }};">{{ $product->stock_quantity }}</span>
                            <div class="stock-bar-bg">
                                <div class="stock-bar" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                            </div>
                        </div>
                    </td>
                    <td style="color:#9e8fc0;">{{ $product->alert_threshold ?? '—' }}</td>
                    <td>{!! $badges[$statusKey] !!}</td>
                    <td style="color:#b0a0d0;font-size:13px;">{{ $product->updated_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-icon" title="Entrée stock"
                                onclick="prefillEntree({{ $product->id }}, '{{ addslashes($product->name) }}')">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="btn-icon" title="Sortie / perte"
                                onclick="prefillSortie({{ $product->id }}, '{{ addslashes($product->name) }}')">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button class="btn-icon" title="Modifier">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn-icon danger" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── Historique mouvements ── --}}
<div class="panel">
    <div class="panel-header">
        <span class="panel-title"><i class="fas fa-history me-2" style="color:var(--violet);"></i>Derniers mouvements</span>
        <a href="{{ route('stock.movements') }}" style="font-size:13px;color:var(--violet);font-weight:600;text-decoration:none;">
            Voir tout →
        </a>
    </div>
    @forelse($recentMovementsData as $mvt)
    @php
        $mvtIcons = ['in' => '↑', 'out' => '↓', 'loss' => '✕'];
        $mvtClasses = ['in' => 'mvt-in', 'out' => 'mvt-out', 'loss' => 'mvt-loss'];
        $mvtColors  = ['in' => '#3B6D11', 'out' => '#854F0B', 'loss' => '#A32D2D'];
    @endphp
    <div class="mvt-row">
        <div class="mvt-icon {{ $mvtClasses[$mvt->type] }}">
            {{ $mvtIcons[$mvt->type] }}
        </div>
        <div class="mvt-info">
            <div class="mvt-product">{{ $mvt->product->name ?? '—' }}</div>
            <div class="mvt-reason">{{ $mvt->reason ?? ucfirst($mvt->type) }}</div>
        </div>
        <div class="mvt-qty" style="color:{{ $mvtColors[$mvt->type] }};">
            {{ $mvt->type === 'in' ? '+' : '-' }}{{ $mvt->quantity }}
        </div>
        <div class="mvt-date">{{ $mvt->created_at->diffForHumans() }}</div>
    </div>
    @empty
    <div style="padding:30px;text-align:center;color:#b0a0d0;font-size:14px;">Aucun mouvement enregistré</div>
    @endforelse
</div>

{{-- ── Modal : Nouvelle entrée de stock ── --}}
<div class="modal fade" id="modalEntree" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0" id="modal-title-entree">
                    <i class="fas fa-plus-circle me-2"></i>Mouvement de stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('stock.movement.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-s">Produit</label>
                        <select class="form-control-s" name="product_id" id="modal-product-id" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (stock: {{ $p->stock_quantity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row mb-3">
                        <div>
                            <label class="form-label-s">Type de mouvement</label>
                            <select class="form-control-s" name="type" id="modal-mvt-type" required>
                                <option value="in">Entrée (+)</option>
                                <option value="out">Sortie (-)</option>
                                <option value="loss">Perte / Casse</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label-s">Quantité</label>
                            <input type="number" class="form-control-s" name="quantity" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-s">Motif (optionnel)</label>
                        <input type="text" class="form-control-s" name="reason" placeholder="Ex: Réapprovisionnement fournisseur, Vente #42...">
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
function stockFilter() {
    const q = document.getElementById('stock-search').value.toLowerCase();
    const s = document.getElementById('stock-filter-status').value;
    document.querySelectorAll('#stock-tbody tr').forEach(row => {
        const nameMatch   = row.dataset.name.includes(q);
        const statusMatch = !s || row.dataset.status === s;
        row.style.display = (nameMatch && statusMatch) ? '' : 'none';
    });
}

function prefillEntree(id, name) {
    document.getElementById('modal-product-id').value = id;
    document.getElementById('modal-mvt-type').value = 'in';
    document.getElementById('modal-title-entree').innerHTML =
        '<i class="fas fa-plus-circle me-2"></i>Entrée — ' + name;
    new bootstrap.Modal(document.getElementById('modalEntree')).show();
}

function prefillSortie(id, name) {
    document.getElementById('modal-product-id').value = id;
    document.getElementById('modal-mvt-type').value = 'out';
    document.getElementById('modal-title-entree').innerHTML =
        '<i class="fas fa-minus-circle me-2"></i>Sortie — ' + name;
    new bootstrap.Modal(document.getElementById('modalEntree')).show();
}

@if(session('success'))
    const t = document.getElementById('stock-toast');
    t.textContent = '✓ {{ session("success") }}';
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 3000);
@endif
</script>

@endsection