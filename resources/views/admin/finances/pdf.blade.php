<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rapport Financier</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 12px;
    color: #1a0a3e;
    background: #fff;
}

/* ── En-tête ──────────────────────────────────────────────── */
.header {
    background: linear-gradient(135deg, #6B46C1, #4c2fa1);
    color: white;
    padding: 24px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.header .logo { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
.header .meta { text-align: right; font-size: 11px; opacity: .85; }
.header .meta strong { display: block; font-size: 15px; font-weight: 700; opacity: 1; }

.report-title {
    background: #f5f0ff;
    padding: 14px 28px;
    border-bottom: 2px solid #6B46C1;
    font-size: 13px;
    font-weight: 700;
    color: #6B46C1;
    display: flex;
    justify-content: space-between;
}

/* ── Corps ────────────────────────────────────────────────── */
.body { padding: 24px 28px; }

/* ── KPI grid ─────────────────────────────────────────────── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 24px;
}
.kpi-card {
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #e8e0ff;
    background: #faf7ff;
    border-top: 4px solid #6B46C1;
}
.kpi-card.green { border-top-color: #177f43; background: #f4fbf7; border-color: #d0eddb; }
.kpi-card.red   { border-top-color: #a32d2d; background: #fdf5f5; border-color: #f0d0d0; }

.kpi-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #9e8fc0;
    margin-bottom: 6px;
}
.kpi-value {
    font-size: 20px;
    font-weight: 800;
    color: #1a0a3e;
}
.kpi-value.green { color: #177f43; }
.kpi-value.red   { color: #a32d2d; }
.kpi-value.violet{ color: #6B46C1; }
.kpi-sub { font-size: 10px; color: #9e8fc0; margin-top: 3px; }

/* ── Sections ─────────────────────────────────────────────── */
.section {
    margin-bottom: 24px;
}
.section-header {
    font-size: 13px;
    font-weight: 700;
    color: #1a0a3e;
    border-bottom: 2px solid #f0ebff;
    padding-bottom: 8px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-header .dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #6B46C1;
    flex-shrink: 0;
}

/* ── Tables ───────────────────────────────────────────────── */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
}
thead tr { background: #6B46C1; color: white; }
thead th { padding: 9px 12px; text-align: left; font-weight: 600; letter-spacing: .03em; }
thead th.right { text-align: right; }
tbody tr { border-bottom: 1px solid #f0ebff; }
tbody tr:nth-child(even) { background: #faf7ff; }
tbody td { padding: 9px 12px; }
tbody td.right { text-align: right; font-weight: 700; }
tbody td.green  { color: #177f43; }
tbody td.red    { color: #a32d2d; }
tfoot tr { background: #f5f0ff; font-weight: 800; border-top: 2px solid #6B46C1; }
tfoot td { padding: 10px 12px; }
tfoot td.right { text-align: right; }

/* ── Pied de page ─────────────────────────────────────────── */
.footer {
    margin-top: 30px;
    padding-top: 14px;
    border-top: 2px solid #f0ebff;
    display: flex;
    justify-content: space-between;
    font-size: 10px;
    color: #9e8fc0;
}
.footer .confidential {
    background: #f5f0ff;
    color: #6B46C1;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 10px;
}

/* ── Badge profit ─────────────────────────────────────────── */
.profit-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
}
.profit-badge.pos { background: #d0eddb; color: #177f43; }
.profit-badge.neg { background: #f0d0d0; color: #a32d2d; }
</style>
</head>
<body>

{{-- EN-TÊTE --}}
<div class="header">
    <div>
        <div class="logo">Rapport Financier — Administration</div>
        <div style="font-size:12px;opacity:.8;margin-top:4px;">Rapport Financier — Administration</div>
    </div>
    <div class="meta">
        <strong>{{ $periodLabel }}</strong>
        {{ $branchName }}<br>
        Généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</div>

<div class="report-title">
    <span>📊 Rapport de performance financière</span>
    <span>{{ $branchName }}</span>
</div>

<div class="body">

    {{-- KPIs PRINCIPAUX --}}
    <div class="kpi-grid" style="margin-bottom:24px;">
        <div class="kpi-card green">
            <div class="kpi-label">Revenus</div>
            <div class="kpi-value green">{{ number_format($revenue, 0, ',', ' ') }}</div>
            <div class="kpi-sub">FCFA</div>
        </div>
        <div class="kpi-card red">
            <div class="kpi-label">Dépenses</div>
            <div class="kpi-value red">{{ number_format($expenses, 0, ',', ' ') }}</div>
            <div class="kpi-sub">FCFA</div>
        </div>
        <div class="kpi-card" style="border-top-color:{{ $profit >= 0 ? '#177f43' : '#a32d2d' }};">
            <div class="kpi-label">Profit net</div>
            <div class="kpi-value {{ $profit >= 0 ? 'green' : 'red' }}">{{ number_format($profit, 0, ',', ' ') }}</div>
            <div class="kpi-sub">FCFA</div>
        </div>
    </div>

    <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px;">
        <div class="kpi-card">
            <div class="kpi-label">Marge nette</div>
            <div class="kpi-value violet">{{ $margin }} %</div>
            <div class="kpi-sub">Profit / Revenus</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Nombre de ventes</div>
            <div class="kpi-value violet">{{ number_format($sales, 0, ',', ' ') }}</div>
            <div class="kpi-sub">transactions</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Ticket moyen</div>
            <div class="kpi-value violet">{{ number_format($avgTicket, 0, ',', ' ') }}</div>
            <div class="kpi-sub">FCFA / vente</div>
        </div>
    </div>

    {{-- DÉPENSES PAR TYPE --}}
    @if($expensesByType->count())
    <div class="section">
        <div class="section-header">
            <span class="dot" style="background:#E24B4A;"></span>
            Détail des dépenses par catégorie
        </div>
        <table>
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th class="right">Montant (FCFA)</th>
                    <th class="right">Part (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByType as $exp)
                <tr>
                    <td>{{ $typeLabels[$exp->type] ?? ucfirst($exp->type) }}</td>
                    <td class="right red">{{ number_format($exp->total, 0, ',', ' ') }}</td>
                    <td class="right">
                        {{ $expenses > 0 ? round(($exp->total / $expenses) * 100, 1) : 0 }} %
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Total dépenses</strong></td>
                    <td class="right"><strong>{{ number_format($expenses, 0, ',', ' ') }}</strong></td>
                    <td class="right"><strong>100 %</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- REVENUS PAR MODE DE PAIEMENT --}}
    @if($salesByPayment->count())
    <div class="section">
        <div class="section-header">
            <span class="dot" style="background:#10B981;"></span>
            Revenus par mode de paiement
        </div>
        <table>
            <thead>
                <tr>
                    <th>Mode de paiement</th>
                    <th class="right">Montant (FCFA)</th>
                    <th class="right">Part (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesByPayment as $pay)
                <tr>
                    <td>{{ $payLabels[$pay->payment_method] ?? ucfirst($pay->payment_method) }}</td>
                    <td class="right green">{{ number_format($pay->total, 0, ',', ' ') }}</td>
                    <td class="right">
                        {{ $revenue > 0 ? round(($pay->total / $revenue) * 100, 1) : 0 }} %
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Total revenus</strong></td>
                    <td class="right"><strong>{{ number_format($revenue, 0, ',', ' ') }}</strong></td>
                    <td class="right"><strong>100 %</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- SITUATION PAR BRANCHE --}}
    @if($branchesSituation->count() > 1)
    <div class="section">
        <div class="section-header">
            <span class="dot" style="background:#F59E0B;"></span>
            Situation par branche
        </div>
        <table>
            <thead>
                <tr>
                    <th>Branche</th>
                    <th class="right">Revenus (FCFA)</th>
                    <th class="right">Dépenses (FCFA)</th>
                    <th class="right">Profit (FCFA)</th>
                    <th class="right">Marge</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branchesSituation as $b)
                @php
                    $bRev  = $b->rev ?? 0;
                    $bExp  = $b->exp ?? 0;
                    $bProf = $b->profit;
                    $bMarg = $bRev > 0 ? round(($bProf / $bRev) * 100, 1) : 0;
                @endphp
                <tr>
                    <td><strong>{{ $b->name }}</strong></td>
                    <td class="right green">{{ number_format($bRev, 0, ',', ' ') }}</td>
                    <td class="right red">{{ number_format($bExp, 0, ',', ' ') }}</td>
                    <td class="right {{ $bProf >= 0 ? 'green' : 'red' }}">{{ number_format($bProf, 0, ',', ' ') }}</td>
                    <td class="right">
                        <span class="profit-badge {{ $bProf >= 0 ? 'pos' : 'neg' }}">{{ $bMarg }}%</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ANALYSE SYNTHÈSE --}}
    <div class="section">
        <div class="section-header">
            <span class="dot" style="background:#6B46C1;"></span>
            Synthèse & analyse
        </div>
        <table>
            <tbody>
                <tr>
                    <td style="width:40%;color:#666;">Solde (Revenus − Dépenses)</td>
                    <td class="right {{ $profit >= 0 ? 'green' : 'red' }}">
                        {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 0, ',', ' ') }} FCFA
                    </td>
                </tr>
                <tr>
                    <td style="color:#666;">Taux de marge nette</td>
                    <td class="right">{{ $margin }} %</td>
                </tr>
                <tr>
                    <td style="color:#666;">Part des dépenses sur revenus</td>
                    <td class="right">
                        {{ $revenue > 0 ? round(($expenses / $revenue) * 100, 1) : 0 }} %
                    </td>
                </tr>
                <tr>
                    <td style="color:#666;">Transactions réalisées</td>
                    <td class="right">{{ number_format($sales, 0, ',', ' ') }} ventes</td>
                </tr>
                <tr>
                    <td style="color:#666;">Revenu moyen par vente</td>
                    <td class="right">{{ number_format($avgTicket, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>{{-- /body --}}

{{-- PIED DE PAGE --}}
<div class="footer" style="padding:0 28px 20px;">
    <div>
        Document confidentiel — Usage interne uniquement<br>
        Généré automatiquement par GestionPro le {{ now()->format('d/m/Y à H:i') }}
    </div>
    <div>
        <span class="confidential">CONFIDENTIEL</span>
    </div>
</div>

</body>
</html>