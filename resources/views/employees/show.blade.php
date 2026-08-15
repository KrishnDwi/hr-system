@extends('layouts.app')

@section('title', 'Detail Karyawan')
@section('page-title', $employee->name)
@section('page-subtitle', $employee->department->name . ' — ' . ($employee->position ?? 'Tanpa Jabatan'))

@section('page-actions')
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-primary">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Kembali</a>
@endsection

@section('content')

{{-- ===== Identitas & Pekerjaan ===== --}}
<div class="content-card mb-3">
    <div class="content-card-header">Identitas & Pekerjaan</div>
    <div class="content-card-body">
        <div class="row">
            <div class="col-md-4">
                <p class="mb-2"><strong>ID No.:</strong> {{ $employee->nik }}</p>
                <p class="mb-2"><strong>Departemen:</strong> {{ $employee->department->name }}</p>
                <p class="mb-0"><strong>Jabatan:</strong> {{ $employee->position ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                @php
                    $typeLabel = ['staff'=>'Staff','dw'=>'Daily Worker','casual'=>'Casual','trainee'=>'Trainee','outsourcing'=>'Outsourcing'];
                    $statusBadge = ['active'=>'bg-success','inactive'=>'bg-secondary','resigned'=>'bg-dark'];
                    $statusLabel = ['active'=>'Aktif','inactive'=>'Nonaktif','resigned'=>'Resign'];
                @endphp
                <p class="mb-2"><strong>Kategori:</strong> {{ $typeLabel[$employee->employee_type] ?? $employee->employee_type }}</p>
                <p class="mb-2"><strong>Status:</strong> <span class="badge {{ $statusBadge[$employee->employment_status] }}">{{ $statusLabel[$employee->employment_status] }}</span></p>
                <p class="mb-0"><strong>Tanggal Masuk:</strong> {{ $employee->join_date?->format('d M Y') ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="mb-2"><strong>Email:</strong> {{ $employee->email ?? '-' }}</p>
                <p class="mb-0"><strong>No. Telepon:</strong> {{ $employee->phone ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ===== Data Pribadi ===== --}}
<div class="content-card mb-3">
    <div class="content-card-header">Data Pribadi</div>
    <div class="content-card-body">
        <div class="row">
            <div class="col-md-4">
                <p class="mb-2"><strong>Tempat, Tanggal Lahir:</strong> {{ $employee->place_of_birth ?? '-' }}{{ $employee->date_of_birth ? ', ' . $employee->date_of_birth->format('d M Y') : '' }}</p>
                <p class="mb-2"><strong>Usia:</strong> {{ $employee->age ? $employee->age . ' tahun' : '-' }}</p>
                <p class="mb-0"><strong>Gender:</strong> {{ $employee->gender === 'male' ? 'Laki-laki' : ($employee->gender === 'female' ? 'Perempuan' : '-') }}</p>
            </div>
            <div class="col-md-4">
                <p class="mb-2"><strong>Agama:</strong> {{ $employee->religion ?? '-' }}</p>
                <p class="mb-2"><strong>Golongan Darah:</strong> {{ $employee->blood_type ?? '-' }}</p>
                <p class="mb-0"><strong>Level:</strong> {{ $employee->job_level ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="mb-2"><strong>Alamat:</strong> {{ $employee->address ?? '-' }}</p>
                <p class="mb-0"><strong>Daerah:</strong> {{ $employee->region ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ===== Keluarga ===== --}}
<div class="content-card mb-3">
    <div class="content-card-header">Keluarga & Kontak Darurat</div>
    <div class="content-card-body">
        <div class="row">
            <div class="col-md-4">
                <p class="mb-2"><strong>Pasangan:</strong> {{ $employee->spouse_name ?? '-' }}</p>
                <p class="mb-0"><strong>Tgl Lahir Pasangan:</strong> {{ $employee->spouse_date_of_birth?->format('d M Y') ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="mb-0"><strong>Jumlah Anak:</strong> {{ $employee->children_count ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="mb-2"><strong>Kontak Darurat:</strong> {{ $employee->emergency_contact_name ?? '-' }}</p>
                <p class="mb-0"><strong>Hubungan:</strong> {{ $employee->emergency_contact_relationship ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ===== Finansial & Legal ===== --}}
<div class="content-card mb-3">
    <div class="content-card-header d-flex justify-content-between">
        <span>Finansial & Legal</span>
        <span class="badge bg-warning text-dark">Data Sensitif</span>
    </div>
    <div class="content-card-body">
        <div class="row">
            <div class="col-md-4">
                <p class="mb-2"><strong>NPWP:</strong> {{ $employee->npwp_no ?? '-' }}</p>
                <p class="mb-0"><strong>NIK KTP:</strong> {{ $employee->nik_ktp ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="mb-2"><strong>Rekening:</strong> {{ $employee->bank_account_number ?? '-' }}</p>
                <p class="mb-0"><strong>A/N:</strong> {{ $employee->bank_account_name ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="mb-2"><strong>Jamsostek:</strong> {{ $employee->jamsostek_no ?? '-' }}</p>
                <p class="mb-0"><strong>BPJS:</strong> {{ $employee->bpjs_no ?? '-' }}</p>
            </div>
        </div>
        <hr>
        <p class="mb-0"><strong>Pendidikan:</strong> {{ $employee->education_background ?? '-' }} ({{ $employee->education_level ?? '-' }})</p>
    </div>
</div>

{{-- ===== Riwayat Kontrak ===== --}}
<div class="content-card mb-3">
    <div class="content-card-header">Riwayat Kontrak Kerja</div>
    <div class="content-card-body">
        <table class="table table-sm mb-3">
            <thead>
                <tr><th>Urutan</th><th>Tipe</th><th>Mulai</th><th>Berakhir</th><th>Catatan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($employee->contracts as $contract)
                    <tr>
                        <td>{{ $contract->sequence }} {{ $contract->is_last ? '(Terakhir)' : '' }}</td>
                        <td>{{ ucfirst($contract->type) }}</td>
                        <td>{{ $contract->start_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $contract->end_date?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $contract->notes ?? '-' }}</td>
                        <td>
                            <form action="{{ route('employees.contracts.destroy', [$employee, $contract]) }}" method="POST" onsubmit="return confirm('Hapus riwayat kontrak ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Belum ada riwayat kontrak</td></tr>
                @endforelse
            </tbody>
        </table>

        <form action="{{ route('employees.contracts.store', $employee) }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-2">
                <label class="form-label small">Tipe</label>
                <select name="type" class="form-select form-select-sm" required>
                    <option value="permanent">Permanent</option>
                    <option value="contract" selected>Contract</option>
                    <option value="jeda">Jeda</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Mulai</label>
                <input type="date" name="start_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Berakhir</label>
                <input type="date" name="end_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Catatan</label>
                <input type="text" name="notes" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 form-check ms-2">
                <input type="checkbox" name="is_last" value="1" class="form-check-input" id="isLastContract">
                <label class="form-check-label small" for="isLastContract">Kontrak Terakhir</label>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">Tambah</button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Mandatory Training Belum/Perlu Dilakukan ===== --}}
<div class="content-card mb-3" style="border-left: 4px solid #dc2626;">
    <div class="content-card-header d-flex justify-content-between align-items-center" style="color:#b91c1c;">
        <span>Mandatory Training yang Belum/Perlu Dilakukan</span>
        <span class="badge bg-danger">{{ $missingMandatoryModules->count() }} modul</span>
    </div>
    <div class="content-card-body p-0">
        <table class="table mb-0">
            <thead><tr><th class="ps-4">Modul Training</th><th>Kategori</th><th class="pe-4">Masa Berlaku</th></tr></thead>
            <tbody>
                @forelse($missingMandatoryModules as $module)
                    <tr>
                        <td class="ps-4">{{ $module->name }}</td>
                        <td>{{ $module->category ?? '-' }}</td>
                        <td class="pe-4">{{ $module->validity_months ? $module->validity_months . ' bulan' : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">✅ Semua mandatory training sudah dipenuhi dan masih valid.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== Riwayat Training ===== --}}
<div class="content-card">
    <div class="content-card-header">Riwayat Training ({{ $trainingHistories->count() }} record)</div>
    <div class="content-card-body p-0">
        <table class="table mb-0 align-middle">
            <thead><tr><th class="ps-4">Training</th><th>Mandatory</th><th>Tanggal</th><th>Trainer</th><th>Expired</th><th class="pe-4">Status</th></tr></thead>
            <tbody>
                @forelse($trainingHistories as $history)
                    <tr>
                        <td class="ps-4">{{ $history->training_name_snapshot }}</td>
                        <td>{!! $history->is_mandatory_snapshot ? '<span class="badge bg-danger">Mandatory</span>' : '<span class="badge bg-secondary">Non Mandatory</span>' !!}</td>
                        <td>{{ $history->training_date->format('d M Y') }}</td>
                        <td>{{ $history->trainer_name_snapshot }}</td>
                        <td>{{ $history->expired_at?->format('d M Y') ?? '-' }}</td>
                        <td class="pe-4">
                            @php
                                $hBadge = ['valid'=>'bg-success','expiring_soon'=>'bg-warning','expired'=>'bg-danger','no_expiry'=>'bg-secondary'];
                                $hLabel = ['valid'=>'Valid','expiring_soon'=>'Akan Expired','expired'=>'Expired','no_expiry'=>'Tanpa Masa Berlaku'];
                            @endphp
                            <span class="badge {{ $hBadge[$history->status] }}">{{ $hLabel[$history->status] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Karyawan ini belum pernah mengikuti training apa pun.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
