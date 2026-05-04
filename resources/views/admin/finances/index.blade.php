@extends('layouts.apps')

@section('title', 'Admin · Finances')
@section('page-title', 'Administration · Finances')

@push('styles')
<style>
/* ── Base ─────────────────────────────────────────────────────── */
:root {
    --violet: #6B46C1;
    --violet-light: rgba(107,70,193,0.08);
    --green: #177f43;
    --red: #a32d2d;
    --text-muted: #9e8fc0;
    --text-dark: #1a0a3e;
    --card-shadow: 0 4px 20px rgba(107,70,193,0.08);
    --card-border: 1px solid rgba(107,70,193,0.06);
}

/* ── Cards ────────────────────────────────────────────────────── */
.section-card {
    background: white;
    border-radius: 16px;
    padding: 22px;
    box-shadow: var(--card-shadow);
    border: var(--card-border);
}
.section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 16px;
}

/* ── Tabs de période ──────────────────────────────────────────── */
.period-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.period-tab {
    padding: 6px 18px;
    border-radius: 20px;
    border: 1.5px solid rgba(107,70,193,0.25);
    font-size: 13px;
    font-weight: 600;
    color: var(--violet);
    background: white;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
}
.period-tab.active,
.period-tab:hover {
    background: var(--violet);
    color: white;
    border-color: var(--violet);
}

/* ── KPI cards ─────────────────────────────────────────────────── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width:768px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
.kpi-card {
    background: white;
    border-radius: 16px;
    padding: 20px 16px;
    box-shadow: var(--card-shadow);
    border: var(--card-border);
    position: relative;
    overflow: hidden;
}
.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--accent, var(--violet));
}
.kpi-card h6 {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 6px;
}
.kpi-card .kpi-value {
    font-size: 22px;
    font-weight: 800;
    color: var(--text-dark);
    line-height: 1.1;
}
.kpi-card .kpi-sub {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 4px;
}

/* ── Periode summary strip ────────────────────────────────────── */
.period-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
@media (max-width:768px) {
    .period-strip { grid-template-columns: repeat(2, 1fr); }
}
.period-card {
    background: white;
    border-radius: 14px;
    padding: 16px;
    box-shadow: var(--card-shadow);
    border: var(--card-border);
    border-left: 4px solid var(--violet);
}
.period-card.day  { border-left-color: #3B82F6; }
.period-card.week { border-left-color: #8B5CF6; }
.period-card.month{ border-left-color: #10B981; }
.period-card.year { border-left-color: #F59E0B; }

.period-card .pc-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--text-muted);
    margin-bottom: 10px;
}
.period-card .pc-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    margin-bottom: 4px;
}
.period-card .pc-row span:first-child { color: #666; }
.period-card .pc-row span:last-child  { font-weight: 700; }
.period-card .pc-profit {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed #eee;
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    font-weight: 800;
}

/* ── Export PDF button ─────────────────────────────────────────── */
.btn-pdf {
    background: #E24B4A;
    color: white;
    border: none;
    border-radius: 10px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none;
    transition: opacity .2s;
}
.btn-pdf:hover { opacity: .88; color: white; }

/* ── Table ─────────────────────────────────────────────────────── */
.finance-table { font-size: 13px; }
.finance-table th { color: var(--text-muted); font-weight: 700; font-size: 11px; text-transform: uppercase; }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════════
     FILTRES + BOUTON PDF
══════════════════════════════════════════════════════════════ --}}
<form method="GET" action="" class="section-card mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label" style="font-size:12px;color:#9e8fc0;font-weight:600;">Année</label>
            <select name="year" class="form-select" style="border-radius:10px;">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" style="font-size:12px;color:#9e8fc0;font-weight:600;">Branche</label>
            <select name="branch_id" class="form-select" style="border-radius:10px;">
                <option value="">Toutes les branches</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex">
            <button type="submit" class="btn-violet w-100 justify-content-center" style="height:38px;">
                <i class="fas fa-filter"></i> Filtrer
            </button>
        </div>
        {{-- Sélecteur de période PDF --}}
        <div class="col-md-2">
            <label class="form-label" style="font-size:12px;color:#9e8fc0;font-weight:600;">Rapport PDF</label>
            <select id="pdfPeriod" class="form-select" style="border-radius:10px;">
                <option value="day">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
                <option value="month" selected>Ce mois</option>
                <option value="year">Année {{ $year }}</option>
            </select>
        </div>
        <div class="col-md-2">
            <a id="pdfLink" href="#" class="btn-pdf w-100 justify-content-center" style="height:38px;">
                <i class="fas fa-file-pdf"></i> Exporter PDF
            </a>
        </div>
    </div>
</form>

{{-- ══════════════════════════════════════════════════════════════
     APERÇUS RAPIDES : JOUR / SEMAINE / MOIS / ANNÉE
══════════════════════════════════════════════════════════════ --}}
<div class="period-strip">

    {{-- Aujourd'hui --}}
    <div class="period-card day">
        <div class="pc-label"><i class="fas fa-sun me-1"></i> Aujourd'hui</div>
        <div class="pc-row">
            <span>Revenus</span>
            <span style="color:#177f43;">{{ number_format($day['revenue'], 0, ',', ' ') }} F</span>
        </div>
        <div class="pc-row">
            <span>Dépenses</span>
            <span style="color:#a32d2d;">{{ number_format($day['expenses'], 0, ',', ' ') }} F</span>
        </div>
        <div class="pc-row">
            <span>Ventes</span>
            <span style="color:#6B46C1;">{{ $day['sales'] }}</span>
        </div>
        <div class="pc-profit">
            <span>Profit</span>
            <span style="color:{{ $day['profit'] >= 0 ? '#177f43' : '#a32d2d' }};">
                {{ number_format($day['profit'], 0, ',', ' ') }} F
            </span>
        </div>
    </div>

    {{-- Semaine --}}
    <div class="period-card week">
        <div class="pc-label"><i class="fas fa-calendar-week me-1"></i> Cette semaine</div>
        <div class="pc-row">
            <span>Revenus</span>
            <span style="color:#177f43;">{{ number_format($week['revenue'], 0, ',', ' ') }} F</span>
        </div>
        <div class="pc-row">
            <span>Dépenses</span>
            <span style="color:#a32d2d;">{{ number_format($week['expenses'], 0, ',', ' ') }} F</span>
        </div>
        <div class="pc-row">
            <span>Ventes</span>
            <span style="color:#8B5CF6;">{{ $week['sales'] }}</span>
        </div>
        <div class="pc-profit">
            <span>Profit</span>
            <span style="color:{{ $week['profit'] >= 0 ? '#177f43' : '#a32d2d' }};">
                {{ number_format($week['profit'], 0, ',', ' ') }} F
            </span>
        </div>
    </div>

    {{-- Mois --}}
    <div class="period-card month">
        <div class="pc-label"><i class="fas fa-calendar-alt me-1"></i> Ce mois</div>
        <div class="pc-row">
            <span>Revenus</span>
            <span style="color:#177f43;">{{ number_format($month['revenue'], 0, ',', ' ') }} F</span>
        </div>
        <div class="pc-row">
            <span>Dépenses</span>
            <span style="color:#a32d2d;">{{ number_format($month['expenses'], 0, ',', ' ') }} F</span>
        </div>
        <div class="pc-row">
            <span>Ventes</span>
            <span style="color:#10B981;">{{ $month['sales'] }}</span>
        </div>
        <div class="pc-profit">
            <span>Profit</span>
            <span style="color:{{ $month['profit'] >= 0 ? '#177f43' : '#a32d2d' }};">
                {{ number_format($month['profit'], 0, ',', ' ') }} F
            </span>
        </div>
    </div>

    {{-- Année --}}
    <div class="period-card year">
        <div class="pc-label"><i class="fas fa-chart-line me-1"></i> Année {{ $year }}</div>
        <div class="pc-row">
            <span>Revenus</span>
            <span style="color:#177f43;">{{ number_format($annual['revenue'], 0, ',', ' ') }} F</span>
        </div>
        <div class="pc-row">
            <span>Dépenses</span>
            <span style="color:#a32d2d;">{{ number_format($annual['expenses'], 0, ',', ' ') }} F</span>
        </div>
        <div class="pc-row">
            <span>Ventes</span>
            <span style="color:#F59E0B;">{{ $annual['sales'] }}</span>
        </div>
        <div class="pc-profit">
            <span>Profit ({{ $annual['margin'] }}%)</span>
            <span style="color:{{ $annual['profit'] >= 0 ? '#177f43' : '#a32d2d' }};">
                {{ number_format($annual['profit'], 0, ',', ' ') }} F
            </span>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     KPI ANNÉE (grands chiffres)
══════════════════════════════════════════════════════════════ --}}
<div class="kpi-grid mb-4">
    <div class="kpi-card" style="--accent:#10B981;">
        <h6>CA annuel</h6>
        <div class="kpi-value" style="color:#177f43;">{{ number_format($annual['revenue'], 0, ',', ' ') }}</div>
        <div class="kpi-sub">FCFA · {{ $annual['sales'] }} ventes</div>
    </div>
    <div class="kpi-card" style="--accent:#E24B4A;">
        <h6>Dépenses annuelles</h6>
        <div class="kpi-value" style="color:#a32d2d;">{{ number_format($annual['expenses'], 0, ',', ' ') }}</div>
        <div class="kpi-sub">FCFA</div>
    </div>
    <div class="kpi-card" style="--accent:{{ $annual['profit'] >= 0 ? '#177f43' : '#a32d2d' }};">
        <h6>Profit net</h6>
        <div class="kpi-value" style="color:{{ $annual['profit'] >= 0 ? '#177f43' : '#a32d2d' }};">{{ number_format($annual['profit'], 0, ',', ' ') }}</div>
        <div class="kpi-sub">FCFA</div>
    </div>
    <div class="kpi-card" style="--accent:#6B46C1;">
        <h6>Marge nette</h6>
        <div class="kpi-value" style="color:#6B46C1;">{{ $annual['margin'] }}%</div>
        <div class="kpi-sub">Profit / Revenus</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     GRAPHIQUE MENSUEL
══════════════════════════════════════════════════════════════ --}}
<div class="section-card mb-4">
    <div class="section-title"><i class="fas fa-chart-area me-2" style="color:#6B46C1;"></i>Évolution mensuelle {{ $year }}</div>
    <canvas id="financeChart" height="90"></canvas>
</div>

{{-- ══════════════════════════════════════════════════════════════
     DÉPENSES + PAIEMENTS
══════════════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="section-card h-100">
            <div class="section-title"><i class="fas fa-tags me-2" style="color:#6B46C1;"></i>Dépenses par type</div>
            <canvas id="expenseChart" height="220"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="section-card h-100">
            <div class="section-title"><i class="fas fa-credit-card me-2" style="color:#6B46C1;"></i>Revenus par mode de paiement</div>
            <canvas id="paymentChart" height="220"></canvas>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     SITUATION PAR BRANCHE
══════════════════════════════════════════════════════════════ --}}
<div class="section-card">
    <div class="section-title"><i class="fas fa-code-branch me-2" style="color:#6B46C1;"></i>Situation par branche — {{ $year }}</div>
    <div class="table-responsive">
        <table class="table finance-table">
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
                    <td class="fw-bold">{{ $b->name }}</td>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
// ── PDF link dynamique ───────────────────────────────────────────
const pdfBase = "{{ route('admin.finances.pdf') }}?year={{ $year }}&branch_id={{ $branchId }}";
const pdfLink = document.getElementById('pdfLink');
const pdfSel  = document.getElementById('pdfPeriod');

function updatePdfLink() {
    pdfLink.href = pdfBase + '&period=' + pdfSel.value;
}
pdfSel.addEventListener('change', updatePdfLink);
updatePdfLink();

// ── Couleurs communes ────────────────────────────────────────────
const palette = ['#6B46C1','#E24B4A','#F59E0B','#10B981','#3B82F6','#8B5CF6'];
const months  = @json($months);

// ── Graphique revenus / dépenses / profit ────────────────────────
new Chart(document.getElementById('financeChart'), {
    type: 'line',
    data: {
        labels: months.map(m => m.label),
        datasets: [
            {
                label: 'Revenus',
                data: months.map(m => m.revenue),
                borderColor: '#6B46C1',
                backgroundColor: 'rgba(107,70,193,0.07)',
                tension: 0.4, fill: true, pointRadius: 4,
            },
            {
                label: 'Dépenses',
                data: months.map(m => m.expenses),
                borderColor: '#E24B4A',
                backgroundColor: 'rgba(226,75,74,0.05)',
                tension: 0.4, fill: true, pointRadius: 4,
            },
            {
                label: 'Profit',
                data: months.map(m => m.profit),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16,185,129,0.05)',
                tension: 0.4, fill: false, pointRadius: 4,
                borderDash: [5,3],
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: {
                grid: { color: '#f0ebff' },
                ticks: { color: '#9e8fc0', callback: v => v >= 1000 ? (v/1000).toFixed(0)+'k' : v }
            },
            x: { grid: { display: false }, ticks: { color: '#9e8fc0' } }
        }
    }
});

// ── Dépenses par type ────────────────────────────────────────────
const expenseData = @json($expensesByType);
const expenseLabels = {
    livraison: 'Livraison', materiel: 'Matériel',
    salaire: 'Salaires', commande: 'Commandes', autre: 'Autre'
};
new Chart(document.getElementById('expenseChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(expenseData).map(k => expenseLabels[k] ?? k),
        datasets: [{ data: Object.values(expenseData), backgroundColor: palette }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
    }
});

// ── Revenus par paiement ─────────────────────────────────────────
const payData = @json($salesByPayment);
const payLabels = {
    cash: 'Espèces', amana: 'Amana', nita: 'Nita',
    western_union: 'Western Union', moneygram: 'MoneyGram', wave: 'Wave'
};
new Chart(document.getElementById('paymentChart'), {
    type: 'pie',
    data: {
        labels: Object.keys(payData).map(k => payLabels[k] ?? k),
        datasets: [{ data: Object.values(payData), backgroundColor: palette }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
    }
});
</script>
@endpush