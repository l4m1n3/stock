
@extends('layouts.app')

@section('title', 'Détail du bon de commande')

@section('content')

@php
    $statusLabels = [
        'pending' => 'En attente',
        'received' => 'Réceptionnée',
        'cancelled' => 'Annulée',
    ];

    $statusClasses = [
        'pending' => 'bg-warning text-dark',
        'received' => 'bg-success',
        'cancelled' => 'bg-danger',
    ];

    $totalOrdered = $order->items->sum('quantity_ordered');
    $totalReceived = $order->items->sum('quantity_received');
    $totalRemaining = max(0, $totalOrdered - $totalReceived);

    $itemsTotal = $order->items->sum(function ($item) {
        return $item->quantity_ordered * $item->purchase_price;
    });
@endphp

<style>
    .purchase-page {
        --primary: #6B46C1;
        --light: #F5F1FC;
        --border: #EAE5F4;
    }

    .purchase-page .card {
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 3px 14px rgba(50,30,90,.04);
        margin-bottom: 20px;
    }

    .purchase-page .card-header {
        background: #FAF8FE;
        border-bottom: 1px solid var(--border);
        padding: 16px 20px;
        font-weight: 700;
    }

    .purchase-page .card-body {
        padding: 20px;
    }

    .purchase-page .kpi {
        font-size: 23px;
        font-weight: 750;
        color: var(--primary);
    }

    .purchase-page .kpi-label {
        color: #89819A;
        font-size: 12px;
        margin-bottom: 8px;
    }

    .purchase-page .info-line {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        padding: 11px 0;
        border-bottom: 1px solid #F2EFF7;
        font-size: 13px;
    }

    .purchase-page .info-line:last-child {
        border-bottom: 0;
    }

    .purchase-page .info-line span:first-child {
        color: #89819A;
    }

    .purchase-page .info-line strong {
        text-align: right;
    }

    .purchase-page .table th {
        background: var(--light);
        color: var(--primary);
        font-size: 11px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .purchase-page .table td {
        vertical-align: middle;
        font-size: 13px;
        padding: 14px;
    }

    .purchase-page .btn-primary-custom {
        background: var(--primary);
        color: white;
        border: 0;
        border-radius: 9px;
        padding: 9px 16px;
        text-decoration: none;
        font-size: 13px;
    }

    .purchase-page .btn-outline-custom {
        border: 1px solid var(--primary);
        color: var(--primary);
        border-radius: 9px;
        padding: 9px 16px;
        text-decoration: none;
        background: white;
        font-size: 13px;
    }

    .purchase-page .progress {
        height: 9px;
        border-radius: 20px;
        background: #EEEAF5;
    }

    .purchase-page .progress-bar {
        background: var(--primary);
    }

    .purchase-page .total-card {
        background: var(--light);
        border-radius: 12px;
        padding: 20px;
        max-width: 420px;
        margin-left: auto;
    }

    .purchase-page .total-final {
        border-top: 1px solid #DDD4EE;
        margin-top: 12px;
        padding-top: 15px;
        font-size: 19px;
        color: var(--primary);
        font-weight: 800;
    }

    @media print {
        .no-print,
        .sidebar,
        .navbar,
        nav {
            display: none !important;
        }

        .purchase-page .card {
            box-shadow: none;
            break-inside: avoid;
        }

        body {
            background: white !important;
        }
    }
</style>

<div class="purchase-page">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-file-invoice text-primary me-2"></i>
                Bon de commande #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
            </h4>

            <small class="text-muted">
                {{ $order->branch->name ?? 'Agence' }}
                · Commande créée le
                {{ $order->ordered_at?->format('d/m/Y H:i') ?? '—' }}
            </small>
        </div>

        <div class="d-flex gap-2 flex-wrap no-print">

            <a href="{{ route('purchases.index') }}"
               class="btn-outline-custom">
                <i class="fas fa-arrow-left me-1"></i>
                Retour
            </a>

            <button onclick="window.print()" class="btn-primary-custom">
                <i class="fas fa-print me-1"></i>
                Imprimer
            </button>

        </div>

    </div>


    {{-- KPI --}}
    <div class="row g-3 mb-3">

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="kpi-label">Montant total</div>
                    <div class="kpi">
                        {{ number_format($order->total_amount, 0, ',', ' ') }}
                    </div>
                    <small class="text-muted">FCFA</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="kpi-label">Articles commandés</div>
                    <div class="kpi">{{ $totalOrdered }}</div>
                    <small class="text-muted">Unités</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="kpi-label">Articles réceptionnés</div>
                    <div class="kpi text-success">{{ $totalReceived }}</div>
                    <small class="text-muted">Unités</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="kpi-label">Reste à recevoir</div>
                    <div class="kpi text-warning">{{ $totalRemaining }}</div>
                    <small class="text-muted">Unités</small>
                </div>
            </div>
        </div>

    </div>


    {{-- INFORMATIONS --}}
    <div class="row g-3">

        <div class="col-lg-6">

            <div class="card h-100">

                <div class="card-header">
                    <i class="fas fa-truck me-2 text-primary"></i>
                    Fournisseur
                </div>

                <div class="card-body">

                    <h5 class="fw-bold mb-3">
                        {{ $order->supplier->name ?? 'Fournisseur inconnu' }}
                    </h5>

                    <div class="info-line">
                        <span>Téléphone</span>
                        <strong>{{ $order->supplier->phone ?? '—' }}</strong>
                    </div>

                    <div class="info-line">
                        <span>Email</span>
                        <strong>{{ $order->supplier->email ?? '—' }}</strong>
                    </div>

                    <div class="info-line">
                        <span>Adresse</span>
                        <strong>{{ $order->supplier->address ?? '—' }}</strong>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="card h-100">

                <div class="card-header">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Informations de commande
                </div>

                <div class="card-body">

                    <div class="info-line">
                        <span>Statut</span>
                        <strong>
                            <span class="badge {{ $statusClasses[$order->status] ?? 'bg-secondary' }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </strong>
                    </div>

                    <div class="info-line">
                        <span>Agence</span>
                        <strong>{{ $order->branch->name ?? '—' }}</strong>
                    </div>

                    <div class="info-line">
                        <span>Créée par</span>
                        <strong>{{ $order->user->name ?? '—' }}</strong>
                    </div>

                    <div class="info-line">
                        <span>Date de commande</span>
                        <strong>
                            {{ $order->ordered_at?->format('d/m/Y H:i') ?? '—' }}
                        </strong>
                    </div>

                    <div class="info-line">
                        <span>Date de réception</span>
                        <strong>
                            {{ $order->received_at?->format('d/m/Y H:i') ?? 'En attente' }}
                        </strong>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- PROGRESSION --}}
    <div class="card">

        <div class="card-header">
            <i class="fas fa-chart-line me-2 text-primary"></i>
            Progression de la réception
        </div>

        <div class="card-body">

            @php
                $progress = $totalOrdered > 0
                    ? min(100, round(($totalReceived / $totalOrdered) * 100))
                    : 0;
            @endphp

            <div class="d-flex justify-content-between mb-2">
                <strong>{{ $progress }} %</strong>
                <small class="text-muted">
                    {{ $totalReceived }} / {{ $totalOrdered }} unités
                </small>
            </div>

            <div class="progress">
                <div class="progress-bar"
                     role="progressbar"
                     style="width: {{ $progress }}%">
                </div>
            </div>

        </div>

    </div>


    {{-- ARTICLES --}}
    <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-boxes me-2 text-primary"></i>
                Articles commandés
            </span>

            <span class="badge bg-light text-dark">
                {{ $order->items->count() }} ligne(s)
            </span>
        </div>

        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produit</th>
                        <th>Qté commandée</th>
                        <th>Qté reçue</th>
                        <th>Reste</th>
                        <th>Prix d'achat</th>
                        <th>Prix de vente</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($order->items as $index => $item)

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>
                                <strong>
                                    {{ $item->product->name ?? 'Produit supprimé' }}
                                </strong>
                            </td>

                            <td>{{ number_format($item->quantity_ordered) }}</td>

                            <td class="text-success fw-bold">
                                {{ number_format($item->quantity_received) }}
                            </td>

                            <td class="text-warning fw-bold">
                                {{ max(0, $item->quantity_ordered - $item->quantity_received) }}
                            </td>

                            <td>
                                {{ number_format($item->purchase_price, 0, ',', ' ') }}
                                FCFA
                            </td>

                            <td>
                                {{ $item->selling_price !== null
                                    ? number_format($item->selling_price, 0, ',', ' ') . ' FCFA'
                                    : '—' }}
                            </td>

                            <td class="fw-bold">
                                {{ number_format(
                                    $item->quantity_ordered * $item->purchase_price,
                                    0, ',', ' '
                                ) }}
                                FCFA
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Aucun article dans cette commande.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- TOTAL --}}
    <div class="total-card mb-4">

        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Sous-total</span>
            <strong>{{ number_format($itemsTotal, 0, ',', ' ') }} FCFA</strong>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Nombre de lignes</span>
            <strong>{{ $order->items->count() }}</strong>
        </div>

        <div class="d-flex justify-content-between total-final">
            <span>Total commande</span>
            <span>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</span>
        </div>

    </div>


    {{-- NOTES --}}
    @if($order->notes)

        <div class="card">

            <div class="card-header">
                <i class="fas fa-sticky-note me-2 text-primary"></i>
                Observations
            </div>

            <div class="card-body">
                {{ $order->notes }}
            </div>

        </div>

    @endif


    <div class="text-center text-muted small mb-4">
        Document généré le {{ now()->format('d/m/Y à H:i') }}
    </div>

</div>

@endsection
