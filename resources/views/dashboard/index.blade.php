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

{{-- ===== Quick-glance tables (gaya "Work Order Baru" di referensi) ===== --}}
<div class="content-card mb-3" style="border-left: 4px solid #f59e0b;">
    <div class="content-card-header" style="color:#b45309;">Akan Expired (10 terdekat)</div>
    <div class="content-card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th class="ps-4">Karyawan</th><th>Training</th><th class="pe-4">Expired</th></tr>
            </thead>
            <tbody>
                @forelse($expiringList as $item)
                    <tr>
                        <td class="ps-4">{{ $item->employee->name }}</td>
                        <td>{{ $item->training_name_snapshot }}</td>
                        <td class="pe-4 text-warning fw-semibold">{{ $item->expired_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="content-card" style="border-left: 4px solid #dc2626;">
    <div class="content-card-header" style="color:#b91c1c;">Sudah Expired (10 terbaru)</div>
    <div class="content-card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr><th class="ps-4">Karyawan</th><th>Training</th><th class="pe-4">Expired</th></tr>
            </thead>
            <tbody>
                @forelse($expiredList as $item)
                    <tr>
                        <td class="ps-4">{{ $item->employee->name }}</td>
                        <td>{{ $item->training_name_snapshot }}</td>
                        <td class="pe-4 text-danger fw-semibold">{{ $item->expired_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
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
