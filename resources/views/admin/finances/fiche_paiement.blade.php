<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>Fiche de paiement - {{ $employee->name ?? '' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
        }

        .employee-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .employee-details h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }

        .periode-info {
            text-align: right;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .amount {
            text-align: right;
            font-weight: bold;
        }

        .total-row {
            background-color: #e8f4fd !important;
            font-size: 12px;
        }

        .total-final {
            background-color: #27ae60 !important;
            color: white;
            font-size: 14px;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: stretch; /* même hauteur pour les deux boîtes */
            width: 80%;
            gap: 20px;
        }

        .signature-box {
            width: 48%;
            text-align: center;
            border: 2px solid #333;
            padding: 15px;
            min-height: 140px; /* même hauteur */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }

        .signature-box.gauche {
            border-right: 3px double #333;
        }

        .signature-box.droite {
            border-left: 3px double #333;
        }

        .signature-line {
            border-top: 2px solid #333;
            width: 160px;
            margin: 20px auto 8px auto;
        }


        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }

        .prime-retenue-list {
            margin: 5px 0;
        }

        .prime-item, .retenue-item {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .montant-positive { color: #27ae60; }
        .montant-negative { color: #e74c3c; }
    </style>
</head>

<body>
    <!-- EN-TÊTE -->
   <div class="header">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 20%; text-align: left; border: none;">
                    <img src="data:image/jpeg;base64,{{ $logo }}" style="height:60px;">
                </td>

                <td style="width: 80%; text-align: center; border: none;">
                    <h1 style="margin:0;">FICHE DE PAIE</h1>
                    <h2 style="margin:0;">Ilyken Services</h2>
                    <p style="margin:0;">
                        {{ $entreprise['adresse'] }} - Tél: {{ $entreprise['contact'] }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- INFOS EMPLOYE -->
    <div class="employee-info">
        <div class="employee-details">
            <h3>{{ $employee->name ?? 'EMPLOYE' }}</h3>
            {{-- <p><strong>Fonction:</strong> {{ $employee->fonction ?? '' }}</p>
            <p><strong>Matricule:</strong> {{ $employee->badge_id ?? '' }}</p> --}}
        </div>
        <div class="periode-info">
            <strong>Période:</strong><br>
            {{ $paies->first()->periode_start->format('d/m/Y') }}<br>
            au<br>
            {{ $paies->first()->periode_end->format('d/m/Y') }}
        </div>
    </div>

    @foreach($paies as $index => $paie)
        @if($index > 0)
            <div style="page-break-before: always;"></div>
        @endif

        <!-- SALAIRE DE BASE -->
        <table>
            <tr>
                <th colspan="4">DÉTAIL DE PAIEMENT - {{ $paie->periode_start->format('M Y') }}</th>
            </tr>
            <tr> 
                <td><strong>Salaire de base</strong></td>
                <td colspan="3" class="amount">{{ number_format($paie->salaire_brut, 2, ',', ' ') }}</td>
            </tr>
        </table>

        <!-- PRIMES -->
        @if($paie->total_primes > 0)
            <div class="section-title">PRIMES ET INDEMNITÉS</div>
            <table>
                @foreach($paie->primes as $prime)
                    <tr>
                        <td width="60%">{{ $prime->libelle }}</td>
                        <td width="20%" class="amount montant-positive">{{ number_format($prime->montant, 2, ',', ' ') }}</td>
                        <td width="20%"></td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td><strong>TOTAL PRIMES</strong></td>
                    <td class="amount">{{ number_format($paie->total_primes, 2, ',', ' ') }}</td>
                    <td></td>
                </tr>
            </table>
        @endif

        <!-- RETENUES -->
        @if($paie->total_retenues > 0)
            <div class="section-title">RETRAITES ET RETENUES</div>
            <table>
                @foreach($paie->retenues as $retenue)
                    <tr>
                        <td width="60%">{{ $retenue->libelle }}</td>
                        <td width="20%" class="amount montant-negative">({{ number_format($retenue->montant, 2, ',', ' ') }})</td>
                        <td width="20%"></td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td><strong>TOTAL RETENUES</strong></td>
                    <td class="amount montant-negative">({{ number_format($paie->total_retenues, 2, ',', ' ') }})</td>
                    <td></td>
                </tr>
            </table>
        @endif
 
        <!-- RÉCAPITULATIF FINAL -->
        <table style="margin-top: 20px;">
            <tr>
                <td width="50%"><strong>SALAIRE BRUT</strong></td>
                <td width="50%" class="amount">{{ number_format($paie->salaire_brut + $paie->total_primes, 2, ',', ' ') }}</td>
            </tr>
            <tr>
                <td><strong>Retenues (-)</strong></td>
                <td class="amount montant-negative">({{ number_format($paie->total_retenues, 2, ',', ' ') }})</td>
            </tr>
            <tr class="total-final">
                <td><strong>NET À PAYER</strong></td>
                <td class="amount">{{ number_format($paie->salaire_net, 2, ',', ' ') }}</td>
            </tr>
        </table>
        <table>
            <tr>
                <td style="width: 50%; text-align: center;">
                    <strong>Signature de l'employé</strong><br><br><br>
                    ___________________________<br>
                    <strong>Date:</strong> __________
                </td>
                <td style="width: 50%; text-align: center;">
                    <strong>Signature du Responsable RH</strong><br><br><br>
                    ___________________________<br>
                    <strong>Date:</strong> {{ $date_emission }}
                </td>
            </tr>
        </table>
    @endforeach

    <!-- PIED DE PAGE -->
    <div class="footer">
        <p>Document généré le {{ $date_emission }} | Ilyken Services - Tout paiement doit être validé par signature</p>
    </div>
</body>
</html>