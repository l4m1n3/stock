@extends('layouts.apps')

@section('title', $client->name)
@section('page-title', 'Fiche client')

@section('content')

{{-- ══════════════════════ BREADCRUMB ══════════════════════ --}}
<div class="mb-3" style="font-size:13px;color:#aaa;">
    <a href="{{ route('admin.clients.index') }}" style="color:#6B46C1;text-decoration:none;">
        <i class="fas fa-users me-1"></i>Clients
    </a>
    <span class="mx-2">/</span>
    <span style="color:#444;">{{ $client->name }}</span>
</div>

{{-- ══════════════════════ HEADER CARD ══════════════════════ --}}
<div class="profile-header-card mb-4">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        {{-- Avatar --}}
        <div class="profile-avatar"
             style="background:{{ $client->avatar_bg }};color:{{ $client->avatar_color }};">
            {{ strtoupper(mb_substr($client->name, 0, 1)) }}{{ strtoupper(mb_substr(strstr($client->name, ' '), 1, 1)) }}
        </div>

        {{-- Name + tier + meta --}}
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h1 class="profile-name">{{ $client->name }}</h1>
                <span class="tier-badge"
                      style="background:{{ $client->tier_bg }};color:{{ $client->tier_color }};">
                   
                </span>
            </div>
            <div class="profile-meta">
                @if($client->phone)
                    <span><i class="fas fa-phone me-1"></i>{{ $client->phone }}</span>
                @endif
                @if($client->address)
                    <span><i class="fas fa-map-marker-alt me-1"></i>{{ $client->address }}</span>
                @endif
                <span><i class="fas fa-store me-1"></i>{{ $client->branch->name }}</span>
                <span><i class="fas fa-calendar me-1"></i>Client depuis {{ $client->created_at->translatedFormat('M Y') }}</span>
            </div>
        </div>

        {{-- Edit button --}}
        <button type="button" class="btn-violet"
            onclick="openEdit(
                {{ $client->id }},
                '{{ addslashes($client->name) }}',
                '{{ addslashes($client->phone ?? '') }}',
                '{{ addslashes($client->address ?? '') }}',
                {{ $client->branch_id }},
                '{{ addslashes($client->notes ?? '') }}'
            )">
            <i class="fas fa-pen me-1"></i> Modifier
        </button>
    </div>

    @if($client->notes)
        <div class="notes-box mt-3">
            <i class="fas fa-sticky-note me-1" style="color:#6B46C1;"></i>
            <em style="font-size:13px;color:#555;">{{ $client->notes }}</em>
        </div>
    @endif
</div>

{{-- ══════════════════════ KPI CARDS ══════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-coins me-1"></i> Total dépensé</div>
            <div class="stat-val">{{ number_format($totalSpent / 1000, 0, ',', ' ') }}k</div>
            <div class="stat-sub">FCFA cumulé</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-receipt me-1"></i> Visites</div>
            <div class="stat-val">{{ $totalVisits }}</div>
            <div class="stat-sub">achats enregistrés</div>
        </div>
    </div>
    {{-- <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-chart-line me-1"></i> Panier moyen</div>
            <div class="stat-val">
                {{ number_format($avgTicket / 1000, 0, ',', ' ') }}k
            </div>
            <div class="stat-sub">FCFA / visite</div>
        </div>
    </div> --}}
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-calendar-check me-1"></i> Dernière visite</div>
            <div class="stat-val" style="font-size:15px;">
                {{ $lastVisit?->created_at->diffForHumans() ?? '—' }}
            </div>
            <div class="stat-sub">{{ $lastVisit?->created_at->format('d/m/Y') ?? '' }}</div>
        </div>
    </div>
</div>

{{-- ══════════════════════ SPENDING CHART + HISTORY ══════════════════════ --}}
<div class="row g-3">

    {{-- Mini bar chart (last 6 months) --}}
    <div class="col-md-5">
        <div class="main-card h-100">
            <div class="main-card-header">
                <span class="card-title-text">Dépenses — 6 derniers mois</span>
            </div>
            <div class="p-3">
                @forelse($spendingByMonth as $month => $total)
                    @php
                        $maxMonth = $spendingByMonth->max() ?: 1;
                        $pct      = round($total / $maxMonth * 100);
                        $label    = \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('M y');
                    @endphp
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width:44px;font-size:11px;color:#aaa;text-align:right;">{{ $label }}</div>
                        <div style="flex:1;height:12px;background:#f0ebff;border-radius:99px;overflow:hidden;">
                            <div style="width:{{ $pct }}%;height:100%;background:#6B46C1;border-radius:99px;"></div>
                        </div>
                        <div style="width:52px;font-size:11px;color:#444;text-align:right;">
                            {{ number_format($total / 1000, 0, ',', ' ') }}k
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-4" style="font-size:13px;">
                        Aucune donnée disponible.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent sales history --}}
    <div class="col-md-7">
        <div class="main-card">
            <div class="main-card-header">
                <span class="card-title-text">Historique des achats récents</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Référence</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td style="font-size:13px;color:#888;">
                                {{ $sale->created_at->format('d/m/Y') }}
                            </td>
                            <td style="font-size:13px;">
                                #{{ $sale->id }}
                                @if($sale->reference ?? null)
                                    <span class="text-muted">— {{ $sale->reference }}</span>
                                @endif
                            </td>
                            <td class="text-end" style="font-size:13px;font-weight:600;">
                                {{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="fas fa-receipt fa-2x mb-2 d-block" style="opacity:.2;"></i>
                                Aucun achat enregistré.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sales->count() === 20)
                <div class="px-3 py-2 border-top" style="font-size:11px;color:#aaa;">
                    Affichage des 20 achats les plus récents uniquement.
                </div>
            @endif
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL — MODIFICATION CLIENT (réutilisé depuis index)       --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal-backdrop-custom" id="modal-edit" onclick="closeModal('modal-edit')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header-custom">
            <div class="modal-title-custom">
                <div class="modal-icon" style="background:#EAF3DE;color:#3B6D11;">
                    <i class="fas fa-pen"></i>
                </div>
                <span>Modifier le client</span>
            </div>
            <button class="modal-close-btn" onclick="closeModal('modal-edit')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" id="form-edit" class="modal-body-custom">
            @csrf
            @method('PUT')

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label-custom">Nom complet <span class="required">*</span></label>
                    <input type="text" name="name" id="edit-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label-custom">Téléphone</label>
                    <input type="text" name="phone" id="edit-phone" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label-custom">Adresse</label>
                <input type="text" name="address" id="edit-address" class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label-custom">Agence <span class="required">*</span></label>
                <select name="branch_id" id="edit-branch" class="form-select" required>
                    <option value="">— Sélectionner —</option>
                    @foreach(\App\Models\Branch::orderBy('name')->get() as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label-custom">Notes internes</label>
                <textarea name="notes" id="edit-notes" class="form-control" rows="2"></textarea>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-cancel" onclick="closeModal('modal-edit')">Annuler</button>
                <button type="submit" class="btn-violet">
                    <i class="fas fa-check me-1"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

@endsection


@push('styles')
<style>
    /* ── Profile header ─────────────────────────────────────── */
    .profile-header-card {
        background: white;
        border-radius: 14px;
        border: 0.5px solid #e5e0f5;
        padding: 20px;
    }
    .profile-avatar {
        width: 60px; height: 60px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 800; flex-shrink: 0;
    }
    .profile-name {
        font-size: 20px; font-weight: 800; color: #1a1a2e;
        margin: 0; line-height: 1.2;
    }
    .profile-meta {
        display: flex; flex-wrap: wrap; gap: 12px;
        font-size: 12px; color: #888; margin-top: 5px;
    }
    .tier-badge {
        display: inline-flex; align-items: center;
        padding: 2px 10px; border-radius: 99px;
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .04em;
    }
    .notes-box {
        background: #faf8ff;
        border: 0.5px solid #e5e0f5;
        border-radius: 8px;
        padding: 10px 14px;
    }

    /* ── Stat cards (same as index) ─────────────────────────── */
    .stat-card {
        background: white;
        border-radius: 12px;
        border: 0.5px solid #e5e0f5;
        padding: 14px 16px;
    }
    .stat-label { font-size: 11px; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
    .stat-val   { font-size: 22px; font-weight: 800; color: #1a1a2e; line-height: 1.1; }
    .stat-sub   { font-size: 11px; color: #bbb; margin-top: 2px; }

    /* ── Main card ──────────────────────────────────────────── */
    .main-card { background: white; border-radius: 14px; border: 0.5px solid #e5e0f5; overflow: hidden; }
    .main-card-header {
        padding: 14px 20px;
        display: flex; align-items: center; justify-content: space-between;
        border-bottom: 0.5px solid #f0ebff; flex-wrap: wrap; gap: 10px;
    }
    .card-title-text { font-size: 15px; font-weight: 700; color: #1a1a2e; }

    /* ── Modals (mirror index styles) ───────────────────────── */
    .modal-backdrop-custom {
        display: none; position: fixed; inset: 0;
        background: rgba(30,10,60,.45); z-index: 1050;
        align-items: center; justify-content: center; padding: 20px;
    }
    .modal-backdrop-custom.open { display: flex; }
    .modal-box { background: white; border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 8px 40px rgba(107,70,193,.18); animation: modalIn .2s ease; overflow: hidden; }
    @keyframes modalIn { from { opacity:0; transform:translateY(-12px) scale(.98); } to { opacity:1; transform:none; } }
    .modal-header-custom { display:flex; align-items:center; justify-content:space-between; padding:18px 20px 14px; border-bottom:.5px solid #f0ebff; }
    .modal-title-custom { display:flex; align-items:center; gap:10px; font-size:15px; font-weight:700; color:#1a1a2e; }
    .modal-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
    .modal-close-btn { width:30px; height:30px; border-radius:8px; border:none; background:#f4f2fc; color:#888; font-size:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; }
    .modal-close-btn:hover { background:#e5e0f5; }
    .modal-body-custom { padding:20px; }
    .modal-footer-custom { display:flex; justify-content:flex-end; gap:8px; margin-top:20px; padding-top:16px; border-top:.5px solid #f0ebff; }
    .form-row-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    @media (max-width:480px) { .form-row-2 { grid-template-columns:1fr; } }
    .form-group { margin-bottom:14px; }
    .form-label-custom { display:block; font-size:12px; font-weight:600; color:#555; margin-bottom:5px; text-transform:uppercase; letter-spacing:.04em; }
    .required { color:#E24B4A; }
    .form-control, .form-select { border-radius:8px !important; border:.5px solid #d8d0f0 !important; font-size:13px !important; padding:8px 12px !important; color:#1a1a2e !important; }
    .form-control:focus, .form-select:focus { border-color:#6B46C1 !important; box-shadow:0 0 0 3px rgba(107,70,193,.12) !important; }
    .btn-cancel { padding:8px 18px; border-radius:8px; border:.5px solid #ddd; background:white; color:#666; font-size:13px; font-weight:600; cursor:pointer; }
    .btn-cancel:hover { background:#f4f2fc; }
</style>
@endpush


@push('scripts')
<script>
    function openModal(id)  { document.getElementById(id).classList.add('open');    document.body.style.overflow = 'hidden'; }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); document.body.style.overflow = ''; }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal('modal-edit');
    });

    function openEdit(id, name, phone, address, branchId, notes) {
        document.getElementById('edit-name').value    = name;
        document.getElementById('edit-phone').value   = phone;
        document.getElementById('edit-address').value = address;
        document.getElementById('edit-notes').value   = notes;
        document.getElementById('edit-branch').value  = branchId;
        document.getElementById('form-edit').action   = `/clients/${id}`;
        openModal('modal-edit');
    }
</script>
@endpush