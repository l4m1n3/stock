@extends('layouts.app')

@section('title', 'Finances & Rapports')

@section('content')
<style>
    :root {
        --violet: #6B46C1;
        --violet-light: #EEEDFE;
        --violet-hover: #5A3AA6;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    .kpi-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e8e4f7;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(107,70,193,0.06);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(107,70,193,0.12);
    }

    .kpi-label {
        font-size: 12px;
        font-weight: 600;
        color: #9e8fc0;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 8px;
    }

    .kpi-value {
        font-size: 32px;
        font-weight: 700;
        color: #2d2d2d;
        line-height: 1.2;
        margin-bottom: 4px;
    }

    .kpi-sub {
        font-size: 12px;
        color: #b0a0d0;
    }

    .kpi-trend {
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 8px;
    }

    .trend-up { color: #3B6D11; }
    .trend-down { color: #A32D2D; }

    .chart-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 28px;
    }

    .panel {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #e8e4f7;
        box-shadow: 0 4px 20px rgba(107,70,193,0.07);
        overflow: hidden;
        margin-bottom: 28px;
    }

    .panel-header {
        padding: 16px 24px;
        border-bottom: 1px solid #f0ebff;
        background: #faf8ff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .panel-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d2d2d;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-outline-violet-sm {
        padding: 6px 14px;
        background: transparent;
        color: var(--violet);
        border: 1.5px solid var(--violet);
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-outline-violet-sm:hover {
        background: var(--violet-light);
    }

    .finance-table {
        width: 100%;
        border-collapse: collapse;
    }

    .finance-table th {
        padding: 14px 20px;
        font-size: 12px;
        font-weight: 600;
        color: var(--violet);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #f0ebff;
        background: #f7f4ff;
        text-align: left;
    }

    .finance-table td {
        padding: 14px 20px;
        font-size: 14px;
        color: #2d2d2d;
        border-bottom: 1px solid #f7f4ff;
    }

    .finance-table tr:last-child td {
        border-bottom: none;
    }

    .finance-table tr:hover td {
        background: #faf8ff;
    }

    .badge-method {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-cash { background: #EAF3DE; color: #3B6D11; }
    .badge-amana { background: #EEEDFE; color: var(--violet); }
    .badge-nita { background: #FAEEDA; color: #854F0B; }

    .expense-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-bottom: 1px solid #f7f4ff;
    }

    .expense-title {
        font-weight: 600;
        color: #2d2d2d;
    }

    .expense-type {
        font-size: 12px;
        color: #9e8fc0;
        background: #f0ebff;
        padding: 2px 10px;
        border-radius: 30px;
    }

    .expense-amount {
        font-weight: 700;
        color: #A32D2D;
    }

    .text-violet { color: var(--violet); }

    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: 1fr 1fr; }
        .chart-grid { grid-template-columns: 1fr; }
    }
</style>

{{-- Données simulées (à remplacer par les vraies données du contrôleur) --}}
@php
    // Ces variables devraient être passées par le contrôleur
    $totalRevenue = 15842000;
    $totalExpenses = 3245000;
    $netProfit = $totalRevenue - $totalExpenses;
    $avgTicket = 12500;
    $revenueChange = '+12.5%'; // vs mois précédent
    $expenseChange = '+3.2%';
    $profitChange = '+18.7%';
    $ticketChange = '+5.8%';

    // Données pour le graphique revenus (7 derniers jours)
    $revenueLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    $revenueData = [245000, 312000, 298000, 410000, 385000, 520000, 478000];

    // Répartition par moyen de paiement
    $paymentMethods = [
        'cash' => ['label' => 'Espèces', 'value' => 6540000, 'color' => '#639922'],
        'amana' => ['label' => 'Amana', 'value' => 5230000, 'color' => '#6B46C1'],
        'nita' => ['label' => 'Nita', 'value' => 4072000, 'color' => '#EF9F27'],
    ];

    // Top produits/services
    $topItems = [
        ['name' => 'Nettoyage complet', 'type' => 'service', 'revenue' => 1280000, 'quantity' => 64],
        ['name' => 'Savon liquide 5L', 'type' => 'produit', 'revenue' => 985000, 'quantity' => 197],
        ['name' => 'Service semi-privé', 'type' => 'service', 'revenue' => 876000, 'quantity' => 48],
        ['name' => 'Gant microfibre', 'type' => 'produit', 'revenue' => 432000, 'quantity' => 540],
    ];

    // Dépenses récentes
    $recentExpenses = [
        ['title' => 'Livraison produits', 'amount' => 125000, 'type' => 'livraison', 'date' => '2026-04-18'],
        ['title' => 'Achat matériel', 'amount' => 89000, 'type' => 'materiel', 'date' => '2026-04-17'],
        ['title' => 'Salaire staff', 'amount' => 450000, 'type' => 'salaire', 'date' => '2026-04-15'],
        ['title' => 'Réparation machine', 'amount' => 65000, 'type' => 'autre', 'date' => '2026-04-14'],
    ];

    // Ventes récentes
    $recentSales = [
        ['id' => 101, 'date' => '2026-04-19 14:32', 'customer' => 'Mme Diallo', 'amount' => 15800, 'method' => 'cash'],
        ['id' => 100, 'date' => '2026-04-19 11:15', 'customer' => 'M. Konaté', 'amount' => 32500, 'method' => 'amana'],
        ['id' => 99, 'date' => '2026-04-18 17:45', 'customer' => 'Mme Touré', 'amount' => 8700, 'method' => 'nita'],
        ['id' => 98, 'date' => '2026-04-18 10:20', 'customer' => 'M. Sissoko', 'amount' => 42900, 'method' => 'cash'],
    ];
@endphp

<div class="stock-header">
    <div>
        <h4 class="fw-bold mb-0">Finances & Rapports</h4>
        <small class="text-muted">Suivi financier et indicateurs de performance</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn-outline-violet-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimer
        </button>
        <button class="btn-outline-violet-sm" id="exportFinanceBtn">
            <i class="fas fa-file-excel me-1"></i> Exporter
        </button>
    </div>
</div>

{{-- KPIs --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Chiffre d'affaires</div>
        <div class="kpi-value">{{ number_format($totalRevenue, 0, ',', ' ') }} FCFA</div>
        <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> {{ $revenueChange }}</div>
        <div class="kpi-sub">vs mois précédent</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Dépenses totales</div>
        <div class="kpi-value">{{ number_format($totalExpenses, 0, ',', ' ') }} FCFA</div>
        <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> {{ $expenseChange }}</div>
        <div class="kpi-sub">vs mois précédent</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Bénéfice net</div>
        <div class="kpi-value" style="color: var(--violet);">{{ number_format($netProfit, 0, ',', ' ') }} FCFA</div>
        <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> {{ $profitChange }}</div>
        <div class="kpi-sub">marge {{ round(($netProfit / $totalRevenue) * 100, 1) }}%</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Ticket moyen</div>
        <div class="kpi-value">{{ number_format($avgTicket, 0, ',', ' ') }} FCFA</div>
        <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> {{ $ticketChange }}</div>
        <div class="kpi-sub">par vente</div>
    </div>
</div>

{{-- Graphiques --}}
<div class="chart-grid">
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title"><i class="fas fa-chart-line me-2 text-violet"></i>Évolution des ventes (7j)</span>
            <select id="revenuePeriod" class="form-select form-select-sm" style="width: auto; background:#faf8ff; border-color:#e0d9f7;">
                <option value="7">7 jours</option>
                <option value="30">30 jours</option>
                <option value="90">90 jours</option>
            </select>
        </div>
        <div class="p-3">
            <canvas id="revenueChart" height="200"></canvas>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title"><i class="fas fa-chart-pie me-2 text-violet"></i>Répartition par moyen de paiement</span>
        </div>
        <div class="p-3">
            <canvas id="paymentChart" height="200"></canvas>
        </div>
    </div>
</div>

{{-- Top produits/services --}}
<div class="panel">
    <div class="panel-header">
        <span class="panel-title"><i class="fas fa-trophy me-2 text-violet"></i>Meilleures ventes (produits & services)</span>
        <a href="#" class="btn-outline-violet-sm" style="text-decoration: none;">Voir détail →</a>
    </div>
    <div style="overflow-x:auto;">
        <table class="finance-table">
            <thead>
                <tr><th>Article</th><th>Type</th><th>Quantité vendue</th><th>Revenu généré</th></tr>
            </thead>
            <tbody>
                @foreach($topItems as $item)
                <tr>
                    <td><strong>{{ $item['name'] }}</strong></td>
                    <td><span class="badge-status" style="background:#EEEDFE; color:var(--violet);">{{ ucfirst($item['type']) }}</span></td>
                    <td>{{ $item['quantity'] }}</td>
                    <td class="text-violet fw-bold">{{ number_format($item['revenue'], 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Deux colonnes : Dépenses récentes + Ventes récentes --}}
<div class="chart-grid" style="grid-template-columns: 1fr 1fr;">
    {{-- Dépenses --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title"><i class="fas fa-receipt me-2 text-violet"></i>Dernières dépenses</span>
            <a href="#" class="btn-outline-violet-sm" style="text-decoration: none;">Ajouter</a>
        </div>
        <div>
            @forelse($recentExpenses as $expense)
            <div class="expense-row">
                <div>
                    <div class="expense-title">{{ $expense['title'] }}</div>
                    <div class="expense-type">{{ ucfirst($expense['type']) }}</div>
                </div>
                <div class="expense-amount">{{ number_format($expense['amount'], 0, ',', ' ') }} FCFA</div>
                <div class="text-muted small">{{ \Carbon\Carbon::parse($expense['date'])->diffForHumans() }}</div>
            </div>
            @empty
            <div class="p-4 text-center text-muted">Aucune dépense enregistrée</div>
            @endforelse
        </div>
    </div>

    {{-- Ventes récentes --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title"><i class="fas fa-shopping-cart me-2 text-violet"></i>Dernières ventes</span>
            <a href="{{ route('sales.index') }}" class="btn-outline-violet-sm" style="text-decoration: none;">Toutes les ventes</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="finance-table">
                <thead>
                    <tr><th>N° vente</th><th>Date</th><th>Client</th><th>Montant</th><th>Paiement</th></tr>
                </thead>
                <tbody>
                    @foreach($recentSales as $sale)
                    <tr>
                        <td>#{{ $sale['id'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($sale['date'])->format('d/m H:i') }}</td>
                        <td>{{ $sale['customer'] }}</td>
                        <td class="fw-bold">{{ number_format($sale['amount'], 0, ',', ' ') }} FCFA</td>
                        <td>
                            @php
                                $methodClass = match($sale['method']) {
                                    'cash' => 'badge-cash',
                                    'amana' => 'badge-amana',
                                    'nita' => 'badge-nita',
                                    default => ''
                                };
                            @endphp
                            <span class="badge-method {{ $methodClass }}">
                                <i class="fas fa-{{ $sale['method'] === 'cash' ? 'money-bill' : ($sale['method'] === 'amana' ? 'hand-holding-heart' : 'credit-card') }}"></i>
                                {{ ucfirst($sale['method']) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Graphique des revenus
        const ctxRev = document.getElementById('revenueChart').getContext('2d');
        let revenueChart = new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenueLabels) !!},
                datasets: [{
                    label: 'Ventes (FCFA)',
                    data: {!! json_encode($revenueData) !!},
                    borderColor: '#6B46C1',
                    backgroundColor: 'rgba(107,70,193,0.05)',
                    borderWidth: 3,
                    pointBackgroundColor: '#9F7AEA',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.raw.toLocaleString()} FCFA` } }
                },
                scales: {
                    y: { ticks: { callback: (val) => val.toLocaleString() + ' F' }, grid: { color: '#f0ebff' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Graphique des moyens de paiement
        const ctxPay = document.getElementById('paymentChart').getContext('2d');
        new Chart(ctxPay, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_column($paymentMethods, 'label')) !!},
                datasets: [{
                    data: {!! json_encode(array_column($paymentMethods, 'value')) !!},
                    backgroundColor: {!! json_encode(array_column($paymentMethods, 'color')) !!},
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw.toLocaleString()} FCFA (${Math.round(ctx.raw / {{ $totalRevenue }} * 100)}%)` } }
                },
                cutout: '60%'
            }
        });

        // Simulation changement période (à connecter avec AJAX plus tard)
        document.getElementById('revenuePeriod').addEventListener('change', function(e) {
            // Ici, vous feriez un fetch vers une route API pour récupérer les données sur 30/90j
            console.log('Changer période vers', e.target.value);
            // Exemple simplifié : on pourrait modifier les données du graphique
            // revenueChart.data.datasets[0].data = nouvellesDonnees;
            // revenueChart.update();
        });

        // Bouton export (simulation)
        document.getElementById('exportFinanceBtn').addEventListener('click', function() {
            alert('Export des données financières (CSV/Excel) – à implémenter côté serveur');
        });
    });
</script>
@endsection