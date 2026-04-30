{{-- resources/views/admin/branches/create.blade.php --}}
@extends('layouts.apps')
@section('title', 'Nouvelle Branche')
@section('page-title', 'Créer une Branche')

@section('content')
<div class="row justify-content-center">
<div class="col-12 col-md-6">
<div style="background:white;border-radius:16px;padding:32px;box-shadow:0 4px 20px rgba(107,70,193,0.08);">

    <h5 style="font-weight:800;color:#1a0a3e;margin-bottom:24px;">
        <i class="fas fa-plus-circle me-2" style="color:#6B46C1;"></i> Nouvelle branche
    </h5>

    <form method="POST" action="{{ route('admin.branches.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-600" style="font-size:13px;color:#4a3a7a;">Nom de la branche *</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="ex: Niamey Centre" style="border-radius:10px;">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label class="form-label fw-600" style="font-size:13px;color:#4a3a7a;">Ville</label>
            <input type="text" name="city" value="{{ old('city') }}"
                   class="form-control @error('city') is-invalid @enderror"
                   placeholder="ex: Niamey" style="border-radius:10px;">
            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.branches') }}" class="btn btn-outline-secondary" style="border-radius:10px;">Annuler</a>
            <button type="submit" class="btn-violet flex-fill justify-content-center">
                <i class="fas fa-check"></i> Créer la branche
            </button>
        </div>
    </form>
</div>
</div>
</div>
@endsection

{{-- ─────────────────────────────────────────────────────────── --}}
{{-- resources/views/admin/branches/edit.blade.php              --}}
{{-- (créer ce fichier séparé, contenu quasi-identique)         --}}