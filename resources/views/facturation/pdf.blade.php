<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Facture {{ $invoice->invoice_number }}</title>

<style>

/* ===== A4 ===== */
@page {
    size: A4 portrait;
    margin: 10mm;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #2d2d2d;
}

/* ===== PAGE ===== */
.page {
    width: 100%;
}

/* ===== RECEIPT ===== */
.receipt {
    height: 120mm; /* 🔥 clé pour tenir sur une page */
    padding: 6mm;
    box-sizing: border-box;
    overflow: hidden;
    border: 1px solid #eee;
    border-radius: 6px;
}

/* ===== SEPARATOR ===== */
.separator {
    height: 5mm;
    border-top: 2px dashed #6B46C1;
    text-align: center;
    font-size: 9px;
    color: #6B46C1;
}

/* ===== HEADER ===== */
.header {
    background: #6B46C1;
    color: #fff;
    padding: 5px;
    border-radius: 4px;
}

.header table {
    width: 100%;
}

.logo {
    font-size: 12px;
    font-weight: bold;
}

.right {
    text-align: right;
}

/* ===== INFO ===== */
.info {
    margin: 4px 0;
    font-size: 8px;
    color: #6B46C1;
}

.info table {
    width: 100%;
}

/* ===== TABLE ===== */
.items-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.items-table th {
    background: #6B46C1;
    color: #fff;
    padding: 3px;
    font-size: 8px;
}

.items-table td {
    padding: 3px;
    border-bottom: 1px solid #eee;
    font-size: 8px;
}

.items-table tr:nth-child(even) {
    background: #f9f7ff;
}

.items-table th:nth-child(1) { width: 50%; }
.items-table th:nth-child(2) { width: 10%; text-align: center; }
.items-table th:nth-child(3) { width: 20%; text-align: right; }
.items-table th:nth-child(4) { width: 20%; text-align: right; }

.items-table td:nth-child(2) { text-align: center; }
.items-table td:nth-child(3),
.items-table td:nth-child(4) { text-align: right; }

/* ===== TOTAL ===== */
.totals {
    margin-top: 4px;
}

.totals table {
    width: 60%;
    margin-left: auto;
}

.totals td {
    padding: 3px;
}

.total-final {
    background: #6B46C1;
    color: #fff;
    font-weight: bold;
}

/* ===== FOOTER ===== */
.footer {
    text-align: center;
    font-size: 8px;
    margin-top: 4px;
    color: #6B46C1;
}

</style>
</head>

<body>

<div class="page">

    <!-- ================= REÇU 1 ================= -->
    <div class="receipt">

        <div style="text-align:right; color:#6B46C1;">Copie client</div>

        <div class="header">
            <table>
                <tr>
                    <td>
                        <div class="logo">ILYKEN</div>
                        Niamey<br>
                        +227 97 04 11 47
                    </td>
                    <td class="right">
                        <strong>FACTURE</strong><br>
                        {{ $invoice->invoice_number }}<br>
                        {{ \Carbon\Carbon::parse($invoice->issued_at)->format('d/m/Y H:i') }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="info">
    <table>
        <tr>
            <td>Vente #{{ $invoice->sale_id }}</td>
            <td class="right">
                @php
                    $payLabels = [
                        'cash'          => 'Espèces',
                        'amana'         => 'Amana',
                        'nita'          => 'Nita',
                        'western_union' => 'Western Union',
                        'moneygram'     => 'MoneyGram',
                        'wave'          => 'Wave',
                    ];
                @endphp
                {{ $payLabels[$invoice->sale->payment_method ?? 'cash'] ?? $invoice->sale->payment_method }}
            </td>
        </tr>
    </table>
</div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Qté</th>
                    <th>PU</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>

            @foreach($invoice->sale->saleItems ?? [] as $item)
            <tr>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                <td>{{ number_format($item->total_price, 0, ',', ' ') }}</td>
            </tr>
            @endforeach

            @foreach($invoice->sale->saleServices ?? [] as $svc)
            <tr>
                <td>{{ $svc->service->name ?? '-' }} {{ $svc->service->type ?? '' }}</td>
                <td>1</td>
                <td>{{ number_format($svc->price, 0, ',', ' ') }}</td>
                <td>{{ number_format($svc->price, 0, ',', ' ') }}</td>
            </tr>
            @endforeach

            @foreach($invoice->sale->saleConfections ?? [] as $sc)
            <tr>
                <td>{{ $sc->confection->name ?? '-' }}</td>
                <td>{{ $sc->quantity }}</td>
                <td>{{ number_format($sc->unit_price, 0, ',', ' ') }}</td>
                <td>{{ number_format($sc->total_price, 0, ',', ' ') }}</td>
            </tr>
            @endforeach

            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Total</td>
                    <td class="right">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="total-final">
                    <td>A payer</td>
                    <td class="right">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Merci pour votre confiance
        </div>

    </div>

    <!-- ================= SEPARATEUR ================= -->
    <div class="separator">✂ ✂ ✂</div>

    <!-- ================= REÇU 2 ================= -->
    <div class="receipt">

        <div style="text-align:right; color:#6B46C1;">Copie caisse</div>

        <!-- (contenu identique) -->
        <div class="header">
            <table>
                <tr>
                    <td>
                        <div class="logo">ILYKEN</div>
                        Niamey<br>
                        +227 97 04 11 47
                    </td>
                    <td class="right">
                        <strong>FACTURE</strong><br>
                        {{ $invoice->invoice_number }}<br>
                        {{ \Carbon\Carbon::parse($invoice->issued_at)->format('d/m/Y H:i') }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="info">
            <table>
                <tr>
                    <td>Vente #{{ $invoice->sale_id }}</td>
                    <td class="right">
                        @php
                            $payLabels = [
                                'cash'          => 'Espèces',
                                'amana'         => 'Amana',
                                'nita'          => 'Nita',
                                'western_union' => 'Western Union',
                                'moneygram'     => 'MoneyGram',
                                'wave'          => 'Wave',
                            ];
                        @endphp
                        {{ $payLabels[$invoice->sale->payment_method ?? 'cash'] ?? $invoice->sale->payment_method }}
                    </td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Qté</th>
                    <th>PU</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>

            @foreach($invoice->sale->saleItems ?? [] as $item)
            <tr>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                <td>{{ number_format($item->total_price, 0, ',', ' ') }}</td>
            </tr>
            @endforeach

            @foreach($invoice->sale->saleServices ?? [] as $svc)
            <tr>
                <td>{{ $svc->service->name ?? '-' }}</td>
                <td>1</td>
                <td>{{ number_format($svc->price, 0, ',', ' ') }}</td>
                <td>{{ number_format($svc->price, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
            
            @foreach($invoice->sale->saleConfections ?? [] as $sc)
            <tr>
                <td>{{ $sc->confection->name ?? '-' }}</td>
                <td>{{ $sc->quantity }}</td>
                <td>{{ number_format($sc->unit_price, 0, ',', ' ') }}</td>
                <td>{{ number_format($sc->total_price, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Total</td>
                    <td class="right">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr class="total-final">
                    <td>A payer</td>
                    <td class="right">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Copie interne
        </div>

    </div>

</div>

</body>
</html>