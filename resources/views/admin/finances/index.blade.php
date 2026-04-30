@extends('layouts.apps')

@section('title', 'Admin · Finances')
@section('page-title', 'Administration · Finances')

@push('styles')
<style>
.kpi-card { background:white; border-radius:16px; padding:22px 20px; box-shadow:0 4px 20px rgba(107,70,193,0.08); border:1px solid rgba(107,70,193,0.06); }
.kpi-card h6 { font-size:12px; font-weight:700; color:#9e8fc0; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px; }
.kpi-card h3 { font-size:24px; font-weight:800; color:#1a0a3e; margin:0; }
.section-card { background:white; border-radius:16px; padding:22px; box-shadow:0 4px 20px rgba(107,70,193,0.08); border:1px solid rgba(107,70,193,0.06); }
.section-title { font-size:14px; font-weight:700; color:#1a0a3e; margin-bottom:16px; }
</style>
@endpush

@section('content')

{{-- Filtres --}}
<form method="GET" class="section-card mb-4">
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label" style="font-size:12px;color:#9e8fc0;font-weight:600;">Année</label>
            <select name="year" class="form-select" style="border-radius:10px;">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" style="font-size:12px;color:#9e8fc0;font-weight:600;">Branche</label>
            <select name="branch_id" class="form-select" style="border-radius:10px;">
                <option value="">Toutes les branches</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn-violet w-100 justify-content-center" style="height:38px;">
                <i class="fas fa-filter"></i> Filtrer
            </button>
        </div>
    </div>
</form>

{{-- KPI --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <h6>Revenus</h6>
            <h3 style="color:#177f43;">{{ number_format($annualRevenue, 0, ',', ' ') }} <small style="font-size:13px;">FCFA</small></h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <h6>Dépenses</h6>
            <h3 style="color:#a32d2d;">{{ number_format($annualExpenses, 0, ',', ' ') }} <small style="font-size:13px;">FCFA</small></h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <h6>Profit net</h6>
            <h3 style="color:{{ $annualProfit >= 0 ? '#177f43' : '#a32d2d' }};">{{ number_format($annualProfit, 0, ',', ' ') }} <small style="font-size:13px;">FCFA</small></h3>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card">
            <h6>Marge</h6>
            <h3 style="color:#6B46C1;">{{ $margin }} %</h3>
        </div>
    </div>
</div>

{{-- Graphique revenus / dépenses --}}
<div class="section-card mb-4">
    <div class="section-title"><i class="fas fa-chart-line me-2" style="color:#6B46C1;"></i>Évolution mensuelle {{ $year }}</div>
    <canvas id="financeChart" height="90"></canvas>
</div>

{{-- Dépenses par type + Revenus par paiement --}}
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="section-card">
            <div class="section-title"><i class="fas fa-tags me-2" style="color:#6B46C1;"></i>Dépenses par type</div>
            <canvas id="expenseChart" height="200"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="section-card">
            <div class="section-title"><i class="fas fa-credit-card me-2" style="color:#6B46C1;"></i>Revenus par mode de paiement</div>
            <canvas id="paymentChart" height="200"></canvas>
        </div>
    </div>
</div>

{{-- Situation par branche --}}
<div class="section-card">
    <div class="section-title"><i class="fas fa-code-branch me-2" style="color:#6B46C1;"></i>Situation par branche — {{ $year }}</div>
    <div class="table-responsive">
        <table class="table" style="font-size:13px;">
            <thead>
                <tr>
                    <th>Branche</th>
                    <th class="text-end">Revenus (FCFA)</th>
                    <th class="text-end">Dépenses (FCFA)</th>
                    <th class="text-end">Profit (FCFA)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branchesSituation as $b)
                <tr>
                    <td class="fw-600">{{ $b->name }}</td>
                    <td class="text-end" style="color:#177f43;font-weight:700;">{{ number_format($b->sales_sum_total_amount ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-end" style="color:#a32d2d;font-weight:700;">{{ number_format($b->expenses_sum_amount ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-end" style="color:{{ $b->profit >= 0 ? '#177f43' : '#a32d2d' }};font-weight:800;">
                        {{ number_format($b->profit, 0, ',', ' ') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

{{-- ✅ FIX : @push('scripts') DOIT être en dehors de @section('content')
     sinon il est ignoré sur certaines versions de Laravel/Blade --}}
@push('scripts')
<script>
const months = @json($months);

// ── Graphique revenus / dépenses ─────────────────────────────────────────────
new Chart(document.getElementById('financeChart'), {
    type: 'line',
    data: {
        labels: months.map(m => m.label),
        datasets: [
            {
                label: 'Revenus',
                data: months.map(m => m.revenue),
                borderColor: '#6B46C1',
                backgroundColor: 'rgba(107,70,193,0.08)',
                tension: 0.4, fill: true, pointRadius: 3,
            },
            {
                label: 'Dépenses',
                data: months.map(m => m.expenses),
                borderColor: '#E24B4A',
                backgroundColor: 'rgba(226,75,74,0.06)',
                tension: 0.4, fill: true, pointRadius: 3,
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: {
                grid: { color: '#f0ebff' },
                ticks: { color: '#9e8fc0', callback: v => (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) }
            },
            x: { grid: { display: false }, ticks: { color: '#9e8fc0' } }
        }
    }
});

// ── Dépenses par type ────────────────────────────────────────────────────────

const expenseData = @json($expensesByType);
new Chart(document.getElementById('expenseChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(expenseData),
        datasets: [{
            data: Object.values(expenseData),
            backgroundColor: ['#6B46C1','#E24B4A','#F59E0B','#10B981','#3B82F6','#8B5CF6'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
    }
});

// ── Revenus par mode de paiement ─────────────────────────────────────────────
const paymentData = @json($salesByPayment);
new Chart(document.getElementById('paymentChart'), {
    type: 'pie',
    data: {
        labels: Object.keys(paymentData),
        datasets: [{
            data: Object.values(paymentData),
            backgroundColor: ['#6B46C1','#10B981','#F59E0B'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
    }
});
</script>
@endpush
