<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Détail prestation</title>

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
    padding: 6mm;
    box-sizing: border-box;
    overflow: hidden;
    border: 1px solid #eee;
    border-radius: 6px;
}

/* ===== HEADER ===== */
.header {
    background: #fff;
    color: #000000;
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

.client-block {
    font-size: 8px;
    color: #2d2d2d;
    margin: 2px 0 3px 0;
    padding: 3px 5px;
    background: #f9f7ff;
    border-left: 2px solid #6B46C1;
    border-radius: 2px;
}

.client-block .client-name {
    font-weight: bold;
    color: #4a2e9e;
}

.client-block .client-phone {
    color: #7a6aaa;
}

/* ===== TABLE DETAILS ===== */
.details-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 6px;
}

.details-table td {
    padding: 4px 5px;
    border-bottom: 1px solid #eee;
    font-size: 8px;
    vertical-align: top;
}

.details-table tr:nth-child(even) {
    background: #f9f7ff;
}

.details-table td:first-child {
    width: 35%;
    font-weight: bold;
    color: #4a2e9e;
    background: #f3f0ff;
}

/* ===== STATUS BADGE ===== */
.status {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 8px;
    font-weight: bold;
}

.pending    { background: #FEF3C7; color: #92400E; }
.confirmed  { background: #DBEAFE; color: #1E40AF; }
.completed  { background: #D1FAE5; color: #065F46; }
.cancelled  { background: #FEE2E2; color: #991B1B; }

/* ===== NOTES BLOCK ===== */
.notes-block {
    margin-top: 6px;
    padding: 4px 6px;
    background: #f9f7ff;
    border-left: 2px solid #6B46C1;
    border-radius: 2px;
    font-size: 8px;
    color: #2d2d2d;
}

.notes-block .notes-label {
    font-weight: bold;
    color: #4a2e9e;
    margin-bottom: 2px;
}

/* ===== FOOTER ===== */
.footer {
    text-align: center;
    font-size: 8px;
    margin-top: 4px;
    color: #6B46C1;
}

/* ===== SEPARATOR ===== */
.separator {
    height: 5mm;
    border-top: 2px dashed #6B46C1;
    text-align: center;
    font-size: 9px;
    color: #6B46C1;
}

/* ===== NO PRINT ===== */
@media print {
    .no-print { display: none; }
}

</style>
</head>

<body>

<div class="no-print" style="margin-bottom: 10px;">
    <button onclick="window.print()">Imprimer</button>
</div>

<div class="page">

    <!-- ================= COPIE CLIENT ================= -->
    <div class="receipt">

        <div style="text-align:right; color:#6B46C1;">Copie client</div>

        <div class="header">
            <table>
                <tr>
                    <td style="width: 20%; text-align: left; border: none;">
                        {{-- <img src="data:image/jpeg;base64,{{ $logo }}" style="height:60px;"> --}}
                    </td>
                    <td>
                        <div class="logo">ILYKEN</div>
                        Niamey<br>
                        
                    </td>
                    <td class="right">
                        <strong>PRESTATION</strong><br>
                        #{{ $serviceSchedule->id }}<br>
                        {{ now()->format('d/m/Y H:i') }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Client --}}
        <div class="client-block">
            <span class="client-name">{{ $serviceSchedule->client_name ?? 'Non renseigné' }}</span>
            @if($serviceSchedule->contact_number)
                &nbsp;·&nbsp;<span class="client-phone">{{ $serviceSchedule->contact_number }}</span>
            @endif
        </div>

        <div class="info">
            <table>
                <tr>
                    <td>Fiche prestation</td>
                    <td class="right">
                        @php
                            $statusLabels = [
                                'pending'   => 'En attente',
                                'confirmed' => 'Confirmé',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                            ];
                        @endphp
                        {{ $statusLabels[$serviceSchedule->status] ?? ucfirst($serviceSchedule->status) }}
                    </td>
                </tr>
            </table>
        </div>

        <table class="details-table">
            <tr>
                <td>Service</td>
                <td>{{ $serviceSchedule->service->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Adresse</td>
                <td>{{ $serviceSchedule->address ?? '-' }}</td>
            </tr>
            <tr>
                <td>Date prévue</td>
                <td>{{ \Carbon\Carbon::parse($serviceSchedule->scheduled_date)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Heure prévue</td>
                <td>{{ $serviceSchedule->scheduled_time ?? '--:--' }}</td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>
                    <span class="status {{ $serviceSchedule->status }}">
                        {{ $statusLabels[$serviceSchedule->status] ?? ucfirst($serviceSchedule->status) }}
                    </span>
                </td>
            </tr>
        </table>

        @if($serviceSchedule->notes)
        <div class="notes-block">
            <div class="notes-label">Notes</div>
            {{ $serviceSchedule->notes }}
        </div>
        @endif

        <div class="footer">
            Merci pour votre confiance
        </div>

    </div>

    <!-- ================= SEPARATEUR ================= -->
    <div class="separator">✂ ✂ ✂</div>

    <!-- ================= COPIE CAISSE ================= -->
    <div class="receipt">

        <div style="text-align:right; color:#6B46C1;">Copie interne</div>

        <div class="header">
            <table>
                <tr>
                    <td style="width: 20%; text-align: left; border: none;">
                        {{-- <img src="data:image/jpeg;base64,{{ $logo }}" style="height:60px;"> --}}
                    </td>
                    <td>
                        <div class="logo">ILYKEN</div>
                        Niamey<br>
                        +227 97 04 11 47
                    </td>
                    <td class="right">
                        <strong>PRESTATION</strong><br>
                        #{{ $serviceSchedule->id }}<br>
                        {{ now()->format('d/m/Y H:i') }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- Client --}}
        <div class="client-block">
            <span class="client-name">{{ $serviceSchedule->client_name ?? 'Non renseigné' }}</span>
            @if($serviceSchedule->contact_number)
                &nbsp;·&nbsp;<span class="client-phone">{{ $serviceSchedule->contact_number }}</span>
            @endif
        </div>

        <div class="info">
            <table>
                <tr>
                    <td>Fiche prestation</td>
                    <td class="right">
                        {{ $statusLabels[$serviceSchedule->status] ?? ucfirst($serviceSchedule->status) }}
                    </td>
                </tr>
            </table>
        </div>

        <table class="details-table">
            <tr>
                <td>Service</td>
                <td>{{ $serviceSchedule->service->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Adresse</td>
                <td>{{ $serviceSchedule->address ?? '-' }}</td>
            </tr>
            <tr>
                <td>Date prévue</td>
                <td>{{ \Carbon\Carbon::parse($serviceSchedule->scheduled_date)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Heure prévue</td>
                <td>{{ $serviceSchedule->scheduled_time ?? '--:--' }}</td>
            </tr>
            <tr>
                <td>Statut</td>
                <td>
                    <span class="status {{ $serviceSchedule->status }}">
                        {{ $statusLabels[$serviceSchedule->status] ?? ucfirst($serviceSchedule->status) }}
                    </span>
                </td>
            </tr>
        </table>

        @if($serviceSchedule->notes)
        <div class="notes-block">
            <div class="notes-label">Notes</div>
            {{ $serviceSchedule->notes }}
        </div>
        @endif

        <div class="footer">
            Document généré automatiquement
        </div>

    </div>

</div>

<script>
    window.onload = function () {
        window.print();
    }
</script>

</body>
</html>