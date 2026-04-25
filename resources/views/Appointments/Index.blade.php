@extends('layouts.app')

@section('page-title', __('nav.appointments'))
@section('breadcrumb', __('appointments.manage'))

@section('topbar-actions')
    <button class="btn btn-primary" onclick="openModal('createModal')">
        <i class="fas fa-plus"></i> {{ __('appointments.new') }}
    </button>
@endsection

@section('content')
<style>
.filters-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:18px; }
.search-wrap { display:flex; align-items:center; gap:8px; background:#fff; border:1px solid var(--border); border-radius:8px; padding:8px 12px; flex:1; min-width:200px; max-width:320px; }
.search-wrap i { color:var(--text-muted); font-size:13px; }
.search-wrap input { border:none; outline:none; font-size:13px; width:100%; font-family:inherit; background:transparent; }
.filter-select { padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; font-family:inherit; background:#fff; color:var(--text); cursor:pointer; }
.avatar-sm { width:30px; height:30px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:600; flex-shrink:0; }
.action-wrap { display:flex; gap:5px; }
.act-btn { width:28px; height:28px; border-radius:6px; border:1px solid var(--border); background:#f9fafb; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.15s; }
.act-btn:hover { background:var(--primary-lt); border-color:var(--primary-b); }
.act-btn:hover i { color:var(--primary-mid); }
.act-btn.del:hover { background:var(--danger-lt); border-color:#F7C1C1; }
.act-btn.del:hover i { color:var(--danger); }
.act-btn i { font-size:12px; color:var(--text-muted); }
.empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
.empty-state i { font-size:40px; margin-bottom:12px; color:var(--border); }
.pagination-wrap { display:flex; justify-content:space-between; align-items:center; padding:14px 0 0; }
</style>

{{-- Barre de filtres --}}
<div class="filters-bar">
    <div class="search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="{{ __('appointments.search_placeholder') }}" autocomplete="off">
    </div>
    <select class="filter-select" id="statusFilter">
        <option value="">{{ __('appointments.all_statuses') }}</option>
        <option value="pending">{{ __('appointments.status.pending') }}</option>
        <option value="confirmed">{{ __('appointments.status.confirmed') }}</option>
        <option value="cancelled">{{ __('appointments.status.cancelled') }}</option>
        <option value="completed">{{ __('appointments.status.completed') }}</option>
    </select>
    <input type="date" class="filter-select" id="dateFilter" value="{{ request('date') }}">
    <span id="resultCount" style="font-size:12px;color:var(--text-muted);"></span>
</div>

{{-- Table --}}
<div class="table-responsive" id="tableWrap">
    <table id="rdvTable">
        <thead>
            <tr>
                <th>{{ __('appointments.patient') }}</th>
                <th>{{ __('appointments.doctor') }}</th>
                <th>{{ __('appointments.date') }}</th>
                <th>{{ __('appointments.time') }}</th>
                <th>{{ __('appointments.service') }}</th>
                <th>{{ __('appointments.status') }}</th>
                <th>{{ __('appointments.actions') }}</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            @forelse($appointments as $rdv)
            <tr data-id="{{ $rdv->id }}">
                <td>
                    <div style="display:flex;align-items:center;gap:9px">
                        <div class="avatar-sm" style="background:var(--primary-lt);color:var(--primary-mid)">{{ $rdv->patient->initials }}</div>
                        <div>
                            <div style="font-weight:500">{{ $rdv->patient->name }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $rdv->patient->phone }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-weight:500">{{ $rdv->doctor->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ $rdv->doctor->specialty }}</div>
                </td>
                <td>{{ $rdv->appointment_date->format('d/m/Y') }}</td>
                <td style="font-weight:500;color:var(--primary-mid)">{{ $rdv->appointment_time }}</td>
                <td>{{ $rdv->service->name }}</td>
                <td>
                    <span class="badge badge-{{ $rdv->status_color }}">{{ $rdv->status_label }}</span>
                </td>
                <td>
                    <div class="action-wrap">
                        <button class="act-btn" onclick="openEditModal({{ $rdv->id }}, '{{ $rdv->patient_id }}', '{{ $rdv->doctor_id }}', '{{ $rdv->service_id }}', '{{ $rdv->appointment_date->format('Y-m-d') }}', '{{ $rdv->appointment_time }}', '{{ $rdv->status }}', '{{ addslashes($rdv->notes ?? '') }}')" title="{{ __('appointments.edit') }}">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="act-btn del" onclick="openDeleteModal({{ $rdv->id }}, '{{ addslashes($rdv->patient->name) }}')" title="{{ __('appointments.cancel') }}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                <i class="fas fa-calendar-times" style="font-size:28px;display:block;margin-bottom:8px;color:var(--border)"></i>
                {{ __('appointments.none') }}
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($appointments->hasPages())
<div class="pagination-wrap">
    <span style="font-size:13px;color:var(--text-muted)">
        {{ __('appointments.showing', ['from' => $appointments->firstItem(), 'to' => $appointments->lastItem(), 'total' => $appointments->total()]) }}
    </span>
    {{ $appointments->links() }}
</div>
@endif

{{-- ═══════════════════════════ MODAL CRÉER ═══════════════════════════ --}}
<div id="createModal" class="modal-overlay" style="display:none">
    <div class="modal-box">
        <div class="modal-title">
            <span><i class="fas fa-calendar-plus" style="color:var(--primary-mid);margin-right:8px"></i> {{ __('appointments.create_title') }}</span>
            <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('appointments.store') }}">
            @csrf
            @include('appointments._form', ['appointment' => null, 'doctors' => $doctors, 'services' => $services, 'patients' => \App\Models\User::patients()->active()->get()])
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-outline" onclick="closeModal('createModal')">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> {{ __('appointments.confirm') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════ MODAL MODIFIER ═══════════════════════════ --}}
<div id="editModal" class="modal-overlay" style="display:none">
    <div class="modal-box">
        <div class="modal-title">
            <span><i class="fas fa-pencil-alt" style="color:var(--primary-mid);margin-right:8px"></i> {{ __('appointments.edit_title') }}</span>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            @include('appointments._form', ['appointment' => null, 'doctors' => $doctors, 'services' => $services, 'patients' => \App\Models\User::patients()->active()->get()])
            <div class="form-group">
                <label class="form-label">{{ __('appointments.status') }}</label>
                <select name="status" class="form-control" id="edit_status">
                    <option value="pending">{{ __('appointments.status.pending') }}</option>
                    <option value="confirmed">{{ __('appointments.status.confirmed') }}</option>
                    <option value="cancelled">{{ __('appointments.status.cancelled') }}</option>
                    <option value="completed">{{ __('appointments.status.completed') }}</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('app.save') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════ MODAL SUPPRIMER ═══════════════════════════ --}}
<div id="deleteModal" class="modal-overlay" style="display:none">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-title">
            <span style="color:var(--danger)"><i class="fas fa-exclamation-triangle" style="margin-right:8px"></i> {{ __('appointments.cancel_confirm_title') }}</span>
            <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px" id="deleteMessage"></p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" class="btn btn-outline" onclick="closeModal('deleteModal')">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-ban"></i> {{ __('appointments.cancel_btn') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js"></script>
<script>
// ── Modals ──
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
});

// ── Edit modal ──
function openEditModal(id, patientId, doctorId, serviceId, date, time, status, notes) {
    const form = document.getElementById('editForm');
    form.action = `/appointments/${id}`;
    form.querySelector('[name=patient_id]').value = patientId;
    form.querySelector('[name=doctor_id]').value  = doctorId;
    form.querySelector('[name=service_id]').value = serviceId;
    form.querySelector('[name=appointment_date]').value = date;
    form.querySelector('[name=appointment_time]').value = time;
    form.querySelector('[name=notes]').value = notes;
    document.getElementById('edit_status').value = status;
    openModal('editModal');
}

// ── Delete modal ──
function openDeleteModal(id, patientName) {
    document.getElementById('deleteForm').action = `/appointments/${id}`;
    document.getElementById('deleteMessage').textContent =
        `{{ __('appointments.cancel_confirm_msg') }}`.replace(':name', patientName);
    openModal('deleteModal');
}

// ── Recherche AJAX (Axios) ──
let searchTimeout;
const searchInput  = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const dateFilter   = document.getElementById('dateFilter');
const resultCount  = document.getElementById('resultCount');

function doSearch() {
    const q      = searchInput.value.trim();
    const status = statusFilter.value;
    const date   = dateFilter.value;

    axios.get('{{ route('appointments.search') }}', { params: { q, status, date } })
        .then(res => {
            const rows = res.data;
            resultCount.textContent = `${rows.length} résultat(s)`;
            const tbody = document.getElementById('tableBody');
            if (rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                    <i class="fas fa-search" style="font-size:24px;display:block;margin-bottom:8px;color:var(--border)"></i>
                    Aucun résultat trouvé
                </td></tr>`;
                return;
            }
            tbody.innerHTML = rows.map(r => `
                <tr>
                    <td><div style="font-weight:500">${r.patient_name}</div></td>
                    <td>${r.doctor_name}</td>
                    <td>${r.appointment_date}</td>
                    <td style="font-weight:500;color:var(--primary-mid)">${r.appointment_time}</td>
                    <td>${r.service_name}</td>
                    <td><span class="badge badge-${statusColor(r.status)}">${r.status_label}</span></td>
                    <td><div class="action-wrap">
                        <button class="act-btn" title="Modifier"><i class="fas fa-pencil-alt"></i></button>
                        <button class="act-btn del" onclick="openDeleteModal(${r.id}, '${r.patient_name}')" title="Annuler">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div></td>
                </tr>
            `).join('');
        })
        .catch(err => console.error('Search error:', err));
}

function statusColor(s) {
    return { pending:'warning', confirmed:'success', cancelled:'danger', completed:'info' }[s] || 'secondary';
}

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(doSearch, 300);
});
statusFilter.addEventListener('change', doSearch);
dateFilter.addEventListener('change', doSearch);
</script>
@endpush
@endsection