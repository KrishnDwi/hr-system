@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Overview kondisi training perusahaan secara real-time.')

@section('content')

{{-- ===== Kartu Ringkasan Utama ===== --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Total Karyawan</div>
            <div class="stat-value">{{ $totalEmployees }}</div>
            <div class="stat-caption">Karyawan dengan status aktif</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Total Modul Training</div>
            <div class="stat-value">{{ $totalModules }}</div>
            <div class="stat-caption">Modul training aktif</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Total Training Session</div>
            <div class="stat-value">{{ $totalSessions }}</div>
            <div class="stat-caption">Seluruh session yang pernah dibuat</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card accent-red">
            <div class="stat-title">Total Mandatory Training</div>
            <div class="stat-value">{{ $totalMandatoryModules }}</div>
            <div class="stat-caption">Modul wajib bagi seluruh karyawan</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-title">Training Hari Ini</div>
            <div class="stat-value">{{ $sessionsToday }}</div>
            <div class="stat-caption">Session dengan tanggal hari ini</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card accent-blue">
            <div class="stat-title">Training Bulan Ini</div>
            <div class="stat-value">{{ $sessionsThisMonth }}</div>
            <div class="stat-caption">Session pada bulan berjalan</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-color:#fde68a;">
            <div class="stat-title">Akan Expired</div>
            <div class="stat-value" style="color:#d97706;">{{ $expiringSoonCount }}</div>
            <div class="stat-caption">Riwayat training ≤ 30 hari lagi</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card accent-red">
            <div class="stat-title">Sudah Expired</div>
            <div class="stat-value">{{ $expiredCount }}</div>
            <div class="stat-caption">Riwayat training yang kedaluwarsa</div>
        </div>
    </div>
</div>

{{-- ===== Mandatory Completion ===== --}}
<div class="content-card mb-3">
    <div class="content-card-header">Mandatory Training Completion</div>
    <div class="content-card-body">
        <div class="progress" style="height: 28px; border-radius: 10px;">
            <div class="progress-bar {{ $mandatoryCompletionPercentage < 60 ? 'bg-danger' : ($mandatoryCompletionPercentage < 85 ? 'bg-warning' : 'bg-success') }}"
                 role="progressbar" style="width: {{ $mandatoryCompletionPercentage }}%">
                {{ $mandatoryCompletionPercentage }}%
            </div>
        </div>
        <small class="text-muted d-block mt-2">Berdasarkan karyawan aktif × modul mandatory aktif yang riwayatnya masih valid (belum expired).</small>
    </div>
</div>

{{-- ===== Charts ===== --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="content-card h-100">
            <div class="content-card-header">Statistik Training per Departemen ({{ date('Y') }})</div>
            <div class="content-card-body">
                <canvas id="chartDepartment"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="content-card h-100">
            <div class="content-card-header">Statistik Training per Bulan ({{ date('Y') }})</div>
            <div class="content-card-body">
                <canvas id="chartMonthly"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ===== Pengingat: Training yang perlu dijadwalkan tahun ini ===== --}}
<div class="content-card">
    <div class="content-card-header d-flex justify-content-between align-items-center">
        <span>Training yang Perlu Dijadwalkan Tahun Ini ({{ date('Y') }})</span>
        <span class="badge bg-danger">{{ $trainingsNeededThisYear->sum('need_count') }} slot karyawan</span>
    </div>
    <div class="content-card-body p-0">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th class="ps-4">Modul Training</th>
                    <th>Kategori</th>
                    <th>Karyawan Perlu Training</th>
                    <th>Session Sudah Dibuat Tahun Ini</th>
                    <th class="pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trainingsNeededThisYear as $item)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $item->module->name }}</td>
                        <td>{{ $item->module->category ?? '-' }}</td>
                        <td>
                            <span class="badge bg-danger">{{ $item->need_count }} orang</span>
                        </td>
                        <td>{{ $item->sessions_this_year }} session</td>
                        <td class="pe-4">
                            <a href="{{ route('training-sessions.create', ['training_module_id' => $item->module->id]) }}"
                               class="btn btn-sm btn-primary">
                                Buat Session
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            🎉 Semua karyawan aktif sudah memenuhi mandatory training untuk tahun ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<small class="text-muted d-block mt-2">
    "Perlu training" mencakup: belum pernah ikut sama sekali, sudah expired, atau akan expired
    sebelum 31 Desember {{ date('Y') }} (meski saat ini statusnya masih valid).
</small>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartDepartment'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($statsByDepartment->pluck('name')) !!},
        datasets: [{
            label: 'Jumlah Kehadiran Training',
            data: {!! json_encode($statsByDepartment->pluck('participant_count')) !!},
            backgroundColor: '#2563eb',
            borderRadius: 6
        }]
    },
    options: { plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('chartMonthly'), {
    type: 'line',
    data: {
        labels: {!! json_encode($monthLabels) !!},
        datasets: [{
            label: 'Jumlah Training Session',
            data: {!! json_encode($sessionsPerMonth) !!},
            borderColor: '#16a34a',
            backgroundColor: 'rgba(22,163,74,0.08)',
            tension: 0.3,
            fill: true
        }]
    }
});
</script>
@endpush
