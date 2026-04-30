@extends('layouts.apps')

@section('title', 'Admin · Facturations')
@section('page-title', 'Administration · Facturations')

@push('styles')
<style>
.kpi-card { background:white; border-radius:16px; padding:22px 20px; box-shadow:0 4px 20px rgba(107,70,193,0.08); }
.kpi-card h6 { font-size:12px; font-weight:700; color:#9e8fc0; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px; }
.kpi-card h3 { font-size:22px; font-weight:800; color:#1a0a3e; margin:0; }
</style>
@endpush

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card">
            <h6>Total Facturé</h6>
            <h3>{{ number_format($totalAmount, 0, ',', ' ') }} <small style="font-size:13px;">FCFA</small></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <h6>Nombre de factures</h6>
            <h3>{{ $totalInvoices }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <h6>Ce mois</h6>
            <h3>{{ number_format($monthAmount, 0, ',', ' ') }} <small style="font-size:13px;">FCFA</small></h3>
        </div>
    </div>
</div>

<form method="GET" style="background:white;border-radius:14px;padding:16px 20px;margin-bottom:20px;box-shadow:0 2px 12px rgba(107,70,193,0.06);">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control form-control-sm" placeholder="N° facture…" style="border-radius:8px;">
        </div>
        <div class="col-md-2">
            <input type="date" name="from" value="{{ request('from') }}"
                   class="form-control form-control-sm" style="border-radius:8px;">
        </div>
        <div class="col-md-2">
            <input type="date" name="to" value="{{ request('to') }}"
                   class="form-control form-control-sm" style="border-radius:8px;">
        </div>
        <div class="col-md-3">
            <select name="branch_id" class="form-select form-select-sm" style="border-radius:8px;">
                <option value="">Toutes les branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn-violet flex-fill justify-content-center" style="height:32px;padding:0 10px;font-size:12px;">
                <i class="fas fa-search"></i> Filtrer
            </button>
            <a href="{{ route('admin.invoices') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">✕</a>
        </div>
    </div>
</form>

<div style="background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(107,70,193,0.08);">
    <div class="table-responsive">
        <table class="table align-middle mb-0" style="font-size:13px;">
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Date</th>
                    <th>Vendeur</th>
                    <th>Branche</th>
                    <th class="text-end">Total</th>
                    <th class="text-center" width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td><strong style="color:#6B46C1;">{{ $invoice->invoice_number }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($invoice->issued_at)->format('d/m/Y') }}</td>
                    <td>{{ $invoice->sale->user->name ?? '—' }}</td>
                    <td>
                        <span style="font-size:11px;background:#f0ebff;color:#6B46C1;padding:2px 8px;border-radius:99px;font-weight:700;">
                            {{ $invoice->branch->name ?? '—' }}
                        </span>
                    </td>
                    <td class="text-end fw-700">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">

                            {{-- ✅ FIX : route avec paramètre --}}
                            <a href=""
                               class="btn btn-sm btn-outline-primary" style="border-radius:7px;" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>

                            {{-- ✅ FIX : route avec paramètre --}}
                            <form method="POST" action=""
                                  onsubmit="return confirm('Supprimer la facture {{ $invoice->invoice_number }} ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" style="border-radius:7px;" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-file-invoice fa-2x mb-2 d-block" style="color:#d8d0f5;"></i>
                        Aucune facture trouvée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px 20px;">
        {{ $invoices->links() }}
    </div>
</div>

@endsection