{{--
    _form.blade.php — Admin planification
    Variables attendues : $schedule (nullable), $services, $sales
--}}
@php
    $isEdit = isset($schedule) && $schedule->exists;
    $action = $isEdit
        ? route('admin.schedules.update', $schedule)
        : route('admin.schedules.store');
    $title  = $isEdit ? 'Modifier la prestation' : 'Nouvelle prestation';
@endphp

@extends('layouts.app')
@section('title', 'Admin · ' . $title)
@section('page-title', 'Administration · Planification')

@section('content')
<style>
:root {
    --p6:#7C3AED; --p7:#6D28D9; --p1:#EDE9FE; --p05:#F5F3FF;
    --dark:#1E1B4B; --g1:#F3F4F6; --g2:#E5E7EB; --g5:#6B7280; --g7:#374151;
}
.page-header {
    background:linear-gradient(135deg,#6D28D9,#7C3AED);
    border-radius:16px; padding:24px 28px; color:#fff;
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:12px; margin-bottom:24px;
}
.page-header h1 { font-size:1.4rem; font-weight:700; margin:0; }
.page-header p  { margin:4px 0 0; opacity:.8; font-size:.85rem; }
.btn-ghost {
    background:rgba(255,255,255,.15); color:#fff;
    border:2px solid rgba(255,255,255,.3); padding:9px 18px;
    border-radius:10px; font-size:.875rem; font-weight:500;
    text-decoration:none; display:inline-flex; align-items:center; gap:6px;
}
.btn-ghost:hover { background:rgba(255,255,255,.25); color:#fff; }
.form-card {
    background:#fff; border-radius:16px; border:1px solid var(--g2);
    overflow:hidden; box-shadow:0 1px 5px rgba(0,0,0,.05); margin-bottom:20px;
}
.sec-header {
    background:var(--p05); border-bottom:1px solid var(--p1);
    padding:12px 22px; font-size:.75rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.07em; color:var(--p7);
    display:flex; align-items:center; gap:8px;
}
.form-body { padding:22px; }
.form-group { margin-bottom:18px; }
.form-group label {
    display:block; font-size:.75rem; font-weight:700;
    color:var(--g7); margin-bottom:6px;
    text-transform:uppercase; letter-spacing:.04em;
}
.req { color:#DC2626; margin-left:2px; }
.opt { color:var(--g5); font-weight:400; font-size:.7rem; text-transform:none; margin-left:4px; }
.form-control {
    width:100%; border:1.5px solid var(--g2); border-radius:10px;
    padding:10px 13px; font-size:.9rem; color:var(--dark);
    background:var(--g1); outline:none;
    transition:border-color .15s,background .15s,box-shadow .15s;
    font-family:inherit;
}
.form-control:focus {
    border-color:var(--p6); background:#fff;
    box-shadow:0 0 0 3px rgba(124,58,237,.1);
}
.form-control.is-invalid { border-color:#DC2626; background:#FEF2F2; }
.invalid-feedback { color:#DC2626; font-size:.78rem; margin-top:4px; display:block; }
textarea.form-control { resize:vertical; min-height:86px; }
.hint { font-size:.75rem; color:var(--g5); margin-top:4px; }
.status-grid {
    display:grid; grid-template-columns:repeat(2,1fr); gap:10px;
}
@media(min-width:768px){ .status-grid { grid-template-columns:repeat(4,1fr); } }
.status-opt input[type="radio"] { display:none; }
.status-opt label {
    display:flex; align-items:center; gap:8px;
    padding:10px 13px; border:2px solid var(--g2); border-radius:10px;
    cursor:pointer; font-size:.85rem; font-weight:500; color:var(--g7);
    transition:all .15s; background:var(--g1);
    text-transform:none; letter-spacing:0;
}
.status-opt input:checked + label {
    border-color:var(--p6); background:var(--p05); color:var(--p7);
    box-shadow:0 0 0 3px rgba(124,58,237,.1);
}
.sdot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
.form-footer {
    padding:18px 22px; border-top:1px solid var(--g1);
    display:flex; gap:12px; align-items:center; justify-content:flex-end;
    background:var(--g1);
}
.btn-submit {
    background:var(--p7); color:#fff; border:none;
    padding:11px 26px; border-radius:10px; font-weight:700;
    font-size:.9rem; cursor:pointer;
    display:inline-flex; align-items:center; gap:8px; transition:background .15s;
}
.btn-submit:hover { background:var(--p6); }
.btn-cancel {
    background:#fff; color:var(--g7);
    border:1.5px solid var(--g2); padding:11px 20px;
    border-radius:10px; font-weight:600; font-size:.9rem;
    text-decoration:none; display:inline-flex; align-items:center; gap:6px;
    transition:border-color .15s;
}
.btn-cancel:hover { border-color:var(--g5); color:var(--g7); }
</style>

<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1>{{ $isEdit ? '✏️ Modifier' : '📅 Nouvelle' }} prestation</h1>
            <p>{{ $isEdit ? 'Modifiez les informations de la prestation #'.$schedule->id : 'Planifiez une nouvelle prestation de service' }}</p>
        </div>
        <a href="{{ route('admin.schedules') }}" class="btn-ghost">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
    </div>

    <form method="POST" action="{{ $action }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="row g-4">

            {{-- ── Colonne principale ── --}}
            <div class="col-lg-8">

                {{-- Service & Vente --}}
                <div class="form-card">
                    <div class="sec-header">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/></svg>
                        Service & Vente
                    </div>
                    <div class="form-body">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Service <span class="req">*</span></label>
                                    <select name="service_id"
                                        class="form-control @error('service_id') is-invalid @enderror">
                                        <option value="">— Sélectionner —</option>
                                        @foreach($services as $s)
                                            <option value="{{ $s->id }}"
                                                @selected(old('service_id', $isEdit ? $schedule->service_id : '') == $s->id)>
                                                {{ $s->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Vente associée <span class="opt">(optionnel)</span></label>
                                    <select name="sale_id"
                                        class="form-control @error('sale_id') is-invalid @enderror">
                                        <option value="">— Aucune —</option>
                                        @foreach($sales as $sale)
                                            <option value="{{ $sale->id }}"
                                                @selected(old('sale_id', $isEdit ? $schedule->sale_id : '') == $sale->id)>
                                                Vente #{{ $sale->id }}{{ $sale->client ? ' — '.$sale->client->name : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sale_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Client --}}
                <div class="form-card">
                    <div class="sec-header">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg>
                        Informations client
                    </div>
                    <div class="form-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom du client <span class="opt">(optionnel)</span></label>
                                    <input type="text" name="client_name"
                                        class="form-control @error('client_name') is-invalid @enderror"
                                        placeholder="Ex : Aminata Diallo"
                                        value="{{ old('client_name', $isEdit ? $schedule->client_name : '') }}">
                                    @error('client_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contact <span class="req">*</span></label>
                                    <input type="text" name="contact_number"
                                        class="form-control @error('contact_number') is-invalid @enderror"
                                        placeholder="+227 90 00 00 00"
                                        value="{{ old('contact_number', $isEdit ? $schedule->contact_number : '') }}">
                                    @error('contact_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group" style="margin-bottom:0">
                                    <label>Adresse <span class="req">*</span></label>
                                    <textarea name="address" rows="2"
                                        class="form-control @error('address') is-invalid @enderror"
                                        placeholder="Adresse complète de la prestation...">{{ old('address', $isEdit ? $schedule->address : '') }}</textarea>
                                    @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="form-card">
                    <div class="sec-header">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Notes <span class="opt" style="font-size:.7rem;margin-left:4px">(optionnel)</span>
                    </div>
                    <div class="form-body">
                        <div class="form-group" style="margin-bottom:0">
                            <textarea name="notes" rows="4"
                                class="form-control @error('notes') is-invalid @enderror"
                                placeholder="Instructions spéciales, informations complémentaires...">{{ old('notes', $isEdit ? $schedule->notes : '') }}</textarea>
                            @error('notes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Colonne droite ── --}}
            <div class="col-lg-4">

                {{-- Date & Heure --}}
                <div class="form-card">
                    <div class="sec-header">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                        Date & Heure
                    </div>
                    <div class="form-body">
                        <div class="form-group">
                            <label>Date prévue <span class="req">*</span></label>
                            <input type="date" name="scheduled_date"
                                class="form-control @error('scheduled_date') is-invalid @enderror"
                                value="{{ old('scheduled_date', $isEdit ? $schedule->scheduled_date : '') }}">
                            @error('scheduled_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label>Heure <span class="opt">(optionnel)</span></label>
                            <input type="time" name="scheduled_time"
                                class="form-control @error('scheduled_time') is-invalid @enderror"
                                value="{{ old('scheduled_time', $isEdit ? ($schedule->scheduled_time ? substr($schedule->scheduled_time,0,5) : '') : '') }}">
                            @error('scheduled_time')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <div class="hint">Laissez vide si non définie</div>
                        </div>
                    </div>
                </div>

                {{-- Statut --}}
                <div class="form-card">
                    <div class="sec-header">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                        Statut
                    </div>
                    <div class="form-body">
                        <div class="status-grid">
                            @foreach([
                                'pending'   => ['En attente',  '#F59E0B'],
                                'confirmed' => ['Confirmé',    '#7C3AED'],
                                'completed' => ['Terminé',     '#10B981'],
                                'cancelled' => ['Annulé',      '#EF4444'],
                            ] as $val => [$label, $dot])
                            <div class="status-opt">
                                <input type="radio" name="status" id="st_{{ $val }}" value="{{ $val }}"
                                    @checked(old('status', $isEdit ? $schedule->status : 'pending') === $val)>
                                <label for="st_{{ $val }}">
                                    <span class="sdot" style="background:{{ $dot }}"></span>
                                    {{ $label }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('status')<span class="invalid-feedback d-block mt-2">{{ $message }}</span>@enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="form-card">
            <div class="form-footer">
                <a href="{{ route('admin.schedules') }}" class="btn-cancel">Annuler</a>
                <button type="submit" class="btn-submit">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                    {{ $isEdit ? 'Enregistrer les modifications' : 'Planifier la prestation' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection