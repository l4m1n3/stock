{{--
    Vue utilisée par create.blade.php et edit.blade.php
    Variables : $schedule (nullable), $services, $sales
--}}

@php
    $isEdit   = isset($schedule) && $schedule->exists;
    $action   = $isEdit
        ? route('service-schedules.update', $schedule)
        : route('service-schedules.store');
    $method   = $isEdit ? 'PUT' : 'POST';
    $title    = $isEdit ? 'Modifier la prestation' : 'Nouvelle prestation';
@endphp

@extends('layouts.app')
@section('title', $title)

@section('content')

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
    .form-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.06);
    }
    .form-section-header {
        background: var(--purple-50);
        border-bottom: 1px solid var(--purple-100);
        padding: 14px 24px;
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--purple-700);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-body { padding: 24px; }
    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: block;
        font-size: .8rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .form-group label .required { color: #DC2626; margin-left: 2px; }
    .form-control {
        width: 100%;
        border: 1.5px solid var(--gray-200);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: .9rem;
        color: var(--dark);
        background: var(--gray-100);
        outline: none;
        transition: border-color .15s, background .15s, box-shadow .15s;
        font-family: inherit;
    }
    .form-control:focus {
        border-color: var(--purple-600);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(124,58,237,.1);
    }
    .form-control.is-invalid { border-color: #DC2626; background: #FEF2F2; }
    .invalid-feedback { color: #DC2626; font-size: .8rem; margin-top: 5px; display: block; }
    select.form-control { cursor: pointer; }
    textarea.form-control { resize: vertical; min-height: 90px; }
    .status-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    @media(min-width:768px) { .status-grid { grid-template-columns: repeat(4, 1fr); } }
    .status-option input[type="radio"] { display: none; }
    .status-option label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        cursor: pointer;
        font-size: .85rem;
        font-weight: 500;
        color: var(--gray-700);
        transition: all .15s;
        background: var(--gray-100);
        text-transform: none;
        letter-spacing: 0;
    }
    .status-option input:checked + label {
        border-color: var(--purple-600);
        background: var(--purple-50);
        color: var(--purple-700);
        box-shadow: 0 0 0 3px rgba(124,58,237,.1);
    }
    .status-option .dot {
        width: 10px; height: 10px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .form-footer {
        padding: 20px 24px;
        border-top: 1px solid var(--gray-100);
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: flex-end;
        background: var(--gray-100);
    }
    .btn-submit {
        background: var(--purple-700);
        color: #fff;
        border: none;
        padding: 11px 28px;
        border-radius: 10px;
        font-weight: 700;
        font-size: .9rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background .15s;
    }
    .btn-submit:hover { background: var(--purple-600); }
    .btn-cancel {
        background: #fff;
        color: var(--gray-700);
        border: 1.5px solid var(--gray-200);
        padding: 11px 22px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .9rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: border-color .15s;
    }
    .btn-cancel:hover { border-color: var(--gray-500); color: var(--gray-700); }
    .hint { font-size: .78rem; color: var(--gray-500); margin-top: 4px; }
</style>

<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1>{{ $isEdit ? '✏️ '.$title : '📅 '.$title }}</h1>
            <p>{{ $isEdit ? 'Modifiez les informations de la prestation' : 'Planifiez une nouvelle prestation de service' }}</p>
        </div>
        <a href="{{ route('service-schedules.index') }}" class="btn-cancel">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="row g-4">

            {{-- Colonne principale --}}
            <div class="col-lg-8">

                {{-- Section Service & Vente --}}
                <div class="form-card mb-4">
                    <div class="form-section-header">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/></svg>
                        Service & Vente
                    </div>
                    <div class="form-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Service <span class="required">*</span></label>
                                    <select name="service_id"
                                        class="form-control @error('service_id') is-invalid @enderror">
                                        <option value="">— Sélectionner un service —</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}"
                                                @selected(old('service_id', $isEdit ? $schedule->service_id : '') == $service->id)>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Vente associée <span style="color:var(--gray-500);font-weight:400">(optionnel)</span></label>
                                    <select name="sale_id"
                                        class="form-control @error('sale_id') is-invalid @enderror">
                                        <option value="">— Aucune vente —</option>
                                        @foreach($sales as $sale)
                                            <option value="{{ $sale->id }}"
                                                @selected(old('sale_id', $isEdit ? $schedule->sale_id : '') == $sale->id)>
                                                Vente #{{ $sale->id }}
                                                @if($sale->client) — {{ $sale->client->name }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sale_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Client --}}
                <div class="form-card mb-4">
                    <div class="form-section-header">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                        Informations client
                    </div>
                    <div class="form-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom du client</label>
                                    <input type="text" name="client_name"
                                        class="form-control @error('client_name') is-invalid @enderror"
                                        placeholder="Ex : Aminata Diallo"
                                        value="{{ old('client_name', $isEdit ? $schedule->client_name : '') }}">
                                    @error('client_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Numéro de contact <span class="required">*</span></label>
                                    <input type="text" name="contact_number"
                                        class="form-control @error('contact_number') is-invalid @enderror"
                                        placeholder="Ex : +227 90 00 00 00"
                                        value="{{ old('contact_number', $isEdit ? $schedule->contact_number : '') }}">
                                    @error('contact_number')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group" style="margin-bottom:0">
                                    <label>Adresse de prestation <span class="required">*</span></label>
                                    <textarea name="address" rows="2"
                                        class="form-control @error('address') is-invalid @enderror"
                                        placeholder="Adresse complète où se déroule la prestation...">{{ old('address', $isEdit ? $schedule->address : '') }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Notes --}}
                <div class="form-card">
                    <div class="form-section-header">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Notes & Remarques
                    </div>
                    <div class="form-body">
                        <div class="form-group" style="margin-bottom:0">
                            <label>Notes internes <span style="color:var(--gray-500);font-weight:400">(optionnel)</span></label>
                            <textarea name="notes" rows="4"
                                class="form-control @error('notes') is-invalid @enderror"
                                placeholder="Instructions spéciales, informations complémentaires...">{{ old('notes', $isEdit ? $schedule->notes : '') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- Colonne droite --}}
            <div class="col-lg-4">

                {{-- Date & Heure --}}
                <div class="form-card mb-4">
                    <div class="form-section-header">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        Date & Heure
                    </div>
                    <div class="form-body">
                        <div class="form-group">
                            <label>Date prévue <span class="required">*</span></label>
                            <input type="date" name="scheduled_date"
                                class="form-control @error('scheduled_date') is-invalid @enderror"
                                value="{{ old('scheduled_date', $isEdit ? $schedule->scheduled_date : '') }}">
                            @error('scheduled_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label>Heure <span style="color:var(--gray-500);font-weight:400">(optionnel)</span></label>
                            <input type="time" name="scheduled_time"
                                class="form-control @error('scheduled_time') is-invalid @enderror"
                                value="{{ old('scheduled_time', $isEdit ? ($schedule->scheduled_time ? substr($schedule->scheduled_time, 0, 5) : '') : '') }}">
                            @error('scheduled_time')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <div class="hint">Laissez vide si l'heure n'est pas encore définie</div>
                        </div>
                    </div>
                </div>

                {{-- Statut --}}
                <div class="form-card">
                    <div class="form-section-header">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                        Statut
                    </div>
                    <div class="form-body">
                        <div class="status-grid">
                            @foreach([
                                'pending'   => ['En attente',  '#F59E0B', '#FEF3C7', '#D97706'],
                                'confirmed' => ['Confirmé',    '#7C3AED', '#EDE9FE', '#7C3AED'],
                                'completed' => ['Terminé',     '#10B981', '#D1FAE5', '#059669'],
                                'cancelled' => ['Annulé',      '#EF4444', '#FEE2E2', '#DC2626'],
                            ] as $val => [$label, $dot, $bg, $text])
                            <div class="status-option">
                                <input type="radio" name="status" id="status_{{ $val }}" value="{{ $val }}"
                                    @checked(old('status', $isEdit ? $schedule->status : 'pending') === $val)>
                                <label for="status_{{ $val }}">
                                    <span class="dot" style="background:{{ $dot }}"></span>
                                    {{ $label }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('status')
                            <span class="invalid-feedback d-block mt-2">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="form-card mt-4">
            <div class="form-footer">
                <a href="{{ route('service-schedules.index') }}" class="btn-cancel">Annuler</a>
                <button type="submit" class="btn-submit">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $isEdit ? 'Enregistrer les modifications' : 'Planifier la prestation' }}
                </button>
            </div>
        </div>

    </form>
</div>
@endsection