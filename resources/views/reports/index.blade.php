@extends('layouts.app')

@section('title', 'Report')
@section('page-title', 'Report Training')
@section('page-subtitle', 'Filter dan export laporan riwayat training.')

@section('content')
<div class="content-card mb-3">
    <div class="content-card-body">
        <form action="{{ route('reports.index') }}" method="GET" id="filter-form">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Periode Dari</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Periode Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nama Karyawan</label>
                    <input type="text" name="employee_name" class="form-control" value="{{ request('employee_name') }}" placeholder="Cari nama...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Departemen</label>
                    <select name="department_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="position" class="form-control" value="{{ request('position') }}" placeholder="Cari jabatan...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Modul Training</label>
                    <select name="training_module_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" @selected(request('training_module_id') == $module->id)>{{ $module->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mandatory</label>
                    <select name="mandatory" class="form-select">
                        <option value="all" @selected(request('mandatory', 'all') === 'all')>Semua</option>
                        <option value="mandatory" @selected(request('mandatory') === 'mandatory')>Mandatory</option>
                        <option value="non_mandatory" @selected(request('mandatory') === 'non_mandatory')>Non Mandatory</option>
                    </select>
                </div>
                <div class="col-md-9 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                    <a href="{{ route('reports.export.excel', request()->query()) }}" class="btn btn-success ms-auto">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                    <a href="{{ route('reports.export.pdf', request()->query()) }}" class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Total Riwayat</div>
            <div class="stat-value">{{ $summary['total'] }}</div>
            <div class="stat-caption">Sesuai filter aktif</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card accent-red">
            <div class="stat-title">Mandatory</div>
            <div class="stat-value">{{ $summary['mandatory'] }}</div>
            <div class="stat-caption">Riwayat training mandatory</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-color:#fde68a;">
            <div class="stat-title">Akan Expired</div>
            <div class="stat-value" style="color:#d97706;">{{ $summary['expiring_soon'] }}</div>
            <div class="stat-caption">≤ 30 hari lagi</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card accent-red">
            <div class="stat-title">Sudah Expired</div>
            <div class="stat-value">{{ $summary['expired'] }}</div>
            <div class="stat-caption">Sudah lewat masa berlaku</div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="content-card-body">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Departemen</th>
                    <th>Training</th>
                    <th>Mandatory</th>
                    <th>Tanggal</th>
                    <th>Trainer</th>
                    <th>Expired</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $history)
                    <tr>
                        <td>{{ $history->employee->name }}<br><small class="text-muted">{{ $history->employee->nik }}</small></td>
                        <td>{{ $history->employee->department->name }}</td>
                        <td>{{ $history->training_name_snapshot }}</td>
                        <td>
                            @if($history->is_mandatory_snapshot)
                                <span class="badge bg-danger">Mandatory</span>
                            @else
                                <span class="badge bg-secondary">Non Mandatory</span>
                            @endif
                        </td>
                        <td>{{ $history->training_date->format('d M Y') }}</td>
                        <td>{{ $history->trainer_name_snapshot }}</td>
                        <td>{{ $history->expired_at?->format('d M Y') ?? '-' }}</td>
                        <td>
                            @php
                                $statusBadge = [
                                    'valid' => 'bg-success',
                                    'expiring_soon' => 'bg-warning',
                                    'expired' => 'bg-danger',
                                    'no_expiry' => 'bg-secondary',
                                ];
                                $statusLabel = [
                                    'valid' => 'Valid',
                                    'expiring_soon' => 'Akan Expired',
                                    'expired' => 'Expired',
                                    'no_expiry' => 'Tanpa Masa Berlaku',
                                ];
                            @endphp
                            <span class="badge {{ $statusBadge[$history->status] }}">{{ $statusLabel[$history->status] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Tidak ada data sesuai filter</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $histories->links() }}
    </div>
</div>
@endsection
