@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@push('styles')
<style>
.kpi-card {
    background: white;
    border-radius: 16px;
    padding: 22px 20px;
    box-shadow: 0 4px 20px rgba(107,70,193,0.08);
    border: 1px solid rgba(107,70,193,0.06);
    transition: transform 0.15s, box-shadow 0.15s;
    height: 100%;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(107,70,193,0.14);
}
.kpi-icon {
    width: 48px; height: 48px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    margin-bottom: 16px;
    flex-shrink: 0;
}
.kpi-value {
    font-size: 22px;
    font-weight: 800;
    color: #1a0a3e;
    line-height: 1.1;
    margin-bottom: 4px;
}
.kpi-label {
    font-size: 12px;
    color: #9e8fc0;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.kpi-variation {
    font-size: 12px;
    font-weight: 700;
    margin-top: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 99px;
}
.kpi-variation.up   { background: #edfcf2; color: #177f43; }
.kpi-variation.down { background: #fcebeb; color: #a32d2d; }
.kpi-variation.flat { background: #f0ebff; color: #6B46C1; }

.section-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(107,70,193,0.08);
    border: 1px solid rgba(107,70,193,0.06);
    overflow: hidden;
}
.section-card-header {
    padding: 16px 22px;
    border-bottom: 1px solid #f0ebff;
    font-size: 14px;
    font-weight: 700;
    color: #1a0a3e;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-stock {
    background: #fff8e1;
    border: none;
    border-left: 4px solid #F59E0B;
    border-radius: 12px;
    padding: 14px 18px;
    font-size: 13px;
    color: #6b4a00;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 24px;
}
.alert-stock i { flex-shrink: 0; margin-top: 1px; color: #F59E0B; }

.sale-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    border-bottom: 1px solid #f5f3ff;
    font-size: 13px;
}
.sale-item:last-child { border: none; }
.sale-item-name  { font-weight: 600; color: #1a0a3e; }
.sale-item-date  { font-size: 11px; color: #9e8fc0; margin-top: 2px; }
.sale-item-amount { font-weight: 800; color: #6B46C1; white-space: nowrap; }

.payment-badge {
    font-size: 10px; font-weight: 700; padding: 2px 7px;
    border-radius: 99px; text-transform: uppercase;
}
.payment-cash  { background: #edfcf2; color: #177f43; }
.payment-amana { background: #e8f4fd; color: #1a6fb0; }
.payment-nita  { background: #f0ebff; color: #6B46C1; }
</style>
@endpush

@section('content')

{{-- ── Alerte stock critique ──────────────────────────────────────────────── --}}
@if($criticalProducts->isNotEmpty())
<div class="alert-stock">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
        <strong>Alertes stock :</strong>
        @foreach($criticalProducts as $p)
            <span style="margin-right:6px;">
                {{ $p->name }}
                <span style="background:#f59e0b20;color:#8a6200;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;">
                    {{ $p->stock_quantity }} restant{{ $p->stock_quantity > 1 ? 's' : '' }}
                </span>
            </span>
            @if(!$loop->last) · @endif
        @endforeach
    </div>
</div>
@endif

{{-- ── KPI ──────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Ventes du jour --}}
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#f0ebff;color:#6B46C1;">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="kpi-value">{{ number_format($todaySales, 0, ',', ' ') }}</div>
            <div class="kpi-label">Ventes aujourd'hui (FCFA)</div>
            @if($todayVariation !== null)
                <div class="kpi-variation {{ $todayVariation >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ $todayVariation >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($todayVariation) }}% vs hier
                </div>
            @else
                <div class="kpi-variation flat"><i class="fas fa-minus"></i> Pas de données hier</div>
            @endif
        </div>
    </div>

    {{-- Chiffre du mois --}}
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#edfcf2;color:#177f43;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="kpi-value">{{ number_format($monthSales, 0, ',', ' ') }}</div>
            <div class="kpi-label">CA du mois (FCFA)</div>
            <div class="kpi-variation {{ $monthProfit >= 0 ? 'up' : 'down' }}">
                <i class="fas fa-wallet"></i>
                Bénéfice : {{ number_format($monthProfit, 0, ',', ' ') }}
            </div>
        </div>
    </div>

    {{-- Commandes en attente --}}
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:#fff8e1;color:#8a6200;">
                <i class="fas fa-truck-ramp-box"></i>
            </div>
            <div class="kpi-value">{{ $pendingOrders }}</div>
            <div class="kpi-label">Commandes en attente</div>
            @if($pendingOrders > 0)
                <a href="{{ route('purchases.index') }}" style="font-size:12px;color:#8a6200;font-weight:600;text-decoration:none;margin-top:6px;display:inline-block;">
                    Voir les commandes →
                </a>
            @endif
        </div>
    </div>

    {{-- Articles en alerte --}}
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <div class="kpi-icon" style="background:{{ $criticalProducts->count() > 0 ? '#fcebeb' : '#edfcf2' }};color:{{ $criticalProducts->count() > 0 ? '#a32d2d' : '#177f43' }};">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="kpi-value" style="color:{{ $criticalProducts->count() > 0 ? '#a32d2d' : '#177f43' }};">
                {{ $criticalProducts->count() }}
            </div>
            <div class="kpi-label">Articles en alerte stock</div>
            @if($criticalProducts->count() > 0)
                <a href="{{ route('stock.index') }}" style="font-size:12px;color:#a32d2d;font-weight:600;text-decoration:none;margin-top:6px;display:inline-block;">
                    Voir le stock →
                </a>
            @endif
        </div>
    </div>

</div>

{{-- ── Graphique + Dernières ventes ────────────────────────────────────── --}}
<div class="row g-4">

    <div class="col-12 col-lg-8">
        <div class="section-card">
            <div class="section-card-header">
                <i class="fas fa-chart-area" style="color:#6B46C1;"></i>
                Ventes des 7 derniers jours
            </div>
            <div style="padding:20px;">
                <canvas id="salesChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="section-card" style="height:100%;">
            <div class="section-card-header">
                <i class="fas fa-receipt" style="color:#6B46C1;"></i>
                Dernières ventes
            </div>

            @forelse($latestSales as $sale)
            <div class="sale-item">
                <div>
                    <div class="sale-item-name">
                        {{ $sale->user->name ?? '—' }}
                    </div>
                    <div class="sale-item-date">
                        {{ \Carbon\Carbon::parse($sale->sold_at)->format('d/m/Y H:i') }}
                        <span class="payment-badge payment-{{ $sale->payment_method }}">
                            {{ $sale->payment_method }}
                        </span>
                    </div>
                </div>
                <div class="sale-item-amount">
                    {{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA
                </div>
            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#9e8fc0;font-size:13px;">
                <i class="fas fa-receipt fa-2x mb-2 d-block" style="color:#d8d0f5;"></i>
                Aucune vente aujourd'hui
            </div>
            @endforelse

            <div style="padding:14px 20px;border-top:1px solid #f0ebff;">
                <a href="{{ route('sales.index') }}" class="btn-violet w-100 justify-content-center">
                    <i class="fas fa-cash-register"></i> Point de vente
                </a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const chartData = @json($salesChart);

new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: chartData.map(d => d.label),
        datasets: [{
            label: 'Ventes (FCFA)',
            data: chartData.map(d => d.total),
            borderColor: '#6B46C1',
            backgroundColor: 'rgba(107,70,193,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#6B46C1',
            pointBorderColor: 'white',
            pointBorderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ' + ctx.parsed.y.toLocaleString('fr-FR') + ' FCFA'
                }
            }
        },
        scales: {
            y: {
                grid: { color: '#f0ebff' },
                ticks: {
                    color: '#9e8fc0',
                    callback: v => v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v
                }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#9e8fc0' }
            }
        }
    }
});
</script>
@endpush