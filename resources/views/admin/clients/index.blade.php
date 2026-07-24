@extends('layouts.apps')

@section('title', 'Clients')
@section('page-title', 'Clients')

@section('content')

{{-- ══════════════════════ STATS ══════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-users me-1"></i> Clients</div>
            <div class="stat-val">{{ $totalClients }}</div>
            <div class="stat-sub">toutes agences</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-coins me-1"></i> Total dépensé</div>
            <div class="stat-val">{{ number_format($totalSpent / 1000, 0, ',', ' ') }}k</div>
            <div class="stat-sub">FCFA cumulé</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-receipt me-1"></i> Visites totales</div>
            <div class="stat-val">{{ $totalVisits }}</div>
            <div class="stat-sub">achats enregistrés</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label"><i class="fas fa-chart-line me-1"></i> Panier moyen</div>
            <div class="stat-val">
                {{ $totalClients > 0 ? number_format($totalSpent / $totalClients / 1000, 0, ',', ' ') : 0 }}k
            </div>
            <div class="stat-sub">FCFA / client</div>
        </div>
    </div>
</div>

{{-- ══════════════════════ TABLE ══════════════════════ --}}
<div class="main-card">

    {{-- Toolbar --}}
    <div class="main-card-header">
        <span class="card-title-text">Liste des clients</span>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <form method="GET" action="{{ route('admin.clients.index') }}" class="d-flex gap-2 flex-wrap align-items-center">
                <input
                    type="text"
                    name="search"
                    class="form-control form-control-sm"
                    placeholder="Rechercher…"
                    value="{{ request('search') }}"
                    style="width:180px;"
                >
                <select name="branch_id" class="form-select form-select-sm" style="width:150px;" onchange="this.form.submit()">
                    <option value="">Toutes les agences</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <select name="sort" class="form-select form-select-sm" style="width:160px;" onchange="this.form.submit()">
                    <option value="spent"  {{ request('sort', 'spent') === 'spent'  ? 'selected' : '' }}>Total dépensé</option>
                    <option value="visits" {{ request('sort') === 'visits' ? 'selected' : '' }}>Visites</option>
                    <option value="name"   {{ request('sort') === 'name'   ? 'selected' : '' }}>Nom A→Z</option>
                </select>
                <button type="submit" class="btn-violet" style="padding:7px 14px;">
                    <i class="fas fa-search"></i>
                </button>
                @if(request('search') || request('branch_id'))
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>

            {{-- Bouton ouvrir modal ajout --}}
            <button type="button" class="btn-violet" onclick="openCreate()">
                <i class="fas fa-plus"></i> Nouveau client
            </button>

            <a href="{{ route('admin.clients.export') }}" class="btn btn-sm btn-outline-secondary rounded-3 fw-600">
                <i class="fas fa-file-export me-1"></i> Export
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:42px;">#</th>
                    <th>Client</th>
                    <th>Agence</th>
                    <th class="text-end">Visites</th>
                    <th class="text-end">Total dépensé</th>
                    <th class="text-end" style="width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr>
                    <td>
                        <span class="rank-num {{ $loop->iteration <= 3 && request('sort', 'spent') !== 'name' ? 'rank-top' : '' }}">
                            {{ request('sort') !== 'name' ? $loop->iteration : '' }}
                        </span>
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="client-avatar" style="background:{{ $client->avatar_bg }};color:{{ $client->avatar_color }};">
                                {{ strtoupper(mb_substr($client->name, 0, 1)) }}{{ strtoupper(mb_substr(strstr($client->name, ' '), 1, 1)) }}
                            </div>
                            <div>
                                <div class="fw-600" style="font-size:13px;">{{ $client->name }}</div>
                                <div style="font-size:11px;color:#aaa;">{{ $client->phone ?? '—' }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="text-muted" style="font-size:13px;">{{ $client->branch->name }}</td>
                    <td class="text-end text-muted" style="font-size:13px;">{{ $client->total_visits }}</td>

                    <td class="text-end" style="font-size:13px;">
                        {{ number_format($client->total_spent, 0, ',', ' ') }} FCFA
                        @if($maxSpent > 0)
                            <span class="bar-wrap ms-1">
                                <span class="bar-fill" style="width:{{ round($client->total_spent / $maxSpent * 100) }}%;"></span>
                            </span>
                        @endif
                    </td>

                    <td class="text-end">
                        <a href="{{ route('admin.clients.show', $client) }}" class="action-btn" title="Voir le profil">
                            <i class="fas fa-eye"></i>
                        </a>
                        {{-- Bouton modifier --}}
                        <button type="button" class="action-btn" title="Modifier"
                            onclick="openEdit(
                                {{ $client->id }},
                                '{{ addslashes($client->name) }}',
                                '{{ addslashes($client->phone ?? '') }}',
                                '{{ addslashes($client->address ?? '') }}',
                                {{ $client->branch_id }},
                                '{{ addslashes($client->notes ?? '') }}'
                            )">
                            <i class="fas fa-pen"></i>
                        </button>
                        {{-- Bouton supprimer --}}
                        <button type="button" class="action-btn" title="Supprimer"
                            style="color:#E24B4A;"
                            onclick="openDelete({{ $client->id }}, '{{ addslashes($client->name) }}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fas fa-users fa-2x mb-2 d-block" style="opacity:.25;"></i>
                        Aucun client trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top"
         style="font-size:12px;color:#aaa;">
        <span>
            @if($clients->total() > 0)
                {{ $clients->firstItem() }}–{{ $clients->lastItem() }} sur {{ $clients->total() }} client{{ $clients->total() > 1 ? 's' : '' }}
            @else
                0 client
            @endif
        </span> 
        @if($clients->hasPages())
            {{ $clients->appends(request()->query())->links('pagination::bootstrap-5') }}
        @endif
    </div>

</div>


{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL — AJOUT CLIENT                                       --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal-backdrop-custom" id="modal-create" onclick="closeModal('modal-create')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header-custom">
            <div class="modal-title-custom">
                <div class="modal-icon" style="background:#EEEDFE;color:#6B46C1;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <span>Nouveau client</span>
            </div>
            <button class="modal-close-btn" onclick="closeModal('modal-create')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.clients.store') }}" class="modal-body-custom">
            @csrf

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label-custom">Nom complet <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Ex : Aïcha Moussa" required>
                </div>
                <div class="form-group">
                    <label class="form-label-custom">Téléphone</label>
                    <input type="text" name="phone" class="form-control" placeholder="+227 90 00 00 00">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label-custom">Adresse</label>
                <input type="text" name="address" class="form-control" placeholder="Quartier, ville…">
            </div>

            <div class="form-group">
                <label class="form-label-custom">Agence <span class="required">*</span></label>
                <select name="branch_id" class="form-select" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label-custom">Notes internes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Remarques, préférences…"></textarea>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-cancel" onclick="closeModal('modal-create')">Annuler</button>
                <button type="submit" class="btn-violet">
                    <i class="fas fa-check me-1"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL — MODIFICATION CLIENT                                --}}
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
                    @foreach($branches as $branch)
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


{{-- ══════════════════════════════════════════════════════════ --}}
{{-- MODAL — SUPPRESSION CLIENT                                 --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal-backdrop-custom" id="modal-delete" onclick="closeModal('modal-delete')">
    <div class="modal-box modal-box-sm" onclick="event.stopPropagation()">
        <div class="modal-header-custom">
            <div class="modal-title-custom">
                <div class="modal-icon" style="background:#FCEBEB;color:#A32D2D;">
                    <i class="fas fa-trash"></i>
                </div>
                <span>Supprimer le client</span>
            </div>
            <button class="modal-close-btn" onclick="closeModal('modal-delete')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="modal-body-custom">
            <p style="font-size:14px;color:#444;margin:0 0 6px;">
                Voulez-vous supprimer le client <strong id="delete-name"></strong> ?
            </p>
            <p style="font-size:12px;color:#aaa;margin:0;">
                Cette action est irréversible. L'historique des ventes associé sera conservé.
            </p>

            <form method="POST" id="form-delete">
                @csrf
                @method('DELETE')
                <div class="modal-footer-custom" style="margin-top:20px;">
                    <button type="button" class="btn-cancel" onclick="closeModal('modal-delete')">Annuler</button>
                    <button type="submit" class="btn-danger-custom">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


@push('styles')
<style>
    /* ── Stat cards ─────────────────────────────────────────── */
    .stat-card {
        background: white;
        border-radius: 12px;
        border: 0.5px solid #e5e0f5;
        padding: 14px 16px;
    }
    .stat-label {
        font-size: 11px;
        color: #888;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 6px;
    }
    .stat-val  { font-size: 22px; font-weight: 800; color: #1a1a2e; line-height: 1.1; }
    .stat-sub  { font-size: 11px; color: #bbb; margin-top: 2px; }

    /* ── Main card ──────────────────────────────────────────── */
    .main-card {
        background: white;
        border-radius: 14px;
        border: 0.5px solid #e5e0f5;
        overflow: hidden;
    }
    .main-card-header {
        padding: 14px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 0.5px solid #f0ebff;
        flex-wrap: wrap;
        gap: 10px;
    }
    .card-title-text { font-size: 15px; font-weight: 700; color: #1a1a2e; }

    /* ── Table ──────────────────────────────────────────────── */
    .client-avatar {
        width: 32px; height: 32px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; flex-shrink: 0;
    }
    .rank-num { font-size: 12px; font-weight: 700; color: #bbb; display:inline-block; min-width:20px; text-align:center; }
    .rank-top { color: #854F0B; }
    .bar-wrap  { display:inline-block; width:60px; height:5px; background:#f0ebff; border-radius:99px; vertical-align:middle; }
    .bar-fill  { display:block; height:100%; background:#6B46C1; border-radius:99px; }
    .action-btn {
        display:inline-flex; align-items:center; justify-content:center;
        width:28px; height:28px; border-radius:6px;
        color:#888; font-size:12px; text-decoration:none;
        transition:background .12s, color .12s; cursor:pointer;
        border: none; background: transparent;
    }
    .action-btn:hover { background:#f0ebff; color:#6B46C1; }

    /* ── Pagination ─────────────────────────────────────────── */
    .pagination { margin: 0; }
    .pagination .page-link { border-radius:6px !important; margin:0 2px; font-size:12px; color:#6B46C1; border-color:#e5e0f5; }
    .pagination .page-item.active .page-link { background-color:#6B46C1; border-color:#6B46C1; color:white; }
    .pagination .page-link:hover { background-color:#f0ebff; color:#6B46C1; }

    /* ══════════════════════ MODALS ══════════════════════════ */
    .modal-backdrop-custom {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(30, 10, 60, 0.45);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-backdrop-custom.open {
        display: flex;
    }

    .modal-box {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 8px 40px rgba(107,70,193,0.18);
        animation: modalIn .2s ease;
        overflow: hidden;
    }
    .modal-box-sm { max-width: 420px; }

    @keyframes modalIn {
        from { opacity: 0; transform: translateY(-12px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0)     scale(1);    }
    }

    .modal-header-custom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 20px 14px;
        border-bottom: 0.5px solid #f0ebff;
    }
    .modal-title-custom {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
        font-weight: 700;
        color: #1a1a2e;
    }
    .modal-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; flex-shrink: 0;
    }
    .modal-close-btn {
        width: 30px; height: 30px;
        border-radius: 8px;
        border: none;
        background: #f4f2fc;
        color: #888;
        font-size: 13px;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background .12s;
    }
    .modal-close-btn:hover { background: #e5e0f5; color: #333; }

    .modal-body-custom { padding: 20px; }

    .modal-footer-custom {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 0.5px solid #f0ebff;
    }

    /* ── Form elements in modals ────────────────────────────── */
    .form-row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    @media (max-width: 480px) {
        .form-row-2 { grid-template-columns: 1fr; }
    }
    .form-group { margin-bottom: 14px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-label-custom {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #555;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .required { color: #E24B4A; }

    .form-control, .form-select {
        border-radius: 8px !important;
        border: 0.5px solid #d8d0f0 !important;
        font-size: 13px !important;
        padding: 8px 12px !important;
        color: #1a1a2e !important;
        transition: border-color .15s, box-shadow .15s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #6B46C1 !important;
        box-shadow: 0 0 0 3px rgba(107,70,193,.12) !important;
    }

    /* ── Buttons in modals ──────────────────────────────────── */
    .btn-cancel {
        padding: 8px 18px;
        border-radius: 8px;
        border: 0.5px solid #ddd;
        background: white;
        color: #666;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background .12s;
    }
    .btn-cancel:hover { background: #f4f2fc; }

    .btn-danger-custom {
        padding: 8px 18px;
        border-radius: 8px;
        border: none;
        background: #E24B4A;
        color: white;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background .12s;
    }
    .btn-danger-custom:hover { background: #c73b3a; }
</style>
@endpush


@push('scripts')
<script>
    // ── Helpers ──────────────────────────────────────────────────────────────
    function openModal(id) {
        document.getElementById(id).classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
        document.body.style.overflow = '';
    }
    // Fermer avec Échap
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            ['modal-create', 'modal-edit', 'modal-delete'].forEach(closeModal);
        }
    });

    // ── Modal Ajout ──────────────────────────────────────────────────────────
    function openCreate() {
        openModal('modal-create');
    }

    // ── Modal Modification ───────────────────────────────────────────────────
    function openEdit(id, name, phone, address, branchId, notes) {
        document.getElementById('edit-name').value    = name;
        document.getElementById('edit-phone').value   = phone;
        document.getElementById('edit-address').value = address;
        document.getElementById('edit-notes').value   = notes;

        const branchSelect = document.getElementById('edit-branch');
        branchSelect.value = branchId;

        // Action du formulaire avec l'ID du client
        document.getElementById('form-edit').action = `/clients/${id}`;

        openModal('modal-edit');
    }

    // ── Modal Suppression ────────────────────────────────────────────────────
    function openDelete(id, name) {
        document.getElementById('delete-name').textContent = name;
        document.getElementById('form-delete').action = `/clients/${id}`;
        openModal('modal-delete');
    }

    // ── Ouvrir automatiquement le modal d'erreur de validation ──────────────
    @if($errors->any())
        // Si on vient d'un POST store → rouvrir modal ajout
        @if(old('_method') === null && old('name'))
            openCreate();
        @endif
        // Si on vient d'un PUT → rouvrir modal modification
        @if(old('_method') === 'PUT')
            openEdit(
                '{{ old("_client_id") }}',
                '{{ old("name") }}',
                '{{ old("phone") }}',
                '{{ old("address") }}',
                '{{ old("branch_id") }}',
                '{{ old("notes") }}'
            );
        @endif
    @endif
</script>
@endpush