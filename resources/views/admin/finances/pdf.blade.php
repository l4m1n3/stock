<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport Financier</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 12px;
    color: #1a0a3e;
    background: #fff;
}

/* ── En-tête (table au lieu de flex) ─────────────────────── */
.header-table {
    width: 100%;
    background: #6B46C1; /* dégradé non supporté correctement -> couleur pleine */
    color: white;
}
.header-table td {
    padding: 24px 28px;
    vertical-align: middle;
}
.header-table .logo { font-size: 22px; font-weight: 800; }
.header-table .tagline { font-size: 12px; opacity: .85; margin-top: 4px; }
.header-table .meta { text-align: right; font-size: 11px; }
.header-table .meta strong { display: block; font-size: 15px; font-weight: 700; }

.report-title-table {
    width: 100%;
    background: #f5f0ff;
    border-bottom: 2px solid #6B46C1;
}
.report-title-table td {
    padding: 14px 28px;
    font-size: 13px;
    font-weight: 700;
    color: #6B46C1;
}
.report-title-table .right { text-align: right; }

/* ── Corps ────────────────────────────────────────────────── */
.body { padding: 24px 28px; }

/* ── KPI grid (table au lieu de grid) ────────────────────── */
.kpi-table { width: 100%; border-collapse: separate; border-spacing: 7px; margin-bottom: 10px; }
.kpi-table td { width: 33.33%; vertical-align: top; }
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
.section { margin-bottom: 24px; }
.section-header {
    font-size: 13px;
    font-weight: 700;
    color: #1a0a3e;
    border-bottom: 2px solid #f0ebff;
    padding-bottom: 8px;
    margin-bottom: 14px;
}
.section-header .dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #6B46C1;
    margin-right: 8px;
}

/* ── Tables de données ───────────────────────────────────── */
table.data { width: 100%; border-collapse: collapse; font-size: 11.5px; }
table.data thead tr { background: #6B46C1; color: white; }
table.data thead th { padding: 9px 12px; text-align: left; font-weight: 600; letter-spacing: .03em; }
table.data thead th.right { text-align: right; }
table.data tbody tr { border-bottom: 1px solid #f0ebff; }
table.data tbody tr.even { background: #faf7ff; }
table.data tbody td { padding: 9px 12px; }
table.data tbody td.right { text-align: right; font-weight: 700; }
table.data tbody td.green  { color: #177f43; }
table.data tbody td.red    { color: #a32d2d; }
table.data tfoot tr { background: #f5f0ff; font-weight: 800; border-top: 2px solid #6B46C1; }
table.data tfoot td { padding: 10px 12px; }
table.data tfoot td.right { text-align: right; }

/* ── Pied de page (table au lieu de flex) ────────────────── */
.footer-table { width: 100%; margin-top: 30px; padding-top: 14px; border-top: 2px solid #f0ebff; }
.footer-table td { font-size: 10px; color: #9e8fc0; vertical-align: middle; }
.footer-table .right { text-align: right; }
.confidential {
    display: inline-block;
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
<table class="header-table">
    <tr>
        <td style="width:60%;">
            <div class="logo">Rapport Financier — Administration</div>
            <div class="tagline">Rapport Financier — Administration</div>
        </td>
        <td class="meta">
            <strong>{{ $periodLabel }}</strong>
            {{ $branchName }}<br>
            Généré le {{ now()->format('d/m/Y à H:i') }}
        </td>
    </tr>
</table>

<table class="report-title-table">
    <tr>
        <td>📊 Rapport de performance financière</td>
        <td class="right">{{ $branchName }}</td>
    </tr>
</table>

<div class="body">

    {{-- KPIs PRINCIPAUX --}}
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-card green">
                    <div class="kpi-label">Revenus</div>
                    <div class="kpi-value green">{{ number_format($revenue, 0, ',', ' ') }}</div>
                    <div class="kpi-sub">FCFA</div>
                </div>
            </td>
            <td>
                <div class="kpi-card red">
                    <div class="kpi-label">Dépenses</div>
                    <div class="kpi-value red">{{ number_format($expenses, 0, ',', ' ') }}</div>
                    <div class="kpi-sub">FCFA</div>
                </div>
            </td>
            <td>
                <div class="kpi-card" style="border-top-color:{{ $profit >= 0 ? '#177f43' : '#a32d2d' }};">
                    <div class="kpi-label">Profit net</div>
                    <div class="kpi-value {{ $profit >= 0 ? 'green' : 'red' }}">{{ number_format($profit, 0, ',', ' ') }}</div>
                    <div class="kpi-sub">FCFA</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="kpi-table" style="margin-bottom:28px;">
        <tr>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Marge nette</div>
                    <div class="kpi-value violet">{{ $margin }} %</div>
                    <div class="kpi-sub">Profit / Revenus</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Nombre de ventes</div>
                    <div class="kpi-value violet">{{ number_format($sales, 0, ',', ' ') }}</div>
                    <div class="kpi-sub">transactions</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Ticket moyen</div>
                    <div class="kpi-value violet">{{ number_format($avgTicket, 0, ',', ' ') }}</div>
                    <div class="kpi-sub">FCFA / vente</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- DÉPENSES PAR TYPE --}}
    @if($expensesByType->count())
    <div class="section">
        <div class="section-header"><span class="dot" style="background:#E24B4A;"></span>Détail des dépenses par catégorie</div>
        <table class="data">
            <thead>
                <tr>
                    <th>Catégorie</th>
                    <th class="right">Montant (FCFA)</th>
                    <th class="right">Part (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesByType as $i => $exp)
                <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
                    <td>{{ $typeLabels[$exp->type] ?? ucfirst($exp->type) }}</td>
                    <td class="right red">{{ number_format($exp->total, 0, ',', ' ') }}</td>
                    <td class="right">{{ $expenses > 0 ? round(($exp->total / $expenses) * 100, 1) : 0 }} %</td>
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
        <div class="section-header"><span class="dot" style="background:#10B981;"></span>Revenus par mode de paiement</div>
        <table class="data">
            <thead>
                <tr>
                    <th>Mode de paiement</th>
                    <th class="right">Montant (FCFA)</th>
                    <th class="right">Part (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesByPayment as $i => $pay)
                <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
                    <td>{{ $payLabels[$pay->payment_method] ?? ucfirst($pay->payment_method) }}</td>
                    <td class="right green">{{ number_format($pay->total, 0, ',', ' ') }}</td>
                    <td class="right">{{ $revenue > 0 ? round(($pay->total / $revenue) * 100, 1) : 0 }} %</td>
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
        <div class="section-header"><span class="dot" style="background:#F59E0B;"></span>Situation par branche</div>
        <table class="data">
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
                @foreach($branchesSituation as $i => $b)
                @php
                    $bRev  = $b->rev ?? 0;
                    $bExp  = $b->exp ?? 0;
                    $bProf = $b->profit;
                    $bMarg = $bRev > 0 ? round(($bProf / $bRev) * 100, 1) : 0;
                @endphp
                <tr class="{{ $i % 2 === 1 ? 'even' : '' }}">
                    <td><strong>{{ $b->name }}</strong></td>
                    <td class="right green">{{ number_format($bRev, 0, ',', ' ') }}</td>
                    <td class="right red">{{ number_format($bExp, 0, ',', ' ') }}</td>
                    <td class="right {{ $bProf >= 0 ? 'green' : 'red' }}">{{ number_format($bProf, 0, ',', ' ') }}</td>
                    <td class="right"><span class="profit-badge {{ $bProf >= 0 ? 'pos' : 'neg' }}">{{ $bMarg }}%</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ANALYSE SYNTHÈSE --}}
    <div class="section">
        <div class="section-header"><span class="dot" style="background:#6B46C1;"></span>Synthèse & analyse</div>
        <table class="data">
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
                    <td class="right">{{ $revenue > 0 ? round(($expenses / $revenue) * 100, 1) : 0 }} %</td>
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
<table class="footer-table" style="padding:0 28px 20px;">
    <tr>
        <td>
            Document confidentiel — Usage interne uniquement<br>
            Généré automatiquement par GestionPro le {{ now()->format('d/m/Y à H:i') }}
        </td>
        <td class="right"><span class="confidential">CONFIDENTIEL</span></td>
    </tr>
</table>

</body>
</html>