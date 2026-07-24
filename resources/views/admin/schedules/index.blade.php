@extends('layouts.app')

@section('title', 'Admin · Planification')
@section('page-title', 'Administration · Planification')

@section('content')

@php
$statusConfig = [
    'pending'   => ['label' => 'En attente',  'bg' => '#FEF3C7', 'color' => '#D97706', 'dot' => '#F59E0B'],
    'confirmed' => ['label' => 'Confirmé',    'bg' => '#EDE9FE', 'color' => '#7C3AED', 'dot' => '#7C3AED'],
    'completed' => ['label' => 'Terminé',     'bg' => '#D1FAE5', 'color' => '#059669', 'dot' => '#10B981'],
    'cancelled' => ['label' => 'Annulé',      'bg' => '#FEE2E2', 'color' => '#DC2626', 'dot' => '#EF4444'],
];
@endphp

<style>
:root {
    --p6: #7C3AED; --p7: #6D28D9; --p1: #EDE9FE; --p05: #F5F3FF;
    --dark: #1E1B4B; --g1: #F3F4F6; --g2: #E5E7EB; --g5: #6B7280; --g7: #374151;
}
.page-header {
    background: linear-gradient(135deg,#6D28D9,#7C3AED);
    border-radius:16px; padding:24px 28px; color:#fff;
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:12px; margin-bottom:24px;
}
.page-header h1 { font-size:1.5rem; font-weight:700; margin:0; }
.page-header p  { margin:4px 0 0; opacity:.8; font-size:.875rem; }
.btn-white {
    background:#fff; color:var(--p7); border:none;
    padding:10px 20px; border-radius:10px; font-weight:600;
    font-size:.875rem; text-decoration:none;
    display:inline-flex; align-items:center; gap:6px; transition:opacity .15s;
}
.btn-white:hover { opacity:.9; color:var(--p7); }

/* Stats */
.stat-card {
    background:#fff; border-radius:14px; padding:18px 20px;
    border:1px solid var(--g2); box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.stat-card .s-label {
    font-size:.72rem; text-transform:uppercase; letter-spacing:.06em;
    color:var(--g5); font-weight:700; margin-bottom:4px;
}
.stat-card .s-val {
    font-size:1.9rem; font-weight:800; color:var(--dark); line-height:1.1;
}

/* Filtres */
.filter-bar {
    background:#fff; border-radius:14px; padding:16px 20px;
    border:1px solid var(--g2); margin-bottom:20px;
    display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;
}
.filter-bar input, .filter-bar select {
    border:1.5px solid var(--g2); border-radius:10px;
    padding:9px 13px; font-size:.875rem; color:var(--g7);
    background:var(--g1); outline:none; transition:border-color .15s;
}
.filter-bar input:focus, .filter-bar select:focus {
    border-color:var(--p6); background:#fff;
}
.btn-filter {
    background:var(--p7); color:#fff; border:none;
    padding:10px 20px; border-radius:10px; font-weight:600;
    font-size:.875rem; cursor:pointer;
    display:inline-flex; align-items:center; gap:6px;
}
.btn-reset {
    background:var(--g1); color:var(--g7); border:1.5px solid var(--g2);
    padding:10px 16px; border-radius:10px; font-size:.875rem;
    font-weight:500; cursor:pointer; text-decoration:none;
}

/* Table */
.table-card {
    background:#fff; border-radius:14px; border:1px solid var(--g2);
    overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.table-card table { width:100%; border-collapse:collapse; }
.table-card thead th {
    background:var(--p05); color:var(--p7); font-size:.72rem;
    font-weight:700; text-transform:uppercase; letter-spacing:.06em;
    padding:13px 16px; text-align:left;
    border-bottom:2px solid var(--p1);
}
.table-card tbody tr { border-bottom:1px solid var(--g1); transition:background .1s; }
.table-card tbody tr:last-child { border-bottom:none; }
.table-card tbody tr:hover { background:var(--p05); }
.table-card tbody td { padding:13px 16px; font-size:.875rem; color:var(--g7); vertical-align:middle; }

.status-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 11px; border-radius:999px; font-size:.73rem; font-weight:700;
}
.dot { width:7px; height:7px; border-radius:50%; display:inline-block; }
.name-bold { font-weight:600; color:var(--dark); }
.sub-info   { font-size:.78rem; color:var(--g5); margin-top:2px; }

.action-btn {
    display:inline-flex; align-items:center; justify-content:center;
    width:31px; height:31px; border-radius:8px;
    border:1.5px solid var(--g2); background:#fff; color:var(--g5);
    text-decoration:none; transition:all .15s; cursor:pointer;
}
.action-btn:hover { border-color:var(--p6); color:var(--p6); background:var(--p05); }
.action-btn.danger:hover { border-color:#DC2626; color:#DC2626; background:#FEF2F2; }

.alert-success {
    background:#D1FAE5; border:1px solid #6EE7B7; color:#065F46;
    border-radius:10px; padding:12px 18px; margin-bottom:20px;
    font-size:.875rem; font-weight:500; display:flex; align-items:center; gap:8px;
}
.empty-state { text-align:center; padding:60px 20px; color:var(--g5); }
.empty-state p { font-size:1rem; font-weight:500; margin-top:14px; }
</style>

<div class="container-fluid px-4 py-4">

    @if(session('success'))
        <div class="alert-success">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1>📅 Planification des prestations</h1>
            <p>Vue globale de toutes les prestations planifiées</p>
        </div>
        <a href="{{ route('admin.schedules.create') }}" class="btn-white">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
            Nouvelle prestation
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card">
                <div class="s-label">Total</div>
                <div class="s-val">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card">
                <div class="s-label">En attente</div>
                <div class="s-val" style="color:#D97706">{{ $stats['pending'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card">
                <div class="s-label">Confirmées</div>
                <div class="s-val" style="color:var(--p6)">{{ $stats['confirmed'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card">
                <div class="s-label">Terminées</div>
                <div class="s-val" style="color:#059669">{{ $stats['completed'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card">
                <div class="s-label">Annulées</div>
                <div class="s-val" style="color:#DC2626">{{ $stats['cancelled'] }}</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card">
                <div class="s-label">Aujourd'hui</div>
                <div class="s-val" style="color:var(--p7)">{{ $stats['today'] }}</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('admin.schedules') }}">
        <div class="filter-bar">
            <input type="text" name="search" placeholder="Rechercher client, adresse..."
                value="{{ request('search') }}" style="min-width:220px">

            <select name="service_id">
                <option value="">Tous les services</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" @selected(request('service_id') == $service->id)>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>

            <select name="status">
                <option value="">Tous les statuts</option>
                @foreach(['pending'=>'En attente','confirmed'=>'Confirmé','completed'=>'Terminé','cancelled'=>'Annulé'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                @endforeach
            </select>

            <input type="date" name="date" value="{{ request('date') }}">

            <button type="submit" class="btn-filter">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                Filtrer
            </button>
            <a href="{{ route('admin.schedules') }}" class="btn-reset">Réinitialiser</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="table-card">
        @if($schedules->isEmpty())
            <div class="empty-state">
                <svg width="56" height="56" fill="none" stroke="#7C3AED" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                <p>Aucune prestation trouvée</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Service</th>
                        <th>Client</th>
                        <th>Date & Heure</th>
                        <th>Adresse</th>
                        <th>Statut</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                    @php $cfg = $statusConfig[$schedule->status]; @endphp
                    <tr>
                        <td style="color:var(--g5);font-size:.8rem;font-weight:600">
                            #{{ $schedule->id }}
                        </td>

                        <td>
                            <div class="name-bold">{{ $schedule->service->name ?? '—' }}</div>
                            @if($schedule->sale)
                                <div class="sub-info">Vente #{{ $schedule->sale->id }}</div>
                            @endif
                        </td>

                        <td>
                            <div style="font-weight:500;color:var(--dark)">
                                {{ $schedule->client_name ?? 'Anonyme' }}
                            </div>
                            <div class="sub-info">📞 {{ $schedule->contact_number }}</div>
                        </td>

                        <td>
                            <div style="font-weight:600;color:var(--dark)">
                                {{ \Carbon\Carbon::parse($schedule->scheduled_date)->translatedFormat('d M Y') }}
                            </div>
                            @if($schedule->scheduled_time)
                                <div class="sub-info">🕐 {{ substr($schedule->scheduled_time, 0, 5) }}</div>
                            @endif
                        </td>

                        <td style="max-width:170px">
                            <span style="display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:170px"
                                  title="{{ $schedule->address }}">
                                📍 {{ $schedule->address }}
                            </span>
                        </td>

                        <td>
                            <span class="status-badge"
                                style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }}">
                                <span class="dot" style="background:{{ $cfg['dot'] }}"></span>
                                {{ $cfg['label'] }}
                            </span>
                        </td>

                        <td>
                            <div style="display:flex;gap:5px;justify-content:center">
                                {{-- Voir --}}
                                <a href="{{ route('service-schedules.print', request()->query()) }}"
                                    target="_blank"
                                    class="btn btn-dark">
                                        <i class="fas fa-print"></i> Imprimer
                                    </a>
                                <a href="{{ route('admin.schedules.show', $schedule) }}"
                                   class="action-btn" title="Voir">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                        <path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                {{-- Modifier --}}
                                <a href="{{ route('admin.schedules.edit', $schedule) }}"
                                   class="action-btn" title="Modifier">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                {{-- Supprimer --}}
                                <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}"
                                    onsubmit="return confirm('Supprimer cette prestation ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn danger" title="Supprimer">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($schedules->hasPages())
                <div style="padding:14px 20px;border-top:1px solid var(--g1)">
                    {{ $schedules->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection