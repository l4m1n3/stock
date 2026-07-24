<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail prestation</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            color:#111;
            padding:30px;
        }

        h1{
            margin-bottom:10px;
            color:#7C3AED;
        }

        .subtitle{
            color:#666;
            margin-bottom:25px;
        }

        table{
            width:100%;
            border-collapse: collapse;
        }

        td{
            border:1px solid #ddd;
            padding:12px;
            vertical-align: top;
        }

        .label{
            width:220px;
            background:#f8f8f8;
            font-weight:bold;
        }

        .status{
            display:inline-block;
            padding:6px 12px;
            border-radius:6px;
            font-size:13px;
            font-weight:bold;
        }

        .pending{
            background:#FEF3C7;
        }

        .confirmed{
            background:#DBEAFE;
        }

        .completed{
            background:#D1FAE5;
        }

        .cancelled{
            background:#FEE2E2;
        }

        .footer{
            margin-top:40px;
            text-align:center;
            color:#888;
            font-size:12px;
        }

        @media print {
            .no-print{
                display:none;
            }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom:20px;">
    <button onclick="window.print()">
        Imprimer
    </button>
</div>

<h1>Détail de la prestation</h1>

<div class="subtitle">
    Généré le {{ now()->format('d/m/Y H:i') }}
</div>

<table>
    <tr>
        <td class="label">Service</td>
        <td>{{ $serviceSchedule->service->name ?? '-' }}</td>
    </tr>

    <tr>
        <td class="label">Client</td>
        <td>{{ $serviceSchedule->client_name ?? 'Non renseigné' }}</td>
    </tr>

    <tr>
        <td class="label">Contact</td>
        <td>{{ $serviceSchedule->contact_number }}</td>
    </tr>

    <tr>
        <td class="label">Adresse</td>
        <td>{{ $serviceSchedule->address }}</td>
    </tr>

    <tr>
        <td class="label">Date prévue</td>
        <td>
            {{ \Carbon\Carbon::parse($serviceSchedule->scheduled_date)->translatedFormat('d F Y') }}
        </td>
    </tr>

    <tr>
        <td class="label">Heure prévue</td>
        <td>{{ $serviceSchedule->scheduled_time ?? '--:--' }}</td>
    </tr>

    <tr>
        <td class="label">Statut</td>
        <td>
            <span class="status {{ $serviceSchedule->status }}">
                {{ ucfirst($serviceSchedule->status) }}
            </span>
        </td>
    </tr>

    @if($serviceSchedule->notes)
    <tr>
        <td class="label">Notes</td>
        <td>{{ $serviceSchedule->notes }}</td>
    </tr>
    @endif
</table>

<div class="footer">
    Document généré automatiquement
</div>

<script>
    window.onload = function () {
        window.print();
    }
</script>

</body>
</html>