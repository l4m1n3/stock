@extends('layouts.app')

@section('title', 'Réapprovisionnement')

@section('content')

<style>
    :root { --violet: #6B46C1; --violet-light: #EEEDFE; --violet-hover: #5A3AA6; }

    .po-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }

    /* KPIs */
    .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px; }
    .kpi-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #e8e4f7; padding: 18px 20px;
        box-shadow: 0 2px 12px rgba(107,70,193,0.06);
    }
    .kpi-label { font-size: 12px; font-weight: 600; color: #9e8fc0; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
    .kpi-value { font-size: 26px; font-weight: 700; color: #2d2d2d; line-height: 1; }
    .kpi-sub   { font-size: 12px; color: #b0a0d0; margin-top: 4px; }

    /* Tabs */
    .tabs-nav {
        display: flex; gap: 4px;
        background: #f7f4ff; border-radius: 12px;
        padding: 4px; width: fit-content; margin-bottom: 20px;
    }
    .tab-nav-btn {
        padding: 8px 22px; border-radius: 9px;
        font-size: 13px; font-weight: 600;
        border: none; background: transparent;
        color: #9e8fc0; cursor: pointer; transition: all 0.18s;
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
        background: #faf8ff; display: flex;
        align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .panel-title { font-size: 15px; font-weight: 600; color: #2d2d2d; }

    /* Toolbar */
    .toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .toolbar input[type=text] {
        padding: 8px 14px; border: 1px solid #e0d9f7;
        border-radius: 10px; font-size: 13px;
        background: #faf8ff; color: #2d2d2d;
        outline: none; width: 200px; transition: border-color 0.15s;
    }
    .toolbar input:focus { border-color: var(--violet); }
    .toolbar select {
        padding: 8px 12px; border: 1px solid #e0d9f7;
        border-radius: 10px; font-size: 13px;
        background: #faf8ff; color: #2d2d2d; outline: none; cursor: pointer;
    }

    /* Table */
    .po-table { width: 100%; border-collapse: collapse; }
    .po-table thead tr { background: #f7f4ff; }
    .po-table th {
        padding: 12px 18px; font-size: 12px; font-weight: 600;
        color: var(--violet); text-transform: uppercase;
        letter-spacing: 0.05em; border-bottom: 1px solid #f0ebff; white-space: nowrap;
    }
    .po-table td {
        padding: 13px 18px; font-size: 14px; color: #2d2d2d;
        border-bottom: 1px solid #f7f4ff; vertical-align: middle;
    }
    .po-table tbody tr:last-child td { border-bottom: none; }
    .po-table tbody tr:hover { background: #faf8ff; }

    /* Badges statut */
    .badge-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 99px;
        font-size: 12px; font-weight: 600;
    }
    .status-pending   { background: #FAEEDA; color: #854F0B; }
    .status-received  { background: #EAF3DE; color: #3B6D11; }
    .status-cancelled { background: #FCEBEB; color: #A32D2D; }

    /* Action buttons */
    .action-btns { display: flex; gap: 6px; }
    .btn-icon {
        width: 30px; height: 30px; border-radius: 8px;
        border: 1px solid #e0d9f7; background: #faf8ff;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        color: #9e8fc0; font-size: 13px; transition: all 0.15s;
        text-decoration: none;
    }
    .btn-icon:hover { background: var(--violet-light); color: var(--violet); border-color: var(--violet); }
    .btn-icon.success:hover { background: #EAF3DE; color: #3B6D11; border-color: #639922; }
    .btn-icon.danger:hover  { background: #FCEBEB; color: #A32D2D; border-color: #E24B4A; }

    /* Boutons */
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

    /* Ligne article commande */
    .po-line {
        display: grid;
        grid-template-columns: 2fr 80px 140px 140px auto;
        gap: 8px; align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f0ebff;
    }
    .po-line:last-child { border-bottom: none; }
    .po-line-header {
        display: grid;
        grid-template-columns: 2fr 80px 140px 140px auto;
        gap: 8px;
        padding: 8px 0;
        font-size: 11px; font-weight: 600;
        color: #9e8fc0; text-transform: uppercase; letter-spacing: 0.05em;
        border-bottom: 1px solid #e0d9f7; margin-bottom: 4px;
    }

    /* Ligne réception */
    .recv-line {
        display: grid;
        grid-template-columns: 2fr 80px 80px 80px;
        gap: 8px; align-items: center;
        padding: 10px 0; border-bottom: 1px solid #f0ebff;
    }
    .recv-line:last-child { border-bottom: none; }
    .recv-line-header {
        display: grid;
        grid-template-columns: 2fr 80px 80px 80px;
        gap: 8px;
        padding: 8px 0;
        font-size: 11px; font-weight: 600;
        color: #9e8fc0; text-transform: uppercase; letter-spacing: 0.05em;
        border-bottom: 1px solid #e0d9f7; margin-bottom: 4px;
    }

    /* Table fournisseurs */
    .sup-table { width: 100%; border-collapse: collapse; }
    .sup-table thead tr { background: #f7f4ff; }
    .sup-table th {
        padding: 12px 18px; font-size: 12px; font-weight: 600;
        color: var(--violet); text-transform: uppercase;
        letter-spacing: 0.05em; border-bottom: 1px solid #f0ebff; white-space: nowrap;
    }
    .sup-table td {
        padding: 13px 18px; font-size: 14px; color: #2d2d2d;
        border-bottom: 1px solid #f7f4ff; vertical-align: middle;
    }
    .sup-table tbody tr:last-child td { border-bottom: none; }
    .sup-table tbody tr:hover { background: #faf8ff; }

    /* Récap total commande */
    .po-total-bar {
        display: flex; justify-content: flex-end; align-items: center; gap: 12px;
        padding: 12px 0 0; margin-top: 4px;
        border-top: 1px solid #e0d9f7;
        font-size: 15px; font-weight: 700; color: var(--violet);
    }

    /* Empty state */
    .empty-state { padding: 60px 20px; text-align: center; color: #b0a0d0; }
    .empty-icon  { font-size: 48px; margin-bottom: 12px; }

    /* Toast */
    #po-toast {
        position: fixed; top: 20px; right: 20px;
        background: #3B6D11; color: white;
        padding: 12px 22px; border-radius: 12px;
        font-size: 14px; font-weight: 500;
        display: none; z-index: 9999;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: 1fr 1fr; }
        .form-row  { grid-template-columns: 1fr; }
        .po-line, .po-line-header { grid-template-columns: 1fr; }
    }
</style>

<div id="po-toast">✓ Opération réussie</div>

{{-- ── Header ── --}}
<div class="po-header">
    <div>
        <h4 class="fw-bold mb-0">Réapprovisionnement</h4>
        <small class="text-muted">Commandes & fournisseurs — {{ now()->format('d/m/Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn-outline-violet" data-bs-toggle="modal" data-bs-target="#modalSupplier">
            <i class="fas fa-truck"></i> Nouveau fournisseur
        </button>
        <button class="btn-violet" data-bs-toggle="modal" data-bs-target="#modalOrder">
            <i class="fas fa-plus"></i> Nouvelle commande
        </button>
    </div>
</div>

{{-- ── KPIs ── --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Commandes ce mois</div>
        <div class="kpi-value">{{ $monthOrders }}</div>
        <div class="kpi-sub">bons de commande émis</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Dépenses réceptionnées</div>
        <div class="kpi-value" style="color:#3B6D11;">{{ number_format($monthSpend, 0, ',', ' ') }}</div>
        <div class="kpi-sub">FCFA ce mois</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">En attente</div>
        <div class="kpi-value" style="color:#854F0B;">{{ $pendingCount }}</div>
        <div class="kpi-sub">commandes à réceptionner</div>
    </div>
</div>

{{-- ── Tabs ── --}}
<div class="tabs-nav">
    <button class="tab-nav-btn active" onclick="switchTab(this,'commandes')">
        <i class="fas fa-clipboard-list me-1"></i> Commandes ({{ $orders->total() }})
    </button>
    <button class="tab-nav-btn" onclick="switchTab(this,'fournisseurs')">
        <i class="fas fa-truck me-1"></i> Fournisseurs ({{ $suppliers->count() }})
    </button>
</div>

{{-- ══════════════════════ SECTION COMMANDES ══════════════════════ --}}
<div id="section-commandes">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">
                <i class="fas fa-clipboard-list me-2" style="color:var(--violet);"></i>Bons de commande
            </span>
            <div class="toolbar">
                <input type="text" id="po-search" placeholder="Rechercher..." oninput="filterOrders()">
                <select id="po-status-filter" onchange="filterOrders()">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="received">Réceptionné</option>
                    <option value="cancelled">Annulé</option>
                </select>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="po-table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Fournisseur</th>
                        <th>Produits</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Date commande</th>
                        <th>Réceptionné le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="po-tbody">
                    @forelse($orders as $order)
                    <tr data-supplier="{{ strtolower($order->supplier->name ?? '') }}"
                        data-status="{{ $order->status }}">
                        <td>
                            <span style="font-weight:700;color:var(--violet);">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td>
                            <div style="font-weight:600;">{{ $order->supplier->name ?? '—' }}</div>
                            <div style="font-size:12px;color:#9e8fc0;">{{ $order->supplier->phone ?? '' }}</div>
                        </td>
                        <td style="font-size:13px;color:#5a4a7a;">
                            {{ $order->items->count() }} article(s)
                        </td>
                        <td style="font-weight:700;color:var(--violet);">
                            {{ number_format($order->total_amount, 0, ',', ' ') }} FCFA
                        </td>
                        <td>
                            @if($order->status === 'pending')
                                <span class="badge-status status-pending"><i class="fas fa-clock"></i> En attente</span>
                            @elseif($order->status === 'received')
                                <span class="badge-status status-received"><i class="fas fa-check"></i> Réceptionné</span>
                            @else
                                <span class="badge-status status-cancelled"><i class="fas fa-times"></i> Annulé</span>
                            @endif
                        </td>
                        <td style="font-size:13px;color:#9e8fc0;">
                            {{ $order->ordered_at->format('d/m/Y') }}
                        </td>
                        <td style="font-size:13px;color:#9e8fc0;">
                            {{ $order->received_at ? $order->received_at->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <div class="action-btns">
                                {{-- Voir détail --}}
                                <a href="{{ route('purchases.show', $order) }}" class="btn-icon" title="Voir détail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($order->isPending())
                                    {{-- Réceptionner --}}
                                    <button class="btn-icon success" title="Réceptionner"
                                        onclick="openReceive({{ $order->id }})">
                                        <i class="fas fa-box-open"></i>
                                    </button>

                                    {{-- Annuler --}}
                                    <form action="{{ route('purchases.cancel', $order) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-icon danger" title="Annuler"
                                            onclick="return confirm('Annuler cette commande ?')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8">
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <p>Aucune commande enregistrée</p>
                            <button class="btn-violet mt-2" data-bs-toggle="modal" data-bs-target="#modalOrder">
                                + Créer la première commande
                            </button>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
        <div class="px-4 py-3 border-top" style="border-color:#f0ebff!important;">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════ SECTION FOURNISSEURS ══════════════════════ --}}
<div id="section-fournisseurs" style="display:none;">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">
                <i class="fas fa-truck me-2" style="color:var(--violet);"></i>Fournisseurs
            </span>
            <div class="toolbar">
                <input type="text" id="sup-search" placeholder="Rechercher..." oninput="filterSuppliers()">
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="sup-table">
                <thead>
                    <tr>
                        <th>Fournisseur</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Adresse</th>
                        <th>Commandes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="sup-tbody">
                    @forelse($suppliers as $supplier)
                    <tr data-name="{{ strtolower($supplier->name) }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:10px;background:var(--violet-light);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;color:var(--violet);flex-shrink:0;">
                                    {{ strtoupper(substr($supplier->name, 0, 1)) }}
                                </div>
                                <span style="font-weight:600;">{{ $supplier->name }}</span>
                            </div>
                        </td>
                        <td style="font-size:13px;">{{ $supplier->phone ?? '—' }}</td>
                        <td style="font-size:13px;color:#9e8fc0;">{{ $supplier->email ?? '—' }}</td>
                        <td style="font-size:13px;color:#9e8fc0;">{{ $supplier->address ?? '—' }}</td>
                        <td style="font-weight:600;color:var(--violet);">
                            {{ $supplier->purchaseOrders->count() }}
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-icon" onclick="editSupplier({{ $supplier->id }})" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('fournisseurs.destroy', $supplier) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon danger" title="Supprimer"
                                        onclick="return confirm('Supprimer ce fournisseur ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon">🚚</div>
                            <p>Aucun fournisseur enregistré</p>
                            <button class="btn-violet mt-2" data-bs-toggle="modal" data-bs-target="#modalSupplier">
                                + Ajouter le premier fournisseur
                            </button>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ══════════════════════ MODAL : Nouvelle commande ══════════════════════ --}}
<div class="modal fade" id="modalOrder" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Nouvelle commande
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchases.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">

                    {{-- Fournisseur + Notes --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-s">Fournisseur *</label>
                            <select class="form-control-s" name="supplier_id" required>
                                <option value="">— Choisir un fournisseur —</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label-s">Notes</label>
                            <input type="text" class="form-control-s" name="notes" placeholder="Remarques éventuelles...">
                        </div>
                    </div>

                    {{-- Lignes articles --}}
                    <div class="form-group">
                        <label class="form-label-s">Articles commandés *</label>
                        <div class="po-line-header">
                            <span>Produit</span>
                            <span>Qté</span>
                            <span>Prix achat (FCFA)</span>
                            <span>Prix vente (FCFA)</span>
                            <span></span>
                        </div>
                        <div id="order-lines-container"></div>
                        <button type="button" class="btn-outline-violet mt-3" style="font-size:13px;padding:7px 14px;" onclick="addOrderLine()">
                            <i class="fas fa-plus"></i> Ajouter un article
                        </button>
                    </div>

                    {{-- Total --}}
                    <div class="po-total-bar">
                        <span style="font-size:13px;font-weight:400;color:#9e8fc0;">Total estimé :</span>
                        <span id="order-total">0 FCFA</span>
                    </div>

                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-violet px-4">
                        <i class="fas fa-check me-1"></i> Créer la commande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════ MODAL : Réception commande ══════════════════════ --}}
<div class="modal fade" id="modalReceive" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0">
                    <i class="fas fa-box-open me-2"></i>Réceptionner la commande
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="receive-form" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div style="background:#faf8ff;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#5a4a7a;">
                        <i class="fas fa-info-circle me-1" style="color:var(--violet);"></i>
                        Saisissez les quantités réellement reçues. Le stock sera incrémenté automatiquement.
                        Si un prix de vente est renseigné sur l'article, il sera mis à jour sur le produit.
                    </div>

                    <div class="recv-line-header">
                        <span>Produit</span>
                        <span style="text-align:center;">Commandé</span>
                        <span style="text-align:center;">Reçu</span>
                        <span style="text-align:center;">Prix achat</span>
                    </div>
                    <div id="receive-lines-container"></div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-violet px-4" style="background:#3B6D11;">
                        <i class="fas fa-check me-1"></i> Confirmer la réception
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════ MODAL : Nouveau/Edit Fournisseur ══════════════════════ --}}
<div class="modal fade" id="modalSupplier" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0" id="modal-supplier-title">
                    <i class="fas fa-truck me-2"></i>Nouveau fournisseur
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="supplier-form" action="{{ route('fournisseurs.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="supplier-method" value="POST">
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="form-label-s">Nom du fournisseur *</label>
                        <input type="text" class="form-control-s" name="name" id="sup-name"
                               placeholder="Ex: Fleurs du Sahel SARL" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-s">Téléphone</label>
                            <input type="text" class="form-control-s" name="phone" id="sup-phone"
                                   placeholder="+227 XX XX XX XX">
                        </div>
                        <div class="form-group">
                            <label class="form-label-s">Email</label>
                            <input type="email" class="form-control-s" name="email" id="sup-email"
                                   placeholder="contact@fournisseur.com">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label-s">Adresse</label>
                        <textarea class="form-control-s" name="address" id="sup-address"
                                  rows="2" placeholder="Adresse du fournisseur..."></textarea>
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
// ── Données injectées ────────────────────────────────────────────────────────
const productsData = @json($products);
const ordersData   = @json($orders->load('items.product')->keyBy('id'));
const suppliersData = @json($suppliers->keyBy('id'));

// ── Tabs ─────────────────────────────────────────────────────────────────────
function switchTab(btn, section) {
    document.querySelectorAll('.tab-nav-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('section-commandes').style.display    = section === 'commandes'    ? '' : 'none';
    document.getElementById('section-fournisseurs').style.display = section === 'fournisseurs' ? '' : 'none';
}

// ── Filtres ──────────────────────────────────────────────────────────────────
function filterOrders() {
    const q = document.getElementById('po-search').value.toLowerCase();
    const s = document.getElementById('po-status-filter').value;
    document.querySelectorAll('#po-tbody tr').forEach(row => {
        const sup    = (row.dataset.supplier || '').includes(q);
        const status = !s || row.dataset.status === s;
        row.style.display = (sup && status) ? '' : 'none';
    });
}

function filterSuppliers() {
    const q = document.getElementById('sup-search').value.toLowerCase();
    document.querySelectorAll('#sup-tbody tr').forEach(row => {
        row.style.display = (row.dataset.name || '').includes(q) ? '' : 'none';
    });
}

// ── Lignes de commande ────────────────────────────────────────────────────────
let lineIndex = 0;

function addOrderLine() {
    const idx = lineIndex++;
    const options = productsData.map(p =>
        `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`
    ).join('');

    const div = document.createElement('div');
    div.className = 'po-line';
    div.innerHTML = `
        <select class="form-control-s ol-product" name="items[${idx}][product_id]" onchange="recalcTotal()" required>
            <option value="">— Produit —</option>
            ${options}
        </select>
        <input type="number" class="form-control-s ol-qty" name="items[${idx}][quantity]"
               value="1" min="1" style="text-align:center;" oninput="recalcTotal()" required>
        <input type="number" class="form-control-s ol-pprice" name="items[${idx}][purchase_price]"
               placeholder="0" min="0" step="100" oninput="recalcTotal()" required>
        <input type="number" class="form-control-s ol-sprice" name="items[${idx}][selling_price]"
               placeholder="Optionnel" min="0" step="100">
        <button type="button" class="btn-icon danger" onclick="this.parentElement.remove();recalcTotal();">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.getElementById('order-lines-container').appendChild(div);
    recalcTotal();
}

function recalcTotal() {
    let total = 0;
    document.querySelectorAll('#order-lines-container .po-line').forEach(line => {
        const qty    = parseFloat(line.querySelector('.ol-qty')?.value)    || 0;
        const pprice = parseFloat(line.querySelector('.ol-pprice')?.value) || 0;
        total += qty * pprice;
    });
    document.getElementById('order-total').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

// Ajouter une ligne vide par défaut à l'ouverture du modal
document.getElementById('modalOrder').addEventListener('show.bs.modal', function(e) {
    if (!e.relatedTarget) return;
    document.getElementById('order-lines-container').innerHTML = '';
    lineIndex = 0;
    recalcTotal();
    addOrderLine();
});

// ── Réception commande ────────────────────────────────────────────────────────
// function openReceive(orderId) {
//     const order = ordersData[orderId];
//     if (!order) return;

//     document.getElementById('receive-form').action = `/reapprovisionnement/${orderId}/recevoir`;

//     const container = document.getElementById('receive-lines-container');
//     container.innerHTML = (order.items || []).map(item => `
//         <div class="recv-line">
//             <div>
//                 <div style="font-weight:600;">${item.product?.name ?? '—'}</div>
//                 <div style="font-size:12px;color:#9e8fc0;">${Number(item.purchase_price).toLocaleString('fr-FR')} FCFA / unité</div>
//             </div>
//             <div style="text-align:center;font-weight:600;color:#9e8fc0;">${item.quantity_ordered}</div>
//             <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
//             <input type="number" class="form-control-s" name="items[${item.id}][quantity_received]"
//                    value="${item.quantity_ordered}" min="0" max="${item.quantity_ordered}"
//                    style="width:70px;text-align:center;margin:0 auto;">
//             <div style="text-align:center;font-size:13px;color:#9e8fc0;">
//                 ${Number(item.purchase_price).toLocaleString('fr-FR')} FCFA
//             </div>
//         </div>
//     `).join('');

//     new bootstrap.Modal(document.getElementById('modalReceive')).show();
// }
// Remplacer openReceive() dans la vue
// async function openReceive(orderId) {
//     const res   = await fetch(`/reapprovisionnement/${orderId}/items`);
//     const items = await res.json();

//     document.getElementById('receive-form').action = `/reapprovisionnement/${orderId}/recevoir`;

//     const container = document.getElementById('receive-lines-container');
//     container.innerHTML = items.map(item => `
//         <div class="recv-line">
//             <div>
//                 <div style="font-weight:600;">${item.product?.name ?? '—'}</div>
//                 <div style="font-size:12px;color:#9e8fc0;">
//                     ${Number(item.purchase_price).toLocaleString('fr-FR')} FCFA / unité
//                 </div>
//             </div>
//             <div style="text-align:center;font-weight:600;color:#9e8fc0;">
//                 ${item.quantity_ordered}
//             </div>
//             <input type="hidden" name="items[${item.id}][id]" value="${item.id}">
//             <input type="number" class="form-control-s"
//                    name="items[${item.id}][quantity_received]"
//                    value="${item.quantity_ordered}" min="0" max="${item.quantity_ordered}"
//                    style="width:70px;text-align:center;margin:0 auto;">
//             <div style="text-align:center;font-size:13px;color:#9e8fc0;">
//                 ${Number(item.purchase_price).toLocaleString('fr-FR')} FCFA
//             </div>
//         </div>
//     `).join('');

//     new bootstrap.Modal(document.getElementById('modalReceive')).show();
// }
// Dans purchases/index.blade.php — remplacer openReceive()
async function openReceive(orderId) {
    const container = document.getElementById('receive-lines-container');
    container.innerHTML = `
        <div style="text-align:center;padding:30px;color:#9e8fc0;">
            <i class="fas fa-spinner fa-spin me-2"></i>Chargement...
        </div>`;

    // Ouvrir le modal d'abord
    const modal = new bootstrap.Modal(document.getElementById('modalReceive'));
    modal.show();

    try {
        const res = await fetch(`/reapprovisionnement/${orderId}/items`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!res.ok) throw new Error('Erreur réseau : ' + res.status);

        const items = await res.json();

        // ← Définir l'action ICI, après confirmation que le fetch a réussi
        document.getElementById('receive-form').action =
            `/reapprovisionnement/${orderId}/recevoir`;

        if (!items.length) {
            container.innerHTML = `
                <div style="text-align:center;padding:30px;color:#b0a0d0;">
                    Aucun article dans cette commande.
                </div>`;
            return;
        }

        container.innerHTML = items.map(item => `
            <div class="recv-line">
                <div>
                    <div style="font-weight:600;">
                        ${item.product?.name ?? '<span style="color:#E24B4A;">Produit introuvable</span>'}
                    </div>
                    <div style="font-size:12px;color:#9e8fc0;">
                        Prix achat : ${Number(item.purchase_price).toLocaleString('fr-FR')} FCFA
                        ${item.selling_price > 0
                            ? ' · Prix vente : ' + Number(item.selling_price).toLocaleString('fr-FR') + ' FCFA'
                            : ''}
                    </div>
                </div>
                <div style="text-align:center;font-weight:600;color:#9e8fc0;">
                    ${item.quantity_ordered}
                </div>
                <input type="hidden"
                       name="items[${item.id}][id]"
                       value="${item.id}">
                <input type="number"
                       class="form-control-s"
                       name="items[${item.id}][quantity_received]"
                       value="${item.quantity_ordered}"
                       min="0"
                       max="${item.quantity_ordered}"
                       style="width:70px;text-align:center;margin:0 auto;"
                       required>
                <div style="text-align:center;font-size:13px;color:#9e8fc0;">
                    ${Number(item.purchase_price).toLocaleString('fr-FR')} FCFA
                </div>
            </div>
        `).join('');

    } catch (err) {
        container.innerHTML = `
            <div style="text-align:center;padding:30px;color:#A32D2D;">
                <i class="fas fa-exclamation-circle me-2"></i>
                Erreur lors du chargement : ${err.message}
            </div>`;
    }
}
// ── Edit fournisseur ──────────────────────────────────────────────────────────
function editSupplier(id) {
    const s = suppliersData[id];
    if (!s) return;
    document.getElementById('modal-supplier-title').innerHTML = '<i class="fas fa-pen me-2"></i>Modifier ' + s.name;
    document.getElementById('supplier-form').action = `/fournisseurs/${id}`;
    document.getElementById('supplier-method').value = 'PUT';
    document.getElementById('sup-name').value    = s.name;
    document.getElementById('sup-phone').value   = s.phone  || '';
    document.getElementById('sup-email').value   = s.email  || '';
    document.getElementById('sup-address').value = s.address || '';
    new bootstrap.Modal(document.getElementById('modalSupplier')).show();
}

// Réinitialiser modal fournisseur à l'ouverture (nouveau)
document.getElementById('modalSupplier').addEventListener('show.bs.modal', function(e) {
    if (!e.relatedTarget) return;
    document.getElementById('modal-supplier-title').innerHTML = '<i class="fas fa-truck me-2"></i>Nouveau fournisseur';
    document.getElementById('supplier-form').action  = "{{ route('fournisseurs.store') }}";
    document.getElementById('supplier-method').value = 'POST';
    ['sup-name','sup-phone','sup-email','sup-address'].forEach(id => {
        document.getElementById(id).value = '';
    });
});

// ── Toast session ─────────────────────────────────────────────────────────────
@if(session('success'))
    const t = document.getElementById('po-toast');
    t.textContent = '✓ {{ session("success") }}';
    t.style.display = 'block';
    setTimeout(() => t.style.display = 'none', 3000);
@endif
</script>

@endsection