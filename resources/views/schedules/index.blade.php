@extends('layouts.app')

@section('title', 'Prestations planifiées')

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
        --purple-600: #7C3AED;
        --purple-700: #6D28D9;
        --purple-100: #EDE9FE;
        --purple-50:  #F5F3FF;
        --dark:       #1E1B4B;
        --gray-100:   #F3F4F6;
        --gray-200:   #E5E7EB;
        --gray-500:   #6B7280;
        --gray-700:   #374151;
    }
    .page-header {
        background: linear-gradient(135deg, #6D28D9 0%, #7C3AED 100%);
        border-radius: 16px;
        padding: 24px 28px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    .page-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
    .page-header p  { margin: 4px 0 0; opacity: .8; font-size: .875rem; }
    .btn-primary {
        background: #fff;
        color: var(--purple-700);
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .875rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: opacity .15s;
    }
    .btn-primary:hover { opacity: .9; color: var(--purple-700); }
    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px 22px;
        border: 1px solid var(--gray-200);
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
    }
    .stat-card .label { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: var(--gray-500); font-weight: 600; }
    .stat-card .value { font-size: 2rem; font-weight: 800; color: var(--dark); line-height: 1.1; margin-top: 4px; }
    .stat-card .value.purple { color: var(--purple-600); }
    .filter-bar {
        background: #fff;
        border-radius: 14px;
        padding: 16px 20px;
        border: 1px solid var(--gray-200);
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .filter-bar input,
    .filter-bar select {
        border: 1.5px solid var(--gray-200);
        border-radius: 10px;
        padding: 9px 14px;
        font-size: .875rem;
        color: var(--gray-700);
        background: var(--gray-100);
        outline: none;
        transition: border-color .15s;
    }
    .filter-bar input:focus,
    .filter-bar select:focus { border-color: var(--purple-600); background: #fff; }
    .filter-bar .btn-filter {
        background: var(--purple-600);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .875rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .filter-bar .btn-reset {
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1.5px solid var(--gray-200);
        padding: 10px 16px;
        border-radius: 10px;
        font-size: .875rem;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
    }
    .table-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
    }
    .table-card table { width: 100%; border-collapse: collapse; }
    .table-card thead th {
        background: var(--purple-50);
        color: var(--purple-700);
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 14px 16px;
        text-align: left;
        border-bottom: 2px solid var(--purple-100);
    }
    .table-card tbody tr { border-bottom: 1px solid var(--gray-100); transition: background .1s; }
    .table-card tbody tr:last-child { border-bottom: none; }
    .table-card tbody tr:hover { background: var(--purple-50); }
    .table-card tbody td { padding: 14px 16px; font-size: .875rem; color: var(--gray-700); vertical-align: middle; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 600;
    }
    .status-badge .dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
    .service-name { font-weight: 600; color: var(--dark); }
    .client-info  { font-size: .8rem; color: var(--gray-500); margin-top: 2px; }
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px; height: 32px;
        border-radius: 8px;
        border: 1.5px solid var(--gray-200);
        background: #fff;
        color: var(--gray-500);
        text-decoration: none;
        transition: all .15s;
        cursor: pointer;
    }
    .action-btn:hover { border-color: var(--purple-600); color: var(--purple-600); background: var(--purple-50); }
    .action-btn.danger:hover { border-color: #DC2626; color: #DC2626; background: #FEF2F2; }
    .alert-success {
        background: #D1FAE5;
        border: 1px solid #6EE7B7;
        color: #065F46;
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 20px;
        font-size: .875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--gray-500);
    }
    .empty-state svg { opacity: .3; margin-bottom: 16px; }
    .empty-state p { font-size: 1rem; font-weight: 500; }
</style>

<div class="container-fluid px-4 py-4">

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert-success">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1>📅 Prestations planifiées</h1>
            <p>Gérez les horaires de vos prestations de service</p>
        </div>
        <a href="{{ route('service-schedules.create') }}" class="btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nouvelle prestation
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Total</div>
                <div class="value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">En attente</div>
                <div class="value" style="color:#D97706">{{ $stats['pending'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Confirmées</div>
                <div class="value purple">{{ $stats['confirmed'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Aujourd'hui</div>
                <div class="value" style="color:#059669">{{ $stats['today'] }}</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('service-schedules.index') }}">
        <div class="filter-bar">
            <div>
                <input type="text" name="search" placeholder="Rechercher client, service..."
                    value="{{ request('search') }}" style="min-width:240px">
            </div>
            <div>
                <select name="status">
                    <option value="">Tous les statuts</option>
                    @foreach(['pending'=>'En attente','confirmed'=>'Confirmé','completed'=>'Terminé','cancelled'=>'Annulé'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="date" name="date" value="{{ request('date') }}">
            </div>
            <button type="submit" class="btn-filter">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                Filtrer
            </button>
            <a href="{{ route('service-schedules.index') }}" class="btn-reset">Réinitialiser</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="table-card">
        @if($schedules->isEmpty())
            <div class="empty-state">
                <svg width="64" height="64" fill="none" stroke="#7C3AED" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                <p>Aucune prestation planifiée</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
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
                        {{-- Service --}}
                        <td>
                            <div class="service-name">{{ $schedule->service->name ?? '—' }}</div>
                            @if($schedule->sale)
                                <div class="client-info">Vente #{{ $schedule->sale->id }}</div>
                            @endif
                        </td>

                        {{-- Client --}}
                        <td>
                            <div style="font-weight:500;color:var(--dark)">
                                {{ $schedule->client_name ?? 'Anonyme' }}
                            </div>
                            <div class="client-info">📞 {{ $schedule->contact_number }}</div>
                        </td>

                        {{-- Date --}}
                        <td>
                            <div style="font-weight:600;color:var(--dark)">
                                {{ \Carbon\Carbon::parse($schedule->scheduled_date)->translatedFormat('d M Y') }}
                            </div>
                            @if($schedule->scheduled_time)
                                <div class="client-info">🕐 {{ substr($schedule->scheduled_time, 0, 5) }}</div>
                            @endif
                        </td>

                        {{-- Adresse --}}
                        <td style="max-width:180px">
                            <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px"
                                 title="{{ $schedule->address }}">
                                📍 {{ $schedule->address }}
                            </div>
                        </td>

                        {{-- Statut --}}
                        <td>
                            <span class="status-badge"
                                style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }}">
                                <span class="dot" style="background:{{ $cfg['dot'] }}"></span>
                                {{ $cfg['label'] }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td style="text-align:center">
                            <div style="display:flex;gap:6px;justify-content:center">
                                {{-- Voir --}}
                                <a href="{{ route('service-schedules.show', $schedule) }}"
                                   class="action-btn" title="Voir">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                        <path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                {{-- Modifier --}}
                                <a href="{{ route('service-schedules.edit', $schedule) }}"
                                   class="action-btn" title="Modifier">
                                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                {{-- Supprimer --}}
                                <form method="POST" action="{{ route('service-schedules.destroy', $schedule) }}"
                                    onsubmit="return confirm('Supprimer cette prestation ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn danger" title="Supprimer">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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

            {{-- Pagination --}}
            @if($schedules->hasPages())
                <div style="padding:16px 20px;border-top:1px solid var(--gray-100)">
                    {{ $schedules->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection