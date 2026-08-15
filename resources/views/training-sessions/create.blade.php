@extends('layouts.app')

@section('title', 'Buat Training Session')
@section('page-title', 'Buat Training Session Baru')
@section('page-subtitle', 'Isi detail sesi lalu pilih peserta yang mengikuti.')

@section('content')
<form action="{{ route('training-sessions.store') }}" method="POST">
    @csrf

    <div class="content-card mb-3">
        <div class="content-card-header">Detail Session</div>
        <div class="content-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Modul Training <span class="text-danger">*</span></label>
                    <select name="training_module_id" class="form-select @error('training_module_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Modul Training --</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" @selected(old('training_module_id', request('training_module_id')) == $module->id)>
                                {{ $module->code }} — {{ $module->name }} @if($module->is_mandatory) (Mandatory) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('training_module_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Trainer <span class="text-danger">*</span></label>
                    <input type="text" name="trainer_name" class="form-control @error('trainer_name') is-invalid @enderror"
                           value="{{ old('trainer_name') }}" placeholder="Nama HOD / HRD" required>
                    @error('trainer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tanggal Training <span class="text-danger">*</span></label>
                    <input type="date" name="session_date" class="form-control @error('session_date') is-invalid @enderror"
                           value="{{ old('session_date') }}" required>
                    @error('session_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror"
                           value="{{ old('start_time') }}" required>
                    @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                    <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
                           value="{{ old('end_time') }}" required>
                    @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-2">
                    <label class="form-label">Durasi Aktual (jam)</label>
                    <input type="number" step="0.5" name="actual_duration_hours"
                           class="form-control @error('actual_duration_hours') is-invalid @enderror"
                           value="{{ old('actual_duration_hours') }}" placeholder="Opsional">
                    @error('actual_duration_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                           value="{{ old('location') }}">
                    @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="content-card mb-3">
        <div class="content-card-header d-flex justify-content-between align-items-center">
            <span>Pilih Peserta Training</span>
            <span class="badge bg-primary" id="selected-count">0 dipilih</span>
        </div>
        <div class="content-card-body">
            @error('employee_ids')
                <div class="alert alert-danger py-2">{{ $message }}</div>
            @enderror

            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" id="search-employee" class="form-control" placeholder="Cari nama / NIK...">
                </div>
                <div class="col-md-4">
                    <select id="filter-department" class="form-select">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-center gap-2">
                    <button type="button" id="btn-select-visible" class="btn btn-outline-primary btn-sm">Pilih Semua (Terfilter)</button>
                    <button type="button" id="btn-clear-selection" class="btn btn-outline-secondary btn-sm">Kosongkan</button>
                </div>
            </div>

            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-hover">
                    <thead class="sticky-top bg-white">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Departemen</th>
                            <th>Jabatan</th>
                        </tr>
                    </thead>
                    <tbody id="employee-list">
                        @php $oldSelected = old('employee_ids', []); @endphp
                        @foreach($employees as $emp)
                            <tr class="employee-row" data-department="{{ $emp->department_id }}"
                                data-search="{{ strtolower($emp->name . ' ' . $emp->nik) }}">
                                <td>
                                    <input type="checkbox" class="form-check-input employee-checkbox"
                                           name="employee_ids[]" value="{{ $emp->id }}"
                                           @checked(in_array($emp->id, $oldSelected))>
                                </td>
                                <td>{{ $emp->nik }}</td>
                                <td>{{ $emp->name }}</td>
                                <td>{{ $emp->department->name }}</td>
                                <td>{{ $emp->position ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Simpan Training Session</button>
    <a href="{{ route('training-sessions.index') }}" class="btn btn-outline-secondary">Batal</a>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-employee');
    const deptFilter = document.getElementById('filter-department');
    const rows = Array.from(document.querySelectorAll('.employee-row'));
    const selectedCountBadge = document.getElementById('selected-count');

    function applyFilter() {
        const keyword = searchInput.value.toLowerCase().trim();
        const dept = deptFilter.value;

        rows.forEach(row => {
            const matchesSearch = !keyword || row.dataset.search.includes(keyword);
            const matchesDept = !dept || row.dataset.department === dept;
            row.style.display = (matchesSearch && matchesDept) ? '' : 'none';
        });
    }

    function updateSelectedCount() {
        const count = document.querySelectorAll('.employee-checkbox:checked').length;
        selectedCountBadge.textContent = count + ' dipilih';
    }

    searchInput.addEventListener('input', applyFilter);
    deptFilter.addEventListener('change', applyFilter);

    document.getElementById('btn-select-visible').addEventListener('click', () => {
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                row.querySelector('.employee-checkbox').checked = true;
            }
        });
        updateSelectedCount();
    });

    document.getElementById('btn-clear-selection').addEventListener('click', () => {
        document.querySelectorAll('.employee-checkbox').forEach(cb => cb.checked = false);
        updateSelectedCount();
    });

    document.querySelectorAll('.employee-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    updateSelectedCount();
});
</script>
@endpush
