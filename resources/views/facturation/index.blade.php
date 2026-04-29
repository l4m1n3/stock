@extends('layouts.app')

@section('title', 'Facturation')

@section('content')

<style>
    :root { --violet: #6B46C1; --violet-light: #EEEDFE; --violet-hover: #5A3AA6; }

    .inv-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
    .kpi-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #e8e4f7; padding: 18px 20px;
        box-shadow: 0 2px 12px rgba(107,70,193,0.06);
    }
    .kpi-label { font-size: 12px; font-weight: 600; color: #9e8fc0; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
    .kpi-value { font-size: 26px; font-weight: 700; color: #2d2d2d; line-height: 1; }
    .kpi-sub   { font-size: 12px; color: #b0a0d0; margin-top: 4px; }

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
    .inv-table { width: 100%; border-collapse: collapse; }
    .inv-table thead tr { background: #f7f4ff; }
    .inv-table th {
        padding: 12px 18px; font-size: 12px; font-weight: 600;
        color: var(--violet); text-transform: uppercase;
        letter-spacing: 0.05em; border-bottom: 1px solid #f0ebff; white-space: nowrap;
    }
    .inv-table td {
        padding: 13px 18px; font-size: 14px; color: #2d2d2d;
        border-bottom: 1px solid #f7f4ff; vertical-align: middle;
    }
    .inv-table tbody tr:last-child td { border-bottom: none; }
    .inv-table tbody tr:hover { background: #faf8ff; cursor: pointer; }

    .badge-pay {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 99px;
        font-size: 12px; font-weight: 600;
    }
    .pay-cash  { background: #EAF3DE; color: #3B6D11; }
    .pay-amana { background: var(--violet-light); color: var(--violet); }
    .pay-nita  { background: #FAEEDA; color: #854F0B; }

    .action-btns { display: flex; gap: 6px; }
    .btn-icon {
        width: 30px; height: 30px; border-radius: 8px;
        border: 1px solid #e0d9f7; background: #faf8ff;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        color: #9e8fc0; font-size: 13px; transition: all 0.15s;
    }
    .btn-icon:hover { background: var(--violet-light); color: var(--violet); border-color: var(--violet); }

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

    /* Aperçu facture (offscreen print) */
    .invoice-preview {
        background: #fff; border-radius: 16px;
        border: 1px solid #e8e4f7; padding: 32px;
        display: none;
    }
    .invoice-preview.show { display: block; }
    .inv-logo { font-size: 22px; font-weight: 800; color: var(--violet); letter-spacing: -0.5px; }
    .inv-meta { font-size: 13px; color: #9e8fc0; line-height: 1.7; }
    .inv-items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    .inv-items-table th {
        background: #f7f4ff; color: var(--violet);
        padding: 10px 14px; font-size: 13px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.04em;
    }
    .inv-items-table td { padding: 10px 14px; font-size: 14px; border-bottom: 1px solid #f7f4ff; }
    .inv-items-table tr:last-child td { border-bottom: none; }
    .inv-total-row { display: flex; justify-content: flex-end; }
    .inv-total-box {
        background: #faf8ff; border-radius: 12px; padding: 16px 24px;
        border: 1px solid #e0d9f7; min-width: 240px;
    }
    .inv-total-line { display: flex; justify-content: space-between; font-size: 14px; color: #9e8fc0; margin-bottom: 6px; }
    .inv-total-final { display: flex; justify-content: space-between; font-size: 18px; font-weight: 700; color: var(--violet); padding-top: 10px; border-top: 1px solid #e0d9f7; margin-top: 4px; }

    /* Modal */
    .modal-content { border-radius: 16px; border: none; }
    .modal-header-violet {
        background: linear-gradient(135deg, #6B46C1, #9F7AEA);
        border-radius: 16px 16px 0 0; padding: 16px 20px;
    }

    @media print {
        body * { visibility: hidden; }
        #print-zone, #print-zone * { visibility: visible; }
        #print-zone { position: absolute; top: 0; left: 0; width: 100%; }
    }
    @media (max-width: 768px) { .kpi-grid { grid-template-columns: 1fr 1fr; } }
</style>

{{-- Header --}}
<div class="inv-header">
    <div>
        <h4 class="fw-bold mb-0">Facturation</h4>
        <small class="text-muted">Toutes les factures — {{ now()->format('d/m/Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn-outline-violet" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button class="btn-violet" data-bs-toggle="modal" data-bs-target="#modalNewInvoice">
            <i class="fas fa-plus"></i> Nouvelle facture
        </button>
    </div>
</div>

{{-- KPIs --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Total factures</div>
        <div class="kpi-value">{{ $invoices->total() }}</div>
        <div class="kpi-sub">toutes périodes</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Ce mois</div>
        <div class="kpi-value" style="color:var(--violet);">{{ $monthCount }}</div>
        <div class="kpi-sub">factures émises</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">CA ce mois</div>
        <div class="kpi-value" style="color:#3B6D11;">{{ number_format($monthRevenue, 0, ',', ' ') }}</div>
        <div class="kpi-sub">FCFA encaissés</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Ticket moyen</div>
        <div class="kpi-value">{{ $monthCount > 0 ? number_format($monthRevenue / $monthCount, 0, ',', ' ') : '0' }}</div>
        <div class="kpi-sub">FCFA / vente</div>
    </div>
</div>

{{-- Table factures --}}
<div class="panel">
    <div class="panel-header">
        <span class="panel-title"><i class="fas fa-file-invoice me-2" style="color:var(--violet);"></i>Liste des factures</span>
        <div class="toolbar">
            <input type="text" id="inv-search" placeholder="N° facture, montant..." oninput="invFilter()">
            <select id="inv-pay-filter" onchange="invFilter()">
                <option value="">Tous paiements</option>
                <option value="cash">Espèces</option>
                <option value="amana">Amana</option>
                <option value="nita">Nita</option>
                <option value="western_union">Western Union</option>
                <option value="moneygram">MoneyGram</option>
                <option value="wave">Wave</option>
            </select>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="inv-table">
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Date émission</th>
                    <th>Montant</th>
                    <th>Paiement</th>
                    <th>Caissier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="inv-tbody">
                @foreach($invoices as $invoice)
               @php
                    $payBadges = [
                        'cash'          => '<span class="badge-pay pay-cash"> Espèces</span>',
                        'amana'         => '<span class="badge-pay pay-amana"> Amana</span>',
                        'nita'          => '<span class="badge-pay pay-nita"> Nita</span>',
                        'western_union' => '<span class="badge-pay" style="background:#FEF3C7;color:#92400E;"> Western Union</span>',
                        'moneygram'     => '<span class="badge-pay" style="background:#FCE7F3;color:#9D174D;"> MoneyGram</span>',
                        'wave'          => '<span class="badge-pay" style="background:#E0F2FE;color:#0369A1;"> Wave</span>',
                    ];
                @endphp
                <tr data-inv="{{ strtolower($invoice->invoice_number) }}"
                    data-pay="{{ $invoice->sale->payment_method ?? '' }}"
                    onclick="showInvoice({{ $invoice->id }})">
                    <td>
                        <span style="font-weight:700;color:var(--violet);">{{ $invoice->invoice_number }}</span>
                    </td>
                    <td style="color:#9e8fc0;">{{ \Carbon\Carbon::parse($invoice->issued_at)->format('d/m/Y H:i') }}</td>
                    <td style="font-weight:700;">{{ number_format($invoice->total_amount, 0, ',', ' ') }} <span style="font-size:12px;color:#9e8fc0;">FCFA</span></td>
                    <td>{!! $payBadges[$invoice->sale->payment_method ?? 'cash'] ?? '' !!}</td>
                    <td style="font-size:13px;color:#5a4a7a;">{{ $invoice->sale->user->name ?? '—' }}</td>
                    <td onclick="event.stopPropagation()">
                        <div class="action-btns">
                            <button class="btn-icon" title="Aperçu" onclick="showInvoice({{ $invoice->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ route('invoices.pdf', $invoice->id) }}" class="btn-icon" title="Télécharger PDF" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <button class="btn-icon" title="Imprimer" onclick="printInvoice({{ $invoice->id }})">
                                <i class="fas fa-print"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="px-4 py-3 border-top" style="border-color:#f0ebff!important;">
        {{ $invoices->links() }}
    </div>
</div>

{{-- Zone aperçu facture imprimable --}}
<div id="print-zone" class="invoice-preview" id="invoice-detail-panel">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="inv-logo">ILYKEN</div>
            <div class="inv-meta">
                Niamey, Niger<br>
                Tel: +227 XX XX XX XX<br>
                ilyken@email.com
            </div>
        </div>
        <div class="text-end">
            <div style="font-size:22px;font-weight:800;color:#2d2d2d;" id="pv-number">—</div>
            <div class="inv-meta">
                Émise le : <span id="pv-date">—</span><br>
                Paiement : <span id="pv-pay">—</span>
            </div>
        </div>
    </div>

    <hr style="border-color:#f0ebff;">

    <table class="inv-items-table" id="pv-items-table">
        <thead>
            <tr>
                <th>Désignation</th>
                <th style="text-align:center;">Qté</th>
                <th style="text-align:right;">P.U.</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody id="pv-items-body"></tbody>
    </table>

    <div class="inv-total-row">
        <div class="inv-total-box">
            <div class="inv-total-line"><span>Sous-total</span><span id="pv-subtotal">—</span></div>
            <div class="inv-total-final"><span>Total TTC</span><span id="pv-total">—</span></div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <button class="btn-outline-violet" onclick="document.getElementById('print-zone').classList.remove('show')">
            <i class="fas fa-times me-1"></i> Fermer
        </button>
        <div class="d-flex gap-2">
            <button class="btn-outline-violet" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimer
            </button>
            <button class="btn-violet" id="pv-pdf-btn">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </button>
        </div>
    </div>
</div>

{{-- Modal nouvelle facture manuelle --}}
<div class="modal fade" id="modalNewInvoice" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header-violet d-flex align-items-center justify-content-between">
                <h5 class="text-white fw-bold mb-0"><i class="fas fa-file-invoice me-2"></i>Nouvelle facture</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p style="font-size:14px;color:#9e8fc0;">
                    Les factures sont générées automatiquement depuis le
                    <a href="{{ route('sales.index') }}" style="color:var(--violet);font-weight:600;">Point de Vente</a>.
                    Vous pouvez aussi sélectionner une vente existante à facturer manuellement.
                </p>
                <label class="form-label-s" style="font-size:13px;font-weight:600;color:#5a4a7a;margin-bottom:5px;display:block;">
                    Rattacher à une vente
                </label>
                <select style="width:100%;padding:9px 14px;border:1px solid #e0d9f7;border-radius:10px;font-size:14px;color:#2d2d2d;outline:none;">
                    <option value="">— Sélectionner une vente —</option>
                    @foreach($uninvoicedSales as $sale)
                    <option value="{{ $sale->id }}">
                        Vente #{{ $sale->id }} — {{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA — {{ \Carbon\Carbon::parse($sale->sold_at)->format('d/m/Y') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn-violet px-4">
                    <i class="fas fa-check me-1"></i> Générer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Données factures injectées pour l'aperçu JS
const invoicesData = @json($invoicesData);

function invFilter() {
    const q = document.getElementById('inv-search').value.toLowerCase();
    const p = document.getElementById('inv-pay-filter').value;
    document.querySelectorAll('#inv-tbody tr').forEach(row => {
        const invMatch = row.dataset.inv.includes(q);
        const payMatch = !p || row.dataset.pay === p;
        row.style.display = (invMatch && payMatch) ? '' : 'none';
    });
}
const payLabels = {
    cash:          'Espèces',
    amana:         'Amana Mobile Money',
    nita:          'Nita',
    western_union: 'Western Union',
    moneygram:     'MoneyGram',
    wave:          'Wave',
};

function showInvoice(id) {
    const inv = invoicesData[id];
    if (!inv) return;

    document.getElementById('pv-number').textContent = inv.invoice_number;
    document.getElementById('pv-date').textContent   = inv.issued_at;
    document.getElementById('pv-pay').textContent    = payLabels[inv.payment_method] || inv.payment_method;

    const typeIcons = { produit: '📦', service: '🌸', confection: '🎀' };

    const tbody = document.getElementById('pv-items-body');
    tbody.innerHTML = inv.items.map(item => `
        <tr>
            <td>${typeIcons[item.type] ?? ''} ${item.name}</td>
            <td style="text-align:center;">${item.qty}</td>
            <td style="text-align:right;">${item.unit_price.toLocaleString('fr-FR')} FCFA</td>
            <td style="text-align:right;font-weight:600;">${item.total.toLocaleString('fr-FR')} FCFA</td>
        </tr>
    `).join('');

    document.getElementById('pv-subtotal').textContent = inv.total_amount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('pv-total').textContent    = inv.total_amount.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('pv-pdf-btn').onclick = () => window.open(`/factures/${id}/pdf`, '_blank');

    const zone = document.getElementById('print-zone');
    zone.classList.add('show');
    zone.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
// function showInvoice(id) {
//     const inv = invoicesData[id];
//     if (!inv) return;

//     document.getElementById('pv-number').textContent = inv.invoice_number;
//     document.getElementById('pv-date').textContent   = inv.issued_at;
//     document.getElementById('pv-pay').textContent    = { cash: 'Espèces', amana: 'Amana', nita: 'Nita' }[inv.payment_method] || inv.payment_method;

//     const tbody = document.getElementById('pv-items-body');
//     tbody.innerHTML = inv.items.map(item => `
//         <tr>
//             <td>${item.name}</td>
//             <td style="text-align:center;">${item.qty}</td>
//             <td style="text-align:right;">${item.unit_price.toLocaleString('fr-FR')} FCFA</td>
//             <td style="text-align:right;font-weight:600;">${item.total.toLocaleString('fr-FR')} FCFA</td>
//         </tr>
//     `).join('');

//     document.getElementById('pv-subtotal').textContent = inv.total_amount.toLocaleString('fr-FR') + ' FCFA';
//     document.getElementById('pv-total').textContent    = inv.total_amount.toLocaleString('fr-FR') + ' FCFA';
//     document.getElementById('pv-pdf-btn').onclick = () => window.open(`/factures/${id}/pdf`, '_blank');

//     const zone = document.getElementById('print-zone');
//     zone.classList.add('show');
//     zone.scrollIntoView({ behavior: 'smooth', block: 'start' });
// }
</script>

@endsection