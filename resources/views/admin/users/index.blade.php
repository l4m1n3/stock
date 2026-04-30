@extends('layouts.apps')

@section('title', 'Admin · Utilisateurs')
@section('page-title', 'Administration · Utilisateurs')

@push('styles')
<style>
.kpi-card { background:white; border-radius:16px; padding:22px 20px; box-shadow:0 4px 20px rgba(107,70,193,0.08); }
.kpi-card h6 { font-size:12px; font-weight:700; color:#9e8fc0; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px; }
.kpi-card h3 { font-size:28px; font-weight:800; color:#1a0a3e; margin:0; }
.badge-admin   { background:#f0ebff; color:#6B46C1; font-size:11px; font-weight:700; padding:3px 10px; border-radius:99px; }
.badge-manager { background:#e8f4fd; color:#1a6fb0; font-size:11px; font-weight:700; padding:3px 10px; border-radius:99px; }
.badge-staff   { background:#edfcf2; color:#177f43; font-size:11px; font-weight:700; padding:3px 10px; border-radius:99px; }
</style>
@endpush

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card">
            <h6>Total utilisateurs</h6>
            <h3>{{ $totalUsers }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <h6>Admins</h6>
            <h3>{{ $adminCount }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <h6>Staff</h6>
            <h3>{{ $staffCount }}</h3>
        </div>
    </div>
</div>

<form method="GET" style="background:white;border-radius:14px;padding:16px 20px;margin-bottom:20px;box-shadow:0 2px 12px rgba(107,70,193,0.06);">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-control form-control-sm" placeholder="Nom ou email…" style="border-radius:8px;">
        </div>
        <div class="col-md-2">
            <select name="role" class="form-select form-select-sm" style="border-radius:8px;">
                <option value="">Tous les rôles</option>
                <option value="admin"   {{ request('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="staff"   {{ request('role') === 'staff'   ? 'selected' : '' }}>Staff</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="branch_id" class="form-select form-select-sm" style="border-radius:8px;">
                <option value="">Toutes les branches</option>
                @foreach($branches as $branch)
                    {{-- ✅ FIX : 'selected' manquait dans l'original --}}
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn-violet flex-fill justify-content-center" style="height:32px;padding:0 10px;font-size:12px;">
                <i class="fas fa-search"></i> Filtrer
            </button>
            <button type="button" class="btn btn-sm btn-success"
                    style="border-radius:8px;" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-plus"></i> Nouveau
            </button>
        </div>
    </div>
</form>

<div style="background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(107,70,193,0.08);">
    <div class="table-responsive">
        <table class="table align-middle mb-0" style="font-size:13px;">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Branche</th>
                    <th class="text-center" width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="fw-600">{{ $user->name }}</td>
                    <td style="color:#9e8fc0;">{{ $user->email }}</td>
                    <td>
                        {{-- ✅ FIX : badge dynamique selon rôle --}}
                        <span class="badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                    </td>
                    <td>
                        @if($user->branch)
                            <span style="font-size:11px;background:#f0ebff;color:#6B46C1;padding:2px 8px;border-radius:99px;font-weight:700;">
                                {{ $user->branch->name }}
                            </span>
                        @else
                            <span style="color:#ccc;">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            {{-- ✅ FIX : data-* attributes au lieu de inline JS avec objet PHP
                                 → évite le parsing cassé si name contient des apostrophes --}}
                            <button class="btn btn-sm btn-outline-primary js-edit-user"
                                    style="border-radius:7px;"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    data-role="{{ $user->role }}"
                                    data-branch="{{ $user->branch_id }}">
                                <i class="fas fa-pen"></i>
                            </button>

                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Supprimer {{ addslashes($user->name) }} ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" style="border-radius:7px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-2x mb-2 d-block" style="color:#d8d0f5;"></i>
                        Aucun utilisateur trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px 20px;">{{ $users->links() }}</div>
</div>

{{-- Modal CREATE --}}
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.users.store') }}" class="modal-content" style="border-radius:16px;overflow:hidden;">
            @csrf
            <div class="modal-header" style="background:linear-gradient(135deg,#6B46C1,#9F7AEA);color:white;border:none;">
                <h5 class="modal-title fw-700">Ajouter un utilisateur</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Nom complet *</label>
                    <input name="name" class="form-control" style="border-radius:10px;" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Email *</label>
                    <input name="email" type="email" class="form-control" style="border-radius:10px;" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Mot de passe *</label>
                        <input name="password" type="password" class="form-control" style="border-radius:10px;" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Confirmer *</label>
                        <input name="password_confirmation" type="password" class="form-control" style="border-radius:10px;" required>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Rôle *</label>
                        <select name="role" class="form-select" style="border-radius:10px;" required>
                            <option value="">— Choisir —</option>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Branche</label>
                        <select name="branch_id" class="form-select" style="border-radius:10px;">
                            <option value="">Aucune</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border:none;padding:16px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:10px;">Annuler</button>
                <button type="submit" class="btn-violet"><i class="fas fa-plus"></i> Créer</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal EDIT --}}
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm" class="modal-content" style="border-radius:16px;overflow:hidden;">
            @csrf @method('PUT')
            <div class="modal-header" style="background:linear-gradient(135deg,#6B46C1,#9F7AEA);color:white;border:none;">
                <h5 class="modal-title fw-700">Modifier l'utilisateur</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:24px;">
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Nom complet *</label>
                    <input id="edit_name" name="name" class="form-control" style="border-radius:10px;" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Email *</label>
                    <input id="edit_email" name="email" type="email" class="form-control" style="border-radius:10px;" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Nouveau mot de passe</label>
                        <input name="password" type="password" class="form-control" style="border-radius:10px;" placeholder="Laisser vide = inchangé">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Confirmer</label>
                        <input name="password_confirmation" type="password" class="form-control" style="border-radius:10px;">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Rôle *</label>
                        <select id="edit_role" name="role" class="form-select" style="border-radius:10px;" required>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:12px;color:#9e8fc0;">Branche</label>
                        <select id="edit_branch" name="branch_id" class="form-select" style="border-radius:10px;">
                            <option value="">Aucune</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border:none;padding:16px 24px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:10px;">Annuler</button>
                <button type="submit" class="btn-violet"><i class="fas fa-check"></i> Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ✅ FIX : utilisation des data-* attributes → aucun risque de casse
//    si le nom contient des apostrophes ou des guillemets
document.querySelectorAll('.js-edit-user').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('edit_name').value   = btn.dataset.name;
        document.getElementById('edit_email').value  = btn.dataset.email;
        document.getElementById('edit_role').value   = btn.dataset.role;
        document.getElementById('edit_branch').value = btn.dataset.branch ?? '';
        document.getElementById('editForm').action   = '/admin/users/' + btn.dataset.id;
        new bootstrap.Modal(document.getElementById('editUserModal')).show();
    });
});
</script>
@endpush