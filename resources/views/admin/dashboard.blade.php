{{-- resources/views/admin/index.blade.php --}}
@extends('layouts.apps')

@section('title', 'Admin · Dashboard Global')
@section('page-title', 'Administration · Vue Globale')

@push('styles')
<style>
.kpi-card {
    background: white;
    border-radius: 16px;
    padding: 22px 20px;
    box-shadow: 0 4px 20px rgba(107,70,193,0.08);
    border: 1px solid rgba(107,70,193,0.06);
    transition: transform 0.15s, box-shadow 0.15s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(107,70,193,0.14); }
.kpi-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    margin-bottom: 14px;
}
.kpi-value { font-size: 26px; font-weight: 800; color: #1a0a3e; line-height: 1; }
.kpi-label { font-size: 12px; color: #9e8fc0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
.kpi-sub   { font-size: 12px; color: #aaa; margin-top: 6px; }

.section-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(107,70,193,0.08);
    border: 1px solid rgba(107,70,193,0.06);
    margin-bottom: 24px;
}
.section-title {
    font-size: 15px; font-weight: 700; color: #1a0a3e;
    margin-bottom: 18px;
    display: flex; align-items: center; gap: 8px;
}

.badge-role {
    font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px;
}
.badge-admin   { background: #f0ebff; color: #6B46C1; }
.badge-manager { background: #e8f4fd; color: #1a6fb0; }
.badge-staff   { background: #edfcf2; color: #177f43; }

.branch-bar {
    display: flex; align-items: center; gap: 10px; margin-bottom: 10px;
}
.branch-bar-label { width: 90px; font-size: 13px; color: #444; font-weight: 600; flex-shrink: 0; }
.branch-bar-track {
    flex: 1; height: 8px; background: #f0ebff; border-radius: 99px; overflow: hidden;
}
.branch-bar-fill { height: 100%; background: linear-gradient(90deg, #6B46C1, #9F7AEA); border-radius: 99px; }
.branch-bar-val { font-size: 12px; color: #9e8fc0; font-weight: 600; flex-shrink: 0; min-width: 80px; text-align: right; }

.alert-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 99px; font-size: 12px; font-weight: 700;
}
.alert-danger  { background: #fcebeb; color: #a32d2d; }
.alert-warning { background: #fff8e1; color: #8a6200; }
.alert-success { background: #edfcf2; color: #177f43; }
</style>
@endpush

@section('content')

{{-- ── KPI row ─────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#f0ebff;color:#6B46C1;"><i class="fas fa-code-branch"></i></div>
            <div class="kpi-value">{{ $totalBranches }}</div>
            <div class="kpi-label">Branches</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#e8f4fd;color:#1a6fb0;"><i class="fas fa-users"></i></div>
            <div class="kpi-value">{{ $totalUsers }}</div>
            <div class="kpi-label">Utilisateurs</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#edfcf2;color:#177f43;"><i class="fas fa-chart-line"></i></div>
            <div class="kpi-value">{{ number_format($monthRevenue, 0, ',', ' ') }}</div>
            <div class="kpi-label">CA ce mois (FCFA)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:{{ $netProfit >= 0 ? '#edfcf2' : '#fcebeb' }};color:{{ $netProfit >= 0 ? '#177f43' : '#a32d2d' }};"><i class="fas fa-wallet"></i></div>
            <div class="kpi-value" style="color:{{ $netProfit >= 0 ? '#177f43' : '#a32d2d' }};">{{ number_format($netProfit, 0, ',', ' ') }}</div>
            <div class="kpi-label">Bénéfice net (FCFA)</div>
        </div>
    </div>
</div>

{{-- Alertes --}}
@if($criticalStock > 0 || $pendingOrders > 0)
<div class="d-flex flex-wrap gap-2 mb-4">
    @if($criticalStock > 0)
        <a href="{{ route('admin.stock') }}?filter=critical" class="alert-badge alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {{ $criticalStock }} produit(s) en stock critique
        </a>
    @endif
    @if($pendingOrders > 0)
        <a href="{{ route('purchases.index') }}" class="alert-badge alert-warning">
            <i class="fas fa-clock"></i> {{ $pendingOrders }} commande(s) en attente
        </a>
    @endif
</div>
@endif

<div class="row g-4">

    {{-- Graphique ventes 30j --}}
    <div class="col-12 col-lg-8">
        <div class="section-card">
            <div class="section-title"><i class="fas fa-chart-area" style="color:#6B46C1;"></i> Ventes — 30 derniers jours</div>
            <canvas id="adminSalesChart" height="110"></canvas>
        </div>
    </div>

    {{-- Top branches --}}
    <div class="col-12 col-lg-4">
        <div class="section-card">
            <div class="section-title"><i class="fas fa-trophy" style="color:#6B46C1;"></i> Top Branches (CA)</div>
            @php $maxSales = $topBranches->max('sales_sum_total_amount') ?: 1; @endphp
            @foreach($topBranches as $branch)
            <div class="branch-bar">
                <div class="branch-bar-label">{{ Str::limit($branch->name, 10) }}</div>
                <div class="branch-bar-track">
                    <div class="branch-bar-fill" style="width:{{ ($branch->sales_sum_total_amount / $maxSales) * 100 }}%;"></div>
                </div>
                <div class="branch-bar-val">{{ number_format($branch->sales_sum_total_amount ?? 0, 0, ',', ' ') }}</div>
            </div>
            @endforeach
            <a href="{{ route('admin.finances') }}" class="btn-violet mt-3 w-100 justify-content-center">
                <i class="fas fa-chart-bar"></i> Rapport complet
            </a>
        </div>
    </div>

    {{-- Liens rapides --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="{{ route('admin.branches') }}" class="kpi-card d-flex align-items-center gap-3 text-decoration-none">
                    <div class="kpi-icon mb-0" style="background:#f0ebff;color:#6B46C1;"><i class="fas fa-code-branch"></i></div>
                    <div>
                        <div style="font-weight:700;color:#1a0a3e;font-size:14px;">Branches</div>
                        <div style="font-size:12px;color:#9e8fc0;">{{ $totalBranches }} branches</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('admin.stock') }}" class="kpi-card d-flex align-items-center gap-3 text-decoration-none">
                    <div class="kpi-icon mb-0" style="background:#fff8e1;color:#8a6200;"><i class="fas fa-boxes-stacked"></i></div>
                    <div>
                        <div style="font-weight:700;color:#1a0a3e;font-size:14px;">Stock Global</div>
                        <div style="font-size:12px;color:#9e8fc0;">{{ $totalProducts }} produits</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('admin.invoices') }}" class="kpi-card d-flex align-items-center gap-3 text-decoration-none">
                    <div class="kpi-icon mb-0" style="background:#edfcf2;color:#177f43;"><i class="fas fa-file-invoice"></i></div>
                    <div>
                        <div style="font-weight:700;color:#1a0a3e;font-size:14px;">Facturation</div>
                        <div style="font-size:12px;color:#9e8fc0;">Toutes branches</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('admin.users') }}" class="kpi-card d-flex align-items-center gap-3 text-decoration-none">
                    <div class="kpi-icon mb-0" style="background:#e8f4fd;color:#1a6fb0;"><i class="fas fa-users-cog"></i></div>
                    <div>
                        <div style="font-weight:700;color:#1a0a3e;font-size:14px;">Utilisateurs</div>
                        <div style="font-size:12px;color:#9e8fc0;">{{ $totalUsers }} comptes</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const ctx = document.getElementById('adminSalesChart');
if (ctx) {
    const labels = @json(array_column($salesChart, 'label'));
    const data   = @json(array_column($salesChart, 'total'));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Ventes (FCFA)',
                data,
                borderColor: '#6B46C1',
                backgroundColor: 'rgba(107,70,193,0.07)',
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#6B46C1',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    grid: { color: '#f0ebff' },
                    ticks: { color: '#9e8fc0', callback: v => (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) }
                },
                x: { grid: { display: false }, ticks: { color: '#9e8fc0', maxTicksLimit: 10 } }
            }
        }
    });
}
</script>
@endpush