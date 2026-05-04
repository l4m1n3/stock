@extends('layouts.apps')

@section('title', 'Admin · Paie')
@section('page-title', 'Administration · Gestion des salaires')

@push('styles')
<style>
:root {
    --violet: #6B46C1;
    --violet-light: rgba(107,70,193,0.08);
    --green: #177f43;
    --red: #a32d2d;
    --text-muted: #9e8fc0;
    --text-dark: #1a0a3e;
    --card-shadow: 0 4px 20px rgba(107,70,193,0.08);
    --card-border: 1px solid rgba(107,70,193,0.06);
}

/* Cards */
.section-card {
    background: white;
    border-radius: 16px;
    padding: 22px;
    box-shadow: var(--card-shadow);
    border: var(--card-border);
}

.section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 16px;
}

/* KPI */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

@media(max-width:768px){
    .kpi-grid { grid-template-columns: repeat(2,1fr); }
}

.kpi-card {
    background: white;
    border-radius: 16px;
    padding: 20px 16px;
    box-shadow: var(--card-shadow);
    border: var(--card-border);
    position: relative;
}

.kpi-card h6 {
    font-size: 11px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
}

.kpi-value {
    font-size: 22px;
    font-weight: 800;
    color: var(--text-dark);
}

/* Table */
.finance-table th {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
}
</style>
@endpush

@section('content')

{{-- ================= FILTRES ================= --}}
<div class="section-card mb-4">
    <form method="GET" class="row g-2 align-items-end">

    {{-- ✅ NOUVEAUX CHAMPS --}}
    <div class="col-md-3">
        <label class="form-label">Date début</label>
        <input type="date" name="periode_start" class="form-control"
               value="{{ request('periode_start') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Date fin</label>
        <input type="date" name="periode_end" class="form-control"
               value="{{ request('periode_end') }}">
    </div>

    <div class="col-md-2">
        <button class="btn-violet w-100">Filtrer</button>
    </div>

    <div class="col-md-2">
        <a href="{{ route('admin.paie.pdf', request()->all()) }}" class="btn-pdf w-100">
            Export PDF
        </a>
    </div>

</form>
    
</div>

{{-- ================= KPI ================= --}}
<div class="kpi-grid">

    <div class="kpi-card">
        <h6>Total salaires</h6>
        <div class="kpi-value">{{ number_format($totalSalaries,0,',',' ') }}</div>
    </div>

    <div class="kpi-card">
        <h6>Primes</h6>
        <div class="kpi-value" style="color:#177f43;">
            {{-- {{ number_format($totalPrimes,0,',',' ') }} --}}
        </div>
    </div>

    <div class="kpi-card">
        <h6>Retenues</h6>
        <div class="kpi-value" style="color:#a32d2d;">
            {{-- {{ number_format($totalRetenues,0,',',' ') }} --}}
        </div>
    </div>

    <div class="kpi-card">
        <h6>Net payé</h6>
        <div class="kpi-value" style="color:var(--violet);">
            {{-- {{ number_format($totalNet,0,',',' ') }} --}}
        </div>
    </div>

</div>
        <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Gestion des salaires</h4>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaie">
            + Nouvelle paie
        </button>
    </div>
{{-- ================= GRAPHIQUE ================= --}}
<div class="section-card mb-4">
    <div class="section-title">Évolution des salaires</div>
    <canvas id="paieChart" height="90"></canvas>
</div>

{{-- ================= TABLE PAIE ================= --}}
<div class="section-card">
    <div class="section-title">Historique des paies</div>

    <div class="table-responsive">
        <table class="table finance-table">
            <thead>
                <tr>
                    <th>Employé</th>
                    <th>Période</th>
                    <th>Brut</th>
                    <th>Primes</th>
                    <th>Retenues</th>
                    <th>Net</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($paies as $p)
                <tr>
                    <td>{{ $p->user->name }}</td>
                    <td>
                        {{ $p->periode_start->format('d/m') }} -
                        {{ $p->periode_end->format('d/m/Y') }}
                    </td>

                    <td>{{ number_format($p->salaire_brut,0,',',' ') }}</td>
                    <td style="color:#177f43;">
                        +{{ number_format($p->total_primes,0,',',' ') }}
                    </td>
                    <td style="color:#a32d2d;">
                        -{{ number_format($p->total_retenues,0,',',' ') }}
                    </td>
                    <td style="font-weight:800;color:var(--violet);">
                        {{ number_format($p->salaire_net,0,',',' ') }}
                    </td>

                    <td>
                        <span class="badge bg-{{ $p->statut == 'payé' ? 'success' : 'warning' }}">
                            {{ $p->statut ?? 'brouillon' }}
                        </span>
                    </td>
                    <td>
                       <a href="{{ route('admin.paies.fiche', $p->user->id) }}" class="btn btn-sm btn-outline-dark">
                                         <i class="bi bi-file-pdf me-1"></i>
                                    </a>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Aucune paie enregistrée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{-- ================= MODAL ADD ================= --}}
<div class="modal fade" id="addPaie">
    <div class="modal-dialog">
        <div class="modal-content p-3">

            <form method="POST" action="{{ route('admin.paie.store') }}">
                @csrf

                <div class="mb-2">
                    <label>Employé</label>
                    <select name="user_id" class="form-control">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <input name="periode_start" type="date" class="form-control mb-2">
                <input name="periode_end" type="date" class="form-control mb-2">

                <input name="salaire_brut" type="number" class="form-control mb-2" placeholder="Salaire brut">
                <input name="total_primes" type="number" class="form-control mb-2" placeholder="Primes">
                <input name="total_retenues" type="number" class="form-control mb-2" placeholder="Retenues">

                <button class="btn btn-success w-100">Enregistrer</button>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const months = @json($months);

new Chart(document.getElementById('paieChart'), {
    type: 'line',
    data: {
        labels: months.map(m => m.label),
        datasets: [
            {
                label: 'Salaires nets',
                data: months.map(m => m.net),
                borderColor: '#6B46C1',
                fill: true,
                backgroundColor: 'rgba(107,70,193,0.1)',
                tension: 0.4
            }
        ]
    }
});
</script>
@endpush