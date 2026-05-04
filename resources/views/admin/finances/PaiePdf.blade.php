<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>État de paiement global</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
        }

        .header h3 {
            margin: 5px 0;
            font-weight: normal;
        }

        .info {
            margin-bottom: 15px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
            font-size: 9px;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            background: #f8f9fa;
        }

        .signature {
            height: 40px;
        }

        .footer {
            margin-top: 15px;
            font-size: 9px;
            text-align: center;
            color: #666;
        }
    </style>
</head>

<body>

    {{-- ================= HEADER ================= --}}
    <div class="header">
        <h2>ÉTAT GLOBAL DE PAIEMENT</h2>
        <h3>
            Période du {{ $periode_start->format('d/m/Y') }}
            au {{ $periode_end->format('d/m/Y') }}
        </h3>
    </div>

    {{-- ================= ENTREPRISE ================= --}}
    <div class="info">
        <strong>{{ $entreprise['nom'] }}</strong><br>
        {{ $entreprise['adresse'] }}<br>
        Contact: {{ $entreprise['contact'] }}
    </div>

    {{-- ================= TABLE ================= --}}
    <table>
        <thead>
            <tr>
                <th>Employé</th>
                {{-- <th>Période</th> --}}
                <th>Salaire brut</th>
                <th>Primes</th>
                <th>Retenues</th>
                <th>Net à payer</th>
                {{-- <th>Statut</th> --}}
                <th>Signature</th>
            </tr>
        </thead>

        <tbody>
            @foreach($paies as $paie)
                <tr>
                    <td>
                        {{ $paie->user->name ?? '—' }}
                    </td>

                    {{-- <td>
                        {{ $paie->periode_start->format('d/m/Y') }}
                        <br>
                        {{ $paie->periode_end->format('d/m/Y') }}
                    </td> --}}

                    <td class="text-right">
                        {{ number_format($paie->salaire_brut, 0, ',', ' ') }}
                    </td>

                    <td class="text-right" style="color:green;">
                        + {{ number_format($paie->total_primes, 0, ',', ' ') }}
                    </td>

                    <td class="text-right" style="color:red;">
                        - {{ number_format($paie->total_retenues, 0, ',', ' ') }}
                    </td>

                    <td class="text-right" style="font-weight:bold;">
                        {{ number_format($paie->salaire_net, 0, ',', ' ') }}
                    </td>

                    {{-- <td>
                        {{ ucfirst($paie->statut ?? 'brouillon') }}
                    </td> --}}

                    <td class="signature"></td>
                </tr>
            @endforeach

            {{-- ================= TOTAL ================= --}}
            <tr class="total">
                <td colspan="2">TOTAL GÉNÉRAL</td>

                <td class="text-right">
                    {{ number_format($paies->sum('salaire_brut'), 0, ',', ' ') }}
                </td>

                <td class="text-right">
                    {{ number_format($paies->sum('total_primes'), 0, ',', ' ') }}
                </td>

                <td class="text-right">
                    {{ number_format($paies->sum('total_retenues'), 0, ',', ' ') }}
                </td>

                <td class="text-right">
                    {{ number_format($total_net, 0, ',', ' ') }}
                </td>

                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        Document généré le {{ $date_emission }} — Valable pour archivage comptable
    </div>

</body>
</html>