@extends('layouts.app')

@section('title', 'Détail de la prestation')

@section('content')

@php
$statusConfig = [
    'pending'   => ['label' => 'En attente',  'bg' => '#FEF3C7', 'color' => '#D97706', 'dot' => '#F59E0B'],
    'confirmed' => ['label' => 'Confirmé',    'bg' => '#EDE9FE', 'color' => '#7C3AED', 'dot' => '#7C3AED'],
    'completed' => ['label' => 'Terminé',     'bg' => '#D1FAE5', 'color' => '#059669', 'dot' => '#10B981'],
    'cancelled' => ['label' => 'Annulé',      'bg' => '#FEE2E2', 'color' => '#DC2626', 'dot' => '#EF4444'],
];
$cfg = $statusConfig[$serviceSchedule->status];
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
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
    .page-header p  { margin: 4px 0 0; opacity: .8; font-size: .875rem; }
    .detail-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        margin-bottom: 20px;
    }
    .card-section-header {
        background: var(--purple-50);
        border-bottom: 1px solid var(--purple-100);
        padding: 12px 22px;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--purple-700);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    .detail-item {
        padding: 18px 22px;
        border-bottom: 1px solid var(--gray-100);
        border-right: 1px solid var(--gray-100);
    }
    .detail-item:nth-child(even) { border-right: none; }
    .detail-item:last-child, .detail-item:nth-last-child(2):nth-child(odd) { border-bottom: none; }
    .detail-item .d-label {
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--gray-500);
        margin-bottom: 5px;
    }
    .detail-item .d-value {
        font-size: .95rem;
        font-weight: 600;
        color: var(--dark);
    }
    .detail-item .d-value.muted { color: var(--gray-500); font-weight: 400; }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 14px;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
    }
    .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .btn-edit {
        background: #fff;
        color: var(--purple-700);
        border: 2px solid rgba(255,255,255,.5);
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background .15s;
    }
    .btn-edit:hover { background: rgba(255,255,255,.15); color: #fff; }
    .btn-back {
        background: rgba(255,255,255,.15);
        color: #fff;
        border: 2px solid rgba(255,255,255,.3);
        padding: 10px 18px;
        border-radius: 10px;
        font-size: .875rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-back:hover { background: rgba(255,255,255,.25); color: #fff; }
    .status-form select {
        border: 1.5px solid var(--gray-200);
        border-radius: 8px;
        padding: 7px 12px;
        font-size: .85rem;
        color: var(--gray-700);
        background: var(--gray-100);
        outline: none;
        cursor: pointer;
    }
    .status-form button {
        background: var(--purple-700);
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
    }
    .notes-box {
        padding: 20px 22px;
        color: var(--gray-700);
        font-size: .9rem;
        line-height: 1.6;
        white-space: pre-wrap;
    }
    .full-item {
        padding: 18px 22px;
        border-bottom: 1px solid var(--gray-100);
    }
    .full-item:last-child { border-bottom: none; }
    .alert-success {
        background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46;
        border-radius: 10px; padding: 12px 18px; margin-bottom: 20px;
        font-size: .875rem; font-weight: 500; display: flex; align-items: center; gap: 8px;
    }
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
            <h1>📋 Détail de la prestation</h1>
            <p>{{ $serviceSchedule->service->name ?? '—' }} · Créée le {{ $serviceSchedule->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('service-schedules.index') }}" class="btn-back">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour
            </a>
            <a href="{{ route('service-schedules.print-single', $serviceSchedule) }}" 
            target="_blank"
            class="btn-edit"
            style="background:#10B981;border-color:#10B981;color:#fff">
                
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/>
                </svg>

                Imprimer
            </a>
            <a href="{{ route('service-schedules.edit', $serviceSchedule) }}" class="btn-edit">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Modifier
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Informations principales --}}
            <div class="detail-card">
                <div class="card-section-header">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/></svg>
                    Informations générales
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="d-label">Service</div>
                        <div class="d-value">{{ $serviceSchedule->service->name ?? '—' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="d-label">Vente associée</div>
                        <div class="d-value {{ !$serviceSchedule->sale ? 'muted' : '' }}">
                            {{ $serviceSchedule->sale ? 'Vente #'.$serviceSchedule->sale->id : 'Aucune' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="d-label">Date prévue</div>
                        <div class="d-value">
                            📅 {{ \Carbon\Carbon::parse($serviceSchedule->scheduled_date)->translatedFormat('d F Y') }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="d-label">Heure prévue</div>
                        <div class="d-value {{ !$serviceSchedule->scheduled_time ? 'muted' : '' }}">
                            {{ $serviceSchedule->scheduled_time
                                ? '🕐 '.substr($serviceSchedule->scheduled_time, 0, 5)
                                : 'Non définie' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Client --}}
            <div class="detail-card">
                <div class="card-section-header">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                    Informations client
                </div>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="d-label">Nom</div>
                        <div class="d-value {{ !$serviceSchedule->client_name ? 'muted' : '' }}">
                            {{ $serviceSchedule->client_name ?? 'Non renseigné' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="d-label">Contact</div>
                        <div class="d-value">📞 {{ $serviceSchedule->contact_number }}</div>
                    </div>
                </div>
                <div class="full-item">
                    <div class="d-label" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500);margin-bottom:5px">Adresse de prestation</div>
                    <div style="font-size:.95rem;color:var(--dark);">📍 {{ $serviceSchedule->address }}</div>
                </div>
            </div>

            {{-- Notes --}}
            @if($serviceSchedule->notes)
            <div class="detail-card">
                <div class="card-section-header">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Notes
                </div>
                <div class="notes-box">{{ $serviceSchedule->notes }}</div>
            </div>
            @endif

        </div>

        {{-- Colonne droite --}}
        <div class="col-lg-4">

            {{-- Statut actuel --}}
            <div class="detail-card mb-4">
                <div class="card-section-header">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                    Statut
                </div>
                <div style="padding:20px 22px">
                    <div style="margin-bottom:16px">
                        <span class="status-badge"
                            style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};font-size:.9rem;padding:7px 18px">
                            <span class="dot" style="background:{{ $cfg['dot'] }}"></span>
                            {{ $cfg['label'] }}
                        </span>
                    </div>
                    {{-- Changer statut rapide --}}
                    <form method="POST"
                        action="{{ route('service-schedules.update-status', $serviceSchedule) }}"
                        class="status-form" style="display:flex;gap:8px;align-items:center">
                        @csrf @method('PATCH')
                        <select name="status" style="flex:1">
                            @foreach(['pending'=>'En attente','confirmed'=>'Confirmé','completed'=>'Terminé','cancelled'=>'Annulé'] as $val => $label)
                                <option value="{{ $val }}" @selected($serviceSchedule->status === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit">Changer</button>
                    </form>
                </div>
            </div>

            {{-- Méta --}}
            <div class="detail-card">
                <div class="card-section-header">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
                    Historique
                </div>
                <div class="full-item">
                    <div class="d-label" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500);margin-bottom:4px">Créée le</div>
                    <div style="font-size:.9rem;color:var(--dark);font-weight:500">{{ $serviceSchedule->created_at->format('d/m/Y à H:i') }}</div>
                </div>
                <div class="full-item">
                    <div class="d-label" style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500);margin-bottom:4px">Dernière modification</div>
                    <div style="font-size:.9rem;color:var(--dark);font-weight:500">{{ $serviceSchedule->updated_at->format('d/m/Y à H:i') }}</div>
                </div>
                {{-- Supprimer --}}
                <div style="padding:16px 22px">
                    <form method="POST" action="{{ route('service-schedules.destroy', $serviceSchedule) }}"
                        onsubmit="return confirm('Supprimer définitivement cette prestation ?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            style="width:100%;background:#FEF2F2;color:#DC2626;border:1.5px solid #FCA5A5;padding:10px;border-radius:10px;font-weight:600;font-size:.875rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer cette prestation
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection