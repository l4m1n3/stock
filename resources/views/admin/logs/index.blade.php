@extends('layouts.apps')

@section('title', 'Logs · Journal des activités')
@section('page-title', 'Journal des activités')

@section('content')

<form method="GET" style="background:white;border-radius:14px;padding:14px 20px;margin-bottom:20px;box-shadow:0 2px 12px rgba(107,70,193,0.06);display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Action ou description…"
           class="form-control form-control-sm" style="width:220px;border-radius:8px;">
    <button type="submit" class="btn-violet" style="padding:6px 14px;font-size:13px;">
        <i class="fas fa-search"></i> Filtrer
    </button>
    <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a>
</form>

<div style="background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(107,70,193,0.08);">
    <div class="table-responsive">
        <table class="table align-middle mb-0" style="font-size:13px;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                {{-- ✅ FIX : @forelse pour gérer la liste vide --}}
                @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap;color:#9e8fc0;">
                        {{ $log->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="fw-600">{{ $log->user->name ?? 'System' }}</td>
                    <td>
                        {{-- ✅ FIX : couleur badge selon famille d'action --}}
                        @php
                            $actionColor = match(true) {
                                str_contains($log->action, 'sale')     => ['#edfcf2','#177f43'],
                                str_contains($log->action, 'purchase') => ['#e8f4fd','#1a6fb0'],
                                str_contains($log->action, 'delete')   => ['#fcebeb','#a32d2d'],
                                str_contains($log->action, 'login')    => ['#fff8e1','#8a6200'],
                                default                                 => ['#f0ebff','#6B46C1'],
                            };
                        @endphp
                        <span style="background:{{ $actionColor[0] }};color:{{ $actionColor[1] }};font-size:11px;font-weight:700;padding:3px 9px;border-radius:99px;">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td style="color:#555;">{{ $log->description ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-list fa-2x mb-2 d-block" style="color:#d8d0f5;"></i>
                        Aucune activité enregistrée.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px 20px;">
        {{ $logs->links() }}
    </div>
</div>

@endsection