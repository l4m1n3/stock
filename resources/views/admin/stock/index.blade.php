{{-- resources/views/admin/stock/index.blade.php --}}
@extends('layouts.apps')
@section('title', 'Admin · Stock Global')
@section('page-title', 'Stock Global — Toutes Branches')

@push('styles')
<style>
.filter-bar { background:white; border-radius:14px; padding:16px 20px; margin-bottom:20px; box-shadow:0 2px 12px rgba(107,70,193,0.06); display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
.badge-stock-ok      { background:#edfcf2; color:#177f43; font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px; }
.badge-stock-warning { background:#fff8e1; color:#8a6200; font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px; }
.badge-stock-danger  { background:#fcebeb; color:#a32d2d; font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px; }
.movement-type-in    { background:#edfcf2; color:#177f43; }
.movement-type-out   { background:#fff8e1; color:#8a6200; }
.movement-type-loss  { background:#fcebeb; color:#a32d2d; }
.movement-badge      { font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px; }
.kpi-sm { background:white; border-radius:14px; padding:16px; text-align:center; box-shadow:0 2px 12px rgba(107,70,193,0.06); }
.kpi-sm-val { font-size:22px; font-weight:800; color:#1a0a3e; }
.kpi-sm-lbl { font-size:11px; color:#9e8fc0; font-weight:600; text-transform:uppercase; margin-top:3px; }
</style>
@endpush

@section('content')

{{-- KPI --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-sm">
            <div class="kpi-sm-val">{{ $totalProducts }}</div>
            <div class="kpi-sm-lbl">Produits total</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-sm">
            <div class="kpi-sm-val" style="color:#a32d2d;">{{ $criticalCount }}</div>
            <div class="kpi-sm-lbl">Stock critique</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-sm">
            <div class="kpi-sm-val" style="color:#8a6200;">{{ $outOfStockCount }}</div>
            <div class="kpi-sm-lbl">Ruptures</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-sm">
            <div class="kpi-sm-val" style="color:#6B46C1;">{{ number_format($totalStockValue ?? 0, 0, ',', ' ') }}</div>
            <div class="kpi-sm-lbl">Valeur stock (FCFA)</div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<form method="GET" class="filter-bar">
    <select name="branch_id" class="form-select form-select-sm" style="width:160px;border-radius:8px;">
        <option value="">Toutes les branches</option>
        @foreach($branches as $b)
            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
        @endforeach
    </select>

    <select name="filter" class="form-select form-select-sm" style="width:160px;border-radius:8px;">
        <option value="">Tous les produits</option>
        <option value="critical" {{ request('filter') === 'critical' ? 'selected' : '' }}>⚠ Stock critique</option>
    </select>

    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Rechercher un produit…"
           class="form-control form-control-sm" style="width:220px;border-radius:8px;">

    <button type="submit" class="btn-violet" style="padding:7px 14px;font-size:13px;">
        <i class="fas fa-search"></i> Filtrer
    </button>
    <a href="{{ route('admin.stock') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a>
</form>

<div class="row g-4">
    {{-- Tableau stock --}}
    <div class="col-12 col-xl-8">
        <div style="background:white;border-radius:16px;padding:0;box-shadow:0 4px 20px rgba(107,70,193,0.08);overflow:hidden;">
            <div style="padding:18px 22px 12px;border-bottom:1px solid #f0ebff;">
                <span style="font-weight:700;color:#1a0a3e;font-size:15px;"><i class="fas fa-boxes-stacked me-2" style="color:#6B46C1;"></i>Inventaire</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Branche</th>
                            <th class="text-center">Qté</th>
                            <th class="text-center">Seuil</th>
                            <th class="text-end">Prix unit.</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $p)
                        <tr>
                            <td class="fw-600">{{ $p->name }}</td>
                            <td><span style="font-size:11px;background:#f0ebff;color:#6B46C1;padding:2px 8px;border-radius:99px;font-weight:700;">{{ $p->branch->name ?? '-' }}</span></td>
                            <td class="text-center fw-700">{{ $p->stock_quantity }}</td>
                            <td class="text-center" style="color:#9e8fc0;">{{ $p->alert_threshold }}</td>
                            <td class="text-end">{{ number_format($p->price, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                @if($p->stock_quantity == 0)
                                    <span class="badge-stock-danger">Rupture</span>
                                @elseif($p->stock_quantity <= $p->alert_threshold)
                                    <span class="badge-stock-warning">Critique</span>
                                @else
                                    <span class="badge-stock-ok">OK</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Aucun produit trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:14px 20px;">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    {{-- Derniers mouvements --}}
    <div class="col-12 col-xl-4">
        <div style="background:white;border-radius:16px;padding:0;box-shadow:0 4px 20px rgba(107,70,193,0.08);overflow:hidden;">
            <div style="padding:18px 22px 12px;border-bottom:1px solid #f0ebff;">
                <span style="font-weight:700;color:#1a0a3e;font-size:15px;"><i class="fas fa-history me-2" style="color:#6B46C1;"></i>Derniers mouvements</span>
            </div>
            <div style="padding:16px;">
                @forelse($recentMovements as $mv)
                <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #f5f3ff;">
                    <span class="movement-badge movement-type-{{ $mv->type }}">
                        {{ $mv->type === 'in' ? '↑ Entrée' : ($mv->type === 'out' ? '↓ Sortie' : '✕ Perte') }}
                    </span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:700;color:#1a0a3e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $mv->product->name ?? '-' }}</div>
                        <div style="font-size:11px;color:#9e8fc0;">{{ $mv->branch->name ?? '-' }} · {{ $mv->created_at->diffForHumans() }}</div>
                        @if($mv->reason)<div style="font-size:11px;color:#aaa;margin-top:2px;">{{ Str::limit($mv->reason, 40) }}</div>@endif
                    </div>
                    <div style="font-size:13px;font-weight:800;color:#1a0a3e;flex-shrink:0;">{{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }}</div>
                </div>
                @empty
                <p class="text-muted text-center" style="font-size:13px;">Aucun mouvement récent.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection