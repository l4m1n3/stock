{{-- resources/views/admin/branches/index.blade.php --}}
@extends('layouts.apps')

@section('title', 'Admin · Branches')
@section('page-title', 'Gestion des Branches')

@push('styles')
<style>
.branch-card {
    background: white;
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 4px 20px rgba(107,70,193,0.08);
    border: 1px solid rgba(107,70,193,0.06);
    transition: transform 0.15s, box-shadow 0.15s;
}
.branch-card:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(107,70,193,0.14); }
.branch-icon {
    width: 48px; height: 48px; border-radius: 14px;
    background: linear-gradient(135deg, #6B46C1, #9F7AEA);
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 18px; margin-bottom: 14px;
}
.stat-row { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; border-bottom: 1px solid #f5f3ff; }
.stat-row:last-child { border: none; }
.stat-key { color: #9e8fc0; }
.stat-val { font-weight: 700; color: #1a0a3e; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div style="font-size:14px;color:#9e8fc0;">{{ $branches->count() }} branche(s) au total</div>
    <a href="{{ route('admin.branches.create') }}" class="btn-violet">
        <i class="fas fa-plus"></i> Nouvelle branche
    </a>
</div>

<div class="row g-4">
    @forelse($branches as $branch)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="branch-card">
            <div class="branch-icon"><i class="fas fa-store"></i></div>
            <h5 style="font-weight:800;color:#1a0a3e;margin-bottom:4px;">{{ $branch->name }}</h5>
            <p style="color:#9e8fc0;font-size:13px;margin-bottom:14px;">
                <i class="fas fa-map-marker-alt me-1"></i>{{ $branch->city ?? 'Ville non définie' }}
            </p>

            <div class="stat-row">
                <span class="stat-key"><i class="fas fa-users me-1"></i> Utilisateurs</span>
                <span class="stat-val">{{ $branch->users_count }}</span>
            </div>
            <div class="stat-row">
                <span class="stat-key"><i class="fas fa-box me-1"></i> Produits</span>
                <span class="stat-val">{{ $branch->products_count }}</span>
            </div>
            <div class="stat-row">
                <span class="stat-key"><i class="fas fa-cash-register me-1"></i> Ventes</span>
                <span class="stat-val">{{ $branch->sales_count }}</span>
            </div>
            <div class="stat-row">
                <span class="stat-key"><i class="fas fa-chart-line me-1"></i> CA total</span>
                <span class="stat-val" style="color:#6B46C1;">{{ number_format($branch->sales_sum_total_amount ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="stat-row">
                <span class="stat-key"><i class="fas fa-minus-circle me-1"></i> Dépenses</span>
                <span class="stat-val" style="color:#a32d2d;">{{ number_format($branch->expenses_sum_amount ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-sm btn-outline-secondary flex-fill">
                    <i class="fas fa-pen"></i> Modifier
                </a>
                <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" class="flex-fill"
                      onsubmit="return confirm('Supprimer la branche {{ $branch->name }} ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="fas fa-store fa-3x mb-3 d-block" style="color:#d8d0f5;"></i>
        Aucune branche. <a href="{{ route('admin.branches.create') }}">Créer la première</a>
    </div>
    @endforelse
</div>

@endsection